<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Services\WalletService;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * Wallet API Controller
 * 
 * Provides comprehensive wallet management API endpoints
 * for mobile applications and external API consumers.
 * 
 * Endpoints:
 * - GET  /wallets          - List all wallets
 * - GET  /wallets/fiat     - List fiat wallets only
 * - GET  /wallets/crypto   - List crypto wallets only
 * - GET  /wallets/balance  - Get specific wallet balance
 * - GET  /wallets/details  - Get wallet details
 * - GET  /wallets/stats    - Get wallet statistics
 * - POST /wallets/transfer - Internal wallet transfer
 * - GET  /wallets/transactions - Get wallet transactions
 * - GET  /wallets/currencies - Get supported currencies
 */
class WalletApiController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get all user wallets
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $wallets = $this->walletService->getAllWallets($user->id);
            
            $formattedWallets = $wallets->map(function ($wallet) {
                return $this->walletService->formatWalletForApi($wallet);
            });

            $statistics = $this->walletService->getWalletStatistics($user->id);

            $data = [
                'wallets' => $formattedWallets,
                'statistics' => $statistics,
                'base_currency' => get_default_currency_code(),
            ];

            $message = ['success' => [__('Wallets retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve wallets')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get fiat wallets only
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fiatWallets()
    {
        try {
            $user = auth()->user();
            $wallets = $this->walletService->getWalletsByCurrencyType(
                $user->id, 
                GlobalConst::FIAT
            );
            
            $formattedWallets = $wallets->map(function ($wallet) {
                return $this->walletService->formatWalletForApi($wallet);
            });

            $data = [
                'wallets' => $formattedWallets,
                'total_count' => $wallets->count(),
                'total_balance' => round($wallets->sum('balance'), 2),
            ];

            $message = ['success' => [__('Fiat wallets retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve fiat wallets')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get crypto wallets only
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cryptoWallets()
    {
        try {
            $user = auth()->user();
            $wallets = $this->walletService->getWalletsByCurrencyType(
                $user->id, 
                GlobalConst::CRYPTO
            );
            
            $formattedWallets = $wallets->map(function ($wallet) {
                return $this->walletService->formatWalletForApi($wallet);
            });

            $data = [
                'wallets' => $formattedWallets,
                'total_count' => $wallets->count(),
                'wallets_with_balance' => $wallets->where('balance', '>', 0)->count(),
            ];

            $message = ['success' => [__('Crypto wallets retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve crypto wallets')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get wallet balance for specific currency
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $user = auth()->user();
            $currency = strtoupper($request->currency);
            
            $wallet = $this->walletService->getWalletByCurrency($user->id, $currency);
            
            if (!$wallet) {
                $error = ['error' => [__('Wallet not found for currency: ') . $currency]];
                return Helpers::error($error);
            }

            $data = [
                'currency' => $currency,
                'balance' => round((float) $wallet->balance, 8),
                'formatted_balance' => getAmount($wallet->balance, 2) . ' ' . $currency,
                'status' => $wallet->status ? 'active' : 'inactive',
            ];

            $message = ['success' => [__('Balance retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve balance')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get detailed wallet information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $user = auth()->user();
            $currency = strtoupper($request->currency);
            
            $wallet = $this->walletService->getWalletByCurrency($user->id, $currency);
            
            if (!$wallet) {
                $error = ['error' => [__('Wallet not found for currency: ') . $currency]];
                return Helpers::error($error);
            }

            // Get recent transactions for this wallet
            $transactions = $this->walletService->getTransactionHistory($user->id, $currency, 10);
            
            $formattedTransactions = $transactions->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'trx_id' => $tx->trx_id,
                    'type' => $tx->type,
                    'attribute' => $tx->attribute,
                    'request_amount' => getAmount($tx->request_amount, 2),
                    'payable' => getAmount($tx->payable, 2),
                    'status' => $tx->stringStatus->value ?? $tx->status,
                    'remark' => $tx->remark ?? '',
                    'created_at' => $tx->created_at,
                ];
            });

            $data = [
                'wallet' => $this->walletService->formatWalletForApi($wallet),
                'recent_transactions' => $formattedTransactions,
                'can_send' => $wallet->status && $wallet->balance > 0,
                'can_receive' => $wallet->status,
            ];

            $message = ['success' => [__('Wallet details retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve wallet details')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get wallet statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            $statistics = $this->walletService->getWalletStatistics($user->id);

            // Add additional stats
            $totalAddMoney = Transaction::where('user_id', $user->id)
                ->where('type', payment_gateway_const()::TYPEADDMONEY)
                ->where('status', 1)
                ->sum('request_amount');

            $totalWithdraw = Transaction::where('user_id', $user->id)
                ->where('type', payment_gateway_const()::TYPEMONEYOUT)
                ->where('status', 1)
                ->sum('request_amount');

            $totalSent = Transaction::where('user_id', $user->id)
                ->where('type', payment_gateway_const()::TYPETRANSFERMONEY)
                ->where('attribute', payment_gateway_const()::SEND)
                ->where('status', 1)
                ->sum('request_amount');

            $totalReceived = Transaction::where('user_id', $user->id)
                ->where('type', payment_gateway_const()::TYPETRANSFERMONEY)
                ->where('attribute', payment_gateway_const()::RECEIVED)
                ->where('status', 1)
                ->sum('request_amount');

            $data = array_merge($statistics, [
                'total_add_money' => getAmount($totalAddMoney, 2),
                'total_withdraw' => getAmount($totalWithdraw, 2),
                'total_sent' => getAmount($totalSent, 2),
                'total_received' => getAmount($totalReceived, 2),
            ]);

            $message = ['success' => [__('Wallet statistics retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve wallet statistics')]];
            return Helpers::error($error);
        }
    }

    /**
     * Internal wallet-to-wallet transfer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_currency' => 'required|string|max:10',
            'to_currency' => 'required|string|max:10|different:from_currency',
            'amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $user = auth()->user();
            $fromCurrency = strtoupper($request->from_currency);
            $toCurrency = strtoupper($request->to_currency);
            $amount = (float) $request->amount;

            // Check if user has sufficient balance
            if (!$this->walletService->hasSufficientBalance($user->id, $fromCurrency, $amount)) {
                $error = ['error' => [__('Insufficient balance in source wallet')]];
                return Helpers::error($error);
            }

            // Perform transfer
            $result = $this->walletService->internalTransfer(
                $user->id,
                $fromCurrency,
                $toCurrency,
                $amount
            );

            $data = [
                'transfer' => $result,
                'message' => sprintf(
                    __('Successfully transferred %s %s to %s wallet'),
                    getAmount($amount, 2),
                    $fromCurrency,
                    $toCurrency
                ),
            ];

            $message = ['success' => [__('Transfer completed successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [$e->getMessage()]];
            return Helpers::error($error);
        }
    }

    /**
     * Get wallet transactions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'nullable|string|max:10',
            'type' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $user = auth()->user();
            $currency = $request->currency ? strtoupper($request->currency) : null;
            $type = $request->type;
            $limit = $request->limit ?? 20;
            $page = $request->page ?? 1;

            $query = Transaction::where('user_id', $user->id);

            // Filter by currency if provided
            if ($currency) {
                $wallet = $this->walletService->getWalletByCurrency($user->id, $currency);
                if ($wallet) {
                    $query->where('creator_wallet_id', $wallet->id);
                }
            }

            // Filter by type if provided
            if ($type) {
                $query->where('type', $type);
            }

            $total = $query->count();
            $transactions = $query->latest()
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            $formattedTransactions = $transactions->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'trx_id' => $tx->trx_id,
                    'type' => $tx->type,
                    'attribute' => $tx->attribute,
                    'request_amount' => getAmount($tx->request_amount, 2),
                    'payable' => getAmount($tx->payable, 2),
                    'total_charge' => getAmount($tx->total_charge ?? 0, 2),
                    'status' => $tx->stringStatus->value ?? $tx->status,
                    'remark' => $tx->remark ?? '',
                    'created_at' => $tx->created_at,
                    'updated_at' => $tx->updated_at,
                ];
            });

            $data = [
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'has_more' => ($page * $limit) < $total,
                ],
            ];

            $message = ['success' => [__('Transactions retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve transactions')]];
            return Helpers::error($error);
        }
    }

    /**
     * Get supported currencies
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currencies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:fiat,crypto',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $type = $request->type;
            $currencies = $this->walletService->getSupportedCurrencies($type);

            $formattedCurrencies = $currencies->map(function ($currency) {
                return [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->name,
                    'symbol' => $currency->symbol,
                    'type' => $currency->type,
                    'rate' => $currency->rate,
                    'flag' => $currency->flag,
                    'sender' => (bool) $currency->sender,
                    'receiver' => (bool) $currency->receiver,
                ];
            });

            $data = [
                'currencies' => $formattedCurrencies,
                'total_count' => $currencies->count(),
                'base_currency' => get_default_currency_code(),
            ];

            $message = ['success' => [__('Currencies retrieved successfully')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to retrieve currencies')]];
            return Helpers::error($error);
        }
    }

    /**
     * Check wallet balance sufficiency
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string|max:10',
            'amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            $error = ['error' => $validator->errors()->all()];
            return Helpers::validation($error);
        }

        try {
            $user = auth()->user();
            $currency = strtoupper($request->currency);
            $amount = (float) $request->amount;

            $wallet = $this->walletService->getWalletByCurrency($user->id, $currency);
            
            if (!$wallet) {
                $error = ['error' => [__('Wallet not found for currency: ') . $currency]];
                return Helpers::error($error);
            }

            $hasSufficientBalance = $this->walletService->hasSufficientBalance(
                $user->id, 
                $currency, 
                $amount
            );

            $data = [
                'currency' => $currency,
                'requested_amount' => getAmount($amount, 2),
                'available_balance' => getAmount($wallet->balance, 2),
                'sufficient' => $hasSufficientBalance,
                'shortfall' => $hasSufficientBalance ? 0 : getAmount($amount - $wallet->balance, 2),
            ];

            $message = ['success' => [__('Balance check completed')]];
            return Helpers::success($data, $message);
        } catch (Exception $e) {
            $error = ['error' => [__('Failed to check balance')]];
            return Helpers::error($error);
        }
    }
}
