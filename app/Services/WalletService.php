<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Agent;
use App\Models\UserWallet;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Merchants\MerchantWallet;

/**
 * Wallet Service
 * 
 * Provides comprehensive wallet management functionality
 * supporting multiple platforms (Web, Mobile App, API)
 * 
 * Features:
 * - Multi-currency wallet support (Fiat & Crypto)
 * - Wallet balance management
 * - Internal transfers between wallets
 * - Transaction history
 * - Balance locking/reservation
 * - Wallet statistics
 */
class WalletService
{
    /**
     * Wallet types for different user roles
     */
    const WALLET_TYPE_USER = 'user';
    const WALLET_TYPE_AGENT = 'agent';
    const WALLET_TYPE_MERCHANT = 'merchant';

    /**
     * Get all wallets for a user
     *
     * @param int $userId
     * @param string $walletType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWallets(int $userId, string $walletType = self::WALLET_TYPE_USER)
    {
        return match ($walletType) {
            self::WALLET_TYPE_USER => UserWallet::where('user_id', $userId)
                ->with('currency')
                ->orderByDesc('balance')
                ->get(),
            self::WALLET_TYPE_AGENT => AgentWallet::where('agent_id', $userId)
                ->with('currency')
                ->orderByDesc('balance')
                ->get(),
            self::WALLET_TYPE_MERCHANT => MerchantWallet::where('merchant_id', $userId)
                ->with('currency')
                ->orderByDesc('balance')
                ->get(),
            default => collect([])
        };
    }

    /**
     * Get wallets by currency type (fiat or crypto)
     *
     * @param int $userId
     * @param string $currencyType
     * @param string $walletType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWalletsByCurrencyType(int $userId, string $currencyType, string $walletType = self::WALLET_TYPE_USER)
    {
        $walletClass = $this->getWalletClass($walletType);
        $ownerField = $this->getOwnerField($walletType);

        return $walletClass::where($ownerField, $userId)
            ->whereHas('currency', function ($q) use ($currencyType) {
                $q->where('type', $currencyType);
            })
            ->with('currency')
            ->orderByDesc('balance')
            ->get();
    }

    /**
     * Get single wallet by currency code
     *
     * @param int $userId
     * @param string $currencyCode
     * @param string $walletType
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getWalletByCurrency(int $userId, string $currencyCode, string $walletType = self::WALLET_TYPE_USER)
    {
        $walletClass = $this->getWalletClass($walletType);
        $ownerField = $this->getOwnerField($walletType);

        return $walletClass::where($ownerField, $userId)
            ->whereHas('currency', function ($q) use ($currencyCode) {
                $q->where('code', strtoupper($currencyCode));
            })
            ->with('currency')
            ->first();
    }

    /**
     * Get wallet balance
     *
     * @param int $userId
     * @param string $currencyCode
     * @param string $walletType
     * @return float
     */
    public function getBalance(int $userId, string $currencyCode, string $walletType = self::WALLET_TYPE_USER): float
    {
        $wallet = $this->getWalletByCurrency($userId, $currencyCode, $walletType);
        return $wallet ? (float) $wallet->balance : 0.0;
    }

    /**
     * Get total balance across all wallets in base currency
     *
     * @param int $userId
     * @param string $walletType
     * @return array
     */
    public function getTotalBalance(int $userId, string $walletType = self::WALLET_TYPE_USER): array
    {
        $wallets = $this->getAllWallets($userId, $walletType);
        $baseCurrency = get_default_currency_code();
        $totalBalance = 0;

        foreach ($wallets as $wallet) {
            if ($wallet->currency) {
                $rate = $wallet->currency->rate ?? 1;
                $totalBalance += $wallet->balance * $rate;
            }
        }

        return [
            'total_balance' => round($totalBalance, 2),
            'currency' => $baseCurrency,
            'wallet_count' => $wallets->count(),
        ];
    }

    /**
     * Add funds to wallet
     *
     * @param int $userId
     * @param string $currencyCode
     * @param float $amount
     * @param string $walletType
     * @return bool
     * @throws Exception
     */
    public function addFunds(int $userId, string $currencyCode, float $amount, string $walletType = self::WALLET_TYPE_USER): bool
    {
        if ($amount <= 0) {
            throw new Exception(__('Amount must be greater than zero'));
        }

        $wallet = $this->getWalletByCurrency($userId, $currencyCode, $walletType);
        
        if (!$wallet) {
            throw new Exception(__('Wallet not found'));
        }

        if (!$wallet->status) {
            throw new Exception(__('Wallet is inactive'));
        }

        return DB::transaction(function () use ($wallet, $amount) {
            $wallet->balance += $amount;
            return $wallet->save();
        });
    }

    /**
     * Deduct funds from wallet
     *
     * @param int $userId
     * @param string $currencyCode
     * @param float $amount
     * @param string $walletType
     * @return bool
     * @throws Exception
     */
    public function deductFunds(int $userId, string $currencyCode, float $amount, string $walletType = self::WALLET_TYPE_USER): bool
    {
        if ($amount <= 0) {
            throw new Exception(__('Amount must be greater than zero'));
        }

        $wallet = $this->getWalletByCurrency($userId, $currencyCode, $walletType);
        
        if (!$wallet) {
            throw new Exception(__('Wallet not found'));
        }

        if (!$wallet->status) {
            throw new Exception(__('Wallet is inactive'));
        }

        if ($wallet->balance < $amount) {
            throw new Exception(__('Insufficient balance'));
        }

        return DB::transaction(function () use ($wallet, $amount) {
            $wallet->balance -= $amount;
            return $wallet->save();
        });
    }

    /**
     * Transfer funds between wallets of the same user
     *
     * @param int $userId
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @param string $walletType
     * @return array
     * @throws Exception
     */
    public function internalTransfer(
        int $userId,
        string $fromCurrency,
        string $toCurrency,
        float $amount,
        string $walletType = self::WALLET_TYPE_USER
    ): array {
        if ($amount <= 0) {
            throw new Exception(__('Amount must be greater than zero'));
        }

        $fromWallet = $this->getWalletByCurrency($userId, $fromCurrency, $walletType);
        $toWallet = $this->getWalletByCurrency($userId, $toCurrency, $walletType);

        if (!$fromWallet || !$toWallet) {
            throw new Exception(__('One or both wallets not found'));
        }

        if (!$fromWallet->status || !$toWallet->status) {
            throw new Exception(__('One or both wallets are inactive'));
        }

        if ($fromWallet->balance < $amount) {
            throw new Exception(__('Insufficient balance'));
        }

        // Calculate conversion rate
        $fromRate = $fromWallet->currency->rate ?? 1;
        $toRate = $toWallet->currency->rate ?? 1;
        $conversionRate = $fromRate / $toRate;
        $convertedAmount = $amount * $conversionRate;

        return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $convertedAmount, $conversionRate) {
            $fromWallet->balance -= $amount;
            $fromWallet->save();

            $toWallet->balance += $convertedAmount;
            $toWallet->save();

            return [
                'from_wallet' => [
                    'currency' => $fromWallet->currency->code,
                    'amount_deducted' => round($amount, 8),
                    'new_balance' => round($fromWallet->balance, 8),
                ],
                'to_wallet' => [
                    'currency' => $toWallet->currency->code,
                    'amount_added' => round($convertedAmount, 8),
                    'new_balance' => round($toWallet->balance, 8),
                ],
                'conversion_rate' => round($conversionRate, 8),
            ];
        });
    }

    /**
     * Get wallet transaction history
     *
     * @param int $userId
     * @param string|null $currencyCode
     * @param int $limit
     * @param string $walletType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionHistory(
        int $userId,
        ?string $currencyCode = null,
        int $limit = 20,
        string $walletType = self::WALLET_TYPE_USER
    ) {
        $query = Transaction::where('user_id', $userId);

        if ($currencyCode) {
            $wallet = $this->getWalletByCurrency($userId, $currencyCode, $walletType);
            if ($wallet) {
                $query->where('creator_wallet_id', $wallet->id);
            }
        }

        return $query->latest()->take($limit)->get();
    }

    /**
     * Get wallet statistics
     *
     * @param int $userId
     * @param string $walletType
     * @return array
     */
    public function getWalletStatistics(int $userId, string $walletType = self::WALLET_TYPE_USER): array
    {
        $wallets = $this->getAllWallets($userId, $walletType);
        $fiatWallets = $this->getWalletsByCurrencyType($userId, GlobalConst::FIAT, $walletType);
        $cryptoWallets = $this->getWalletsByCurrencyType($userId, GlobalConst::CRYPTO, $walletType);

        $totalBalance = $this->getTotalBalance($userId, $walletType);

        return [
            'total_wallets' => $wallets->count(),
            'fiat_wallets' => $fiatWallets->count(),
            'crypto_wallets' => $cryptoWallets->count(),
            'active_wallets' => $wallets->where('status', true)->count(),
            'inactive_wallets' => $wallets->where('status', false)->count(),
            'total_balance' => $totalBalance['total_balance'],
            'base_currency' => $totalBalance['currency'],
            'fiat_balance' => $fiatWallets->sum('balance'),
            'crypto_wallets_with_balance' => $cryptoWallets->where('balance', '>', 0)->count(),
        ];
    }

    /**
     * Check if wallet exists for user
     *
     * @param int $userId
     * @param string $currencyCode
     * @param string $walletType
     * @return bool
     */
    public function walletExists(int $userId, string $currencyCode, string $walletType = self::WALLET_TYPE_USER): bool
    {
        return $this->getWalletByCurrency($userId, $currencyCode, $walletType) !== null;
    }

    /**
     * Check if wallet has sufficient balance
     *
     * @param int $userId
     * @param string $currencyCode
     * @param float $amount
     * @param string $walletType
     * @return bool
     */
    public function hasSufficientBalance(int $userId, string $currencyCode, float $amount, string $walletType = self::WALLET_TYPE_USER): bool
    {
        $balance = $this->getBalance($userId, $currencyCode, $walletType);
        return $balance >= $amount;
    }

    /**
     * Get supported currencies
     *
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSupportedCurrencies(?string $type = null)
    {
        $query = Currency::where('status', GlobalConst::ACTIVE);
        
        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Format wallet data for API response
     *
     * @param \Illuminate\Database\Eloquent\Model $wallet
     * @return array
     */
    public function formatWalletForApi($wallet): array
    {
        return [
            'id' => $wallet->id,
            'currency_code' => $wallet->currency->code ?? 'N/A',
            'currency_name' => $wallet->currency->name ?? 'N/A',
            'currency_symbol' => $wallet->currency->symbol ?? '',
            'currency_type' => $wallet->currency->type ?? 'fiat',
            'balance' => round((float) $wallet->balance, 8),
            'formatted_balance' => getAmount($wallet->balance, 2) . ' ' . ($wallet->currency->code ?? ''),
            'status' => $wallet->status ? 'active' : 'inactive',
            'rate' => $wallet->currency->rate ?? 1,
            'flag' => $wallet->currency->flag ?? null,
            'created_at' => $wallet->created_at,
            'updated_at' => $wallet->updated_at,
        ];
    }

    /**
     * Get wallet class based on type
     *
     * @param string $walletType
     * @return string
     */
    private function getWalletClass(string $walletType): string
    {
        return match ($walletType) {
            self::WALLET_TYPE_USER => UserWallet::class,
            self::WALLET_TYPE_AGENT => AgentWallet::class,
            self::WALLET_TYPE_MERCHANT => MerchantWallet::class,
            default => UserWallet::class
        };
    }

    /**
     * Get owner field name based on wallet type
     *
     * @param string $walletType
     * @return string
     */
    private function getOwnerField(string $walletType): string
    {
        return match ($walletType) {
            self::WALLET_TYPE_USER => 'user_id',
            self::WALLET_TYPE_AGENT => 'agent_id',
            self::WALLET_TYPE_MERCHANT => 'merchant_id',
            default => 'user_id'
        };
    }
}
