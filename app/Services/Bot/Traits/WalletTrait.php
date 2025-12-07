<?php

namespace App\Services\Bot\Traits;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\Transaction;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Provides wallet operation capabilities for bots.
 * 
 * This trait can be combined with other traits for multiple inheritance.
 */
trait WalletTrait
{
    /**
     * Get wallet balance for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getWalletBalance(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $wallets = UserWallet::where('user_id', $userId)
            ->with('currency')
            ->get()
            ->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'currency_code' => $wallet->currency->code ?? 'N/A',
                    'currency_name' => $wallet->currency->name ?? 'N/A',
                    'balance' => number_format($wallet->balance, 2),
                    'status' => $wallet->status ? 'active' : 'inactive',
                ];
            });

        return [
            'success' => true,
            'wallets' => $wallets->toArray(),
            'total_wallets' => $wallets->count(),
        ];
    }

    /**
     * Transfer funds between wallets.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param float $amount
     * @param string|null $currency
     * @return array
     */
    public function transferFunds(int $senderId, int $receiverId, float $amount, ?string $currency = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Invalid amount'];
        }

        $sender = User::where('id', $senderId)->active()->first();
        if (!$sender) {
            return ['success' => false, 'error' => 'Sender not found or inactive'];
        }

        $receiver = User::where('id', $receiverId)->active()->first();
        if (!$receiver) {
            return ['success' => false, 'error' => 'Receiver not found or inactive'];
        }

        if ($senderId === $receiverId) {
            return ['success' => false, 'error' => 'Cannot transfer to yourself'];
        }

        // Get wallets
        $senderWallet = $this->getDefaultWallet($senderId, $currency);
        $receiverWallet = $this->getDefaultWallet($receiverId, $currency);

        if (!$senderWallet || !$receiverWallet) {
            return ['success' => false, 'error' => 'Wallet not found'];
        }

        if ($senderWallet->balance < $amount) {
            return ['success' => false, 'error' => 'Insufficient balance'];
        }

        try {
            DB::beginTransaction();

            // Lock wallets
            $senderWallet = UserWallet::where('id', $senderWallet->id)->lockForUpdate()->first();
            $receiverWallet = UserWallet::where('id', $receiverWallet->id)->lockForUpdate()->first();

            // Double-check balance
            if ($senderWallet->balance < $amount) {
                DB::rollBack();
                return ['success' => false, 'error' => 'Insufficient balance'];
            }

            // Execute transfer
            $senderWallet->balance -= $amount;
            $senderWallet->save();

            $receiverWallet->balance += $amount;
            $receiverWallet->save();

            $trxId = 'BOT' . $this->generateTrxId();

            // Record transactions
            $this->createTransactionRecord($sender, $senderWallet, $amount, $trxId, $receiver, PaymentGatewayConst::SEND);
            $this->createTransactionRecord($receiver, $receiverWallet, $amount, $trxId, $sender, PaymentGatewayConst::RECEIVED);

            DB::commit();

            return [
                'success' => true,
                'transaction_id' => $trxId,
                'amount' => $amount,
                'sender_balance' => number_format($senderWallet->balance, 2),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bot transfer error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Transfer failed'];
        }
    }

    /**
     * Get transaction history for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getTransactionHistory(int $userId, int $limit = 10): array
    {
        $transactions = Transaction::where('user_id', $userId)
            ->latest()
            ->take(min($limit, 50))
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'trx_id' => $tx->trx_id,
                    'type' => $tx->type,
                    'attribute' => $tx->attribute,
                    'amount' => number_format($tx->request_amount, 2),
                    'status' => $tx->status == 1 ? 'completed' : 'pending',
                    'remark' => $tx->remark,
                    'date' => $tx->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return [
            'success' => true,
            'transactions' => $transactions->toArray(),
            'total' => $transactions->count(),
        ];
    }

    /**
     * Get user's default wallet.
     *
     * @param int $userId
     * @param string|null $currency
     * @return UserWallet|null
     */
    protected function getDefaultWallet(int $userId, ?string $currency = null)
    {
        $query = UserWallet::where('user_id', $userId)->with('currency');

        if ($currency) {
            $query->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });
        } else {
            // Try default wallet first using a separate query
            $defaultWallet = UserWallet::where('user_id', $userId)
                ->with('currency')
                ->whereHas('currency', function ($q) {
                    $q->where('default', 1);
                })->first();

            if ($defaultWallet) {
                return $defaultWallet;
            }
        }

        return $query->first();
    }

    /**
     * Create a transaction record.
     *
     * @param User $user
     * @param UserWallet $wallet
     * @param float $amount
     * @param string $trxId
     * @param User $otherParty
     * @param string $attribute
     * @return void
     */
    protected function createTransactionRecord(User $user, UserWallet $wallet, float $amount, string $trxId, User $otherParty, string $attribute): void
    {
        Transaction::create([
            'user_id' => $user->id,
            'user_wallet_id' => $wallet->id,
            'type' => PaymentGatewayConst::TYPETRANSFERMONEY,
            'trx_id' => $trxId,
            'request_amount' => $amount,
            'payable' => $amount,
            'available_balance' => $wallet->balance,
            'remark' => $attribute === PaymentGatewayConst::SEND 
                ? 'Transfer via Bot to ' . $otherParty->fullname 
                : 'Transfer via Bot from ' . $otherParty->fullname,
            'attribute' => $attribute,
            'status' => 1,
            'details' => json_encode([
                'amount' => $amount,
                'other_party' => [
                    'id' => $otherParty->id,
                    'email' => $otherParty->email,
                ],
                'source' => 'bot_service',
            ]),
        ]);
    }

    /**
     * Generate a transaction ID.
     *
     * @return string
     */
    protected function generateTrxId(): string
    {
        return time() . random_int(1000, 9999);
    }
}
