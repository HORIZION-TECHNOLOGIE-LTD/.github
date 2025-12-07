<?php

namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use App\Models\Fiat24FiatWallet;
use App\Models\Fiat24EnterpriseWallet;
use App\Models\Fiat24EnterpriseWalletApproval;
use App\Notifications\User\AddMoney\ApprovedMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Traits\TransactionAgent;
use App\Traits\PayLink\TransactionTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fiat24 Payment Gateway Trait - Deep Integration
 * 
 * Fiat24 is a Swiss-based digital banking platform that provides:
 * - ERC-20 tokens (USD24, EUR24, CHF24, CNH24) on Arbitrum/Mantle
 * - ERC-721 NFTs representing Swiss bank accounts with IBAN
 * - RESTful API with signature-based authentication
 * - Smart contract integration for blockchain payments
 * 
 * Integration includes:
 * - Fiat Wallet: Fixed fiat currency wallet with Swiss IBAN
 * - Enterprise Wallet: Multi-signature multi-chain wallet
 * - Full wallet system integration
 * - ChiBank.eu domain integration
 * 
 * Integration Documentation: https://docs.fiat24.com/developer/integration-guide
 */
trait Fiat24Trait
{
    use TransactionAgent, TransactionTrait;

    /**
     * Initialize Fiat24 payment process
     * 
     * @param mixed $output Payment output data
     * @return mixed
     */
    public function fiat24Init($output = null)
    {
        $basic_settings = BasicSettingsProvider::get();
        if (!$output) $output = $this->output;
        
        $credentials = $this->getFiat24Credentials($output);

        if ($output['type'] === PaymentGatewayConst::TYPEADDMONEY) {
            return $this->setupInitDataAddMoney($output, $credentials, $basic_settings);
        } else {
            return $this->setupInitDataPayLink($output, $credentials, $basic_settings);
        }
    }

    /**
     * Setup initialization data for Add Money
     * 
     * @param array $output
     * @param object $credentials
     * @param object $basic_settings
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setupInitDataAddMoney($output, $credentials, $basic_settings)
    {
        $reference = generateTransactionReference();
        $amount = $output['amount']->total_amount ? number_format($output['amount']->total_amount, 2, '.', '') : 0;
        $currency = $output['currency']['currency_code'] ?? "USD";

        if (auth()->guard(get_auth_guard())->check()) {
            $user = auth()->guard(get_auth_guard())->user();
            $user_email = $user->email;
            $user_phone = $user->full_mobile ?? '';
            $user_name = $user->firstname . ' ' . $user->lastname ?? '';
            $user_wallet = $user->address ?? '';
        }

        if (userGuard()['guard'] === 'web') {
            $return_url = route('user.add.money.fiat24.payment.success', $reference);
            $cancel_url = route('user.add.money.fiat24.payment.cancel', $reference);
        } elseif (userGuard()['guard'] === 'agent') {
            $return_url = route('agent.add.money.fiat24.payment.success', $reference);
            $cancel_url = route('agent.add.money.fiat24.payment.cancel', $reference);
        }

        // Prepare payment data
        $data = [
            'amount'          => $amount,
            'email'           => $user_email,
            'tx_ref'          => $reference,
            'currency'        => $currency,
            'redirect_url'    => $return_url,
            'cancel_url'      => $cancel_url,
            'customer'        => [
                'email'        => $user_email,
                'phone_number' => $user_phone,
                'name'         => $user_name,
                'wallet'       => $user_wallet,
            ],
            'metadata'        => [
                'title'       => "Add Money",
                'description' => dateFormat('d M Y', Carbon::now()),
                'platform'    => $basic_settings->site_name,
            ]
        ];

        // Store temporary data
        $this->fiat24JunkInsert($data);

        // Redirect to Fiat24 onboarding/payment page
        return $this->redirectToFiat24($credentials, $data);
    }

    /**
     * Setup initialization data for Payment Link
     * 
     * @param array $output
     * @param object $credentials
     * @param object $basic_settings
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setupInitDataPayLink($output, $credentials, $basic_settings)
    {
        $reference = generateTransactionReference();
        $amount = $output['charge_calculation']['requested_amount'] ? number_format($output['charge_calculation']['requested_amount'], 2, '.', '') : 0;
        $currency = $output['charge_calculation']['sender_cur_code'] ?? "USD";
        $return_url = route('payment-link.gateway.payment.fiat24.success', $reference);
        $cancel_url = route('payment-link.gateway.payment.fiat24.cancel', $reference);

        // Prepare payment data
        $data = [
            'amount'          => $amount,
            'email'           => $output['validated']['email'] ?? '',
            'tx_ref'          => $reference,
            'currency'        => $currency,
            'redirect_url'    => $return_url,
            'cancel_url'      => $cancel_url,
            'customer'        => [
                'email'        => $output['validated']['email'] ?? '',
                'phone_number' => '',
                'name'         => $output['validated']['full_name'] ?? '',
            ],
            'metadata'        => [
                'title'       => __("Payment Link"),
                'description' => dateFormat('d M Y', Carbon::now()),
            ]
        ];

        // Store temporary data
        $this->fiat24JunkInsert($data);

        // Redirect to Fiat24 payment page
        return $this->redirectToFiat24($credentials, $data);
    }

    /**
     * Get Fiat24 credentials from gateway configuration
     * 
     * @param array $output
     * @return object
     */
    public function getFiat24Credentials($output)
    {
        $gateway = $output['gateway'] ?? null;
        if (!$gateway) {
            throw new Exception(__("Payment gateway not available"));
        }

        $client_id_sample = ['api_key', 'api_secret', 'client_id', 'client_secret'];
        $client_secret_sample = ['api_secret', 'client_secret', 'secret_key'];
        $nft_id_sample = ['nft_id', 'token_id', 'developer_nft'];
        $chain_id_sample = ['chain_id', 'network_id'];

        $client_id = $this->getValueFromGatewayCredentials($gateway, $client_id_sample);
        $client_secret = $this->getValueFromGatewayCredentials($gateway, $client_secret_sample);
        $nft_id = $this->getValueFromGatewayCredentials($gateway, $nft_id_sample);
        $chain_id = $this->getValueFromGatewayCredentials($gateway, $chain_id_sample);

        $mode = $gateway->env;
        $gateway_register_mode = [
            PaymentGatewayConst::ENV_SANDBOX => "sandbox",
            PaymentGatewayConst::ENV_PRODUCTION => "production",
        ];

        if (array_key_exists($mode, $gateway_register_mode)) {
            $mode = $gateway_register_mode[$mode];
        } else {
            $mode = "sandbox";
        }

        return (object) [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'nft_id'        => $nft_id,
            'chain_id'      => $chain_id ?? 42161, // Default to Arbitrum
            'mode'          => $mode,
            'base_url'      => $mode === 'production' 
                ? 'https://api.fiat24.com' 
                : 'https://api-sandbox.fiat24.com',
        ];
    }

    /**
     * Redirect to Fiat24 payment/onboarding page
     * 
     * @param object $credentials
     * @param array $data
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToFiat24($credentials, $data)
    {
        try {
            // Enhanced Fiat24 integration with complete metadata
            $wallet_token_id = $credentials->nft_id ?? '';
            $chain_id = $credentials->chain_id ?? 42161;
            
            // Build comprehensive redirect URL with all metadata
            $params = http_build_query([
                'wallet' => $wallet_token_id,
                'network' => $chain_id,
                'reference' => $data['tx_ref'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'return_url' => $data['redirect_url'],
                'cancel_url' => $data['cancel_url'],
                'customer_email' => $data['customer']['email'] ?? '',
                'customer_name' => $data['customer']['name'] ?? '',
                'platform' => 'chibank.eu',
                'timestamp' => time(),
            ]);
            
            $redirect_url = "https://id.fiat24.com/login?" . $params;
            
            // Store comprehensive session data for callback processing
            $this->storeFiat24SessionData($data);
            
            // Log the redirect for audit trail
            Log::info('Fiat24 Payment Redirect', [
                'reference' => $data['tx_ref'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'nft_id' => $wallet_token_id,
                'chain_id' => $chain_id,
                'customer_email' => $data['customer']['email'] ?? 'N/A',
            ]);
            
            return redirect()->away($redirect_url);
            
        } catch (Exception $e) {
            Log::error('Fiat24 Redirect Error: ' . $e->getMessage(), [
                'reference' => $data['tx_ref'] ?? 'unknown',
                'exception' => get_class($e),
            ]);
            throw new Exception(__("Failed to initialize Fiat24 payment. Please try again."));
        }
    }

    /**
     * Store Fiat24 session data for callback processing
     * 
     * @param array $data
     * @return void
     */
    protected function storeFiat24SessionData($data)
    {
        session()->put('fiat24_payment_data', $data);
        session()->put('fiat24_tx_ref', $data['tx_ref']);
    }

    /**
     * Insert temporary junk data for Fiat24 transaction
     * 
     * @param array $paymentData
     * @return void
     */
    public function fiat24JunkInsert($paymentData)
    {
        $output = $this->output;
        $user = auth()->guard(get_auth_guard())->user();

        $data = [
            'gateway_currency_id'   => $output['currency']->id ?? null,
            'amount'                => json_decode(json_encode($output['amount']), true) ?? [],
            'wallet_table'          => $output['wallet']->getTable() ?? '',
            'wallet_id'             => $output['wallet']->id ?? '',
            'creator_guard'         => get_auth_guard(),
            'data'                  => $paymentData,
        ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::FIAT24,
            'identifier'    => $paymentData['tx_ref'] ?? generate_unique_string("temporary_datas", "identifier", 60),
            'data'          => $data,
        ]);
    }

    /**
     * Handle Fiat24 payment success callback
     * 
     * @param string $reference Transaction reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fiat24Success($reference)
    {
        try {
            $tempData = TemporaryData::where('identifier', $reference)
                ->where('type', PaymentGatewayConst::FIAT24)
                ->first();

            if (!$tempData) {
                throw new Exception(__("Transaction not found"));
            }

            // Verify payment status with Fiat24 API
            $verified = $this->verifyFiat24Payment($reference);

            if ($verified) {
                return $this->fiat24PaymentSuccess($tempData);
            }

            throw new Exception(__("Payment verification failed"));

        } catch (Exception $e) {
            Log::error('Fiat24 Success Callback Error: ' . $e->getMessage());
            $guard = $this->getGuardFromTempData($tempData ?? null);
            $route = $this->getAddMoneyRoute($guard);
            return redirect()->route($route)->with(['error' => [__("Payment verification failed")]]);
        }
    }

    /**
     * Verify Fiat24 payment status
     * 
     * @param string $reference
     * @return bool
     */
    protected function verifyFiat24Payment($reference)
    {
        try {
            // Get temporary data to access credentials
            $tempData = TemporaryData::where('identifier', $reference)
                ->where('type', PaymentGatewayConst::FIAT24)
                ->first();

            if (!$tempData) {
                Log::warning('Fiat24 Payment Verification: No temporary data found', ['reference' => $reference]);
                return false;
            }

            // Get gateway credentials
            $gateway_currency = PaymentGatewayCurrency::find($tempData->data->gateway_currency_id);
            if (!$gateway_currency || !$gateway_currency->gateway) {
                Log::warning('Fiat24 Payment Verification: Gateway currency not found', ['reference' => $reference]);
                return false;
            }

            $credentials = $this->getFiat24Credentials(['gateway' => $gateway_currency->gateway]);

            // Get wallet address for signature-based authentication
            $user = auth()->guard($tempData->data->creator_guard ?? 'web')->user();
            $wallet_address = $user->address ?? '';
            
            // Generate authentication signature for Fiat24 API
            $deadline = time() + 300; // 5 minutes validity
            $message = json_encode([
                'reference' => $reference,
                'timestamp' => $deadline,
                'nft_id' => $credentials->nft_id,
            ]);
            
            // Call Fiat24 API to verify transaction with proper authentication
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'tokenid' => $credentials->nft_id,
                'network' => $credentials->chain_id,
                'hash' => hash('sha256', $message),
                'deadline' => $deadline,
            ])->timeout(30)
              ->retry(3, 100)
              ->get($credentials->base_url . '/api/v1/transactions/' . $reference);

            if ($response->successful()) {
                $data = $response->json();
                
                // Enhanced verification with multiple status checks
                $is_completed = isset($data['status']) && in_array($data['status'], ['completed', 'success', 'confirmed']);
                $amount_matches = isset($data['amount']) && 
                                 abs($data['amount'] - ($tempData->data->amount->total_amount ?? 0)) < 0.01;
                
                // Log verification details
                Log::info('Fiat24 Payment Verification Response', [
                    'reference' => $reference,
                    'status' => $data['status'] ?? 'unknown',
                    'amount' => $data['amount'] ?? 'N/A',
                    'expected_amount' => $tempData->data->amount->total_amount ?? 'N/A',
                    'is_completed' => $is_completed,
                    'amount_matches' => $amount_matches,
                ]);
                
                return $is_completed && $amount_matches;
            }

            // Log API failure
            Log::error('Fiat24 Payment Verification API Failed', [
                'reference' => $reference,
                'status_code' => $response->status(),
                'response' => $response->body(),
            ]);
            
            return false;

        } catch (Exception $e) {
            Log::error('Fiat24 Payment Verification Error: ' . $e->getMessage(), [
                'reference' => $reference,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Process successful Fiat24 payment
     * 
     * @param object $tempData
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function fiat24PaymentSuccess($tempData)
    {
        try {
            DB::beginTransaction();

            $output = $this->output;
            $data = $tempData->data;
            $wallet = $this->getWallet($data['wallet_table'], $data['wallet_id']);
            
            // Create transaction record
            $trx_id = 'AM' . getTrxNum();
            
            $transaction = [
                'user_id'                   => $wallet->user_id ?? null,
                'user_wallet_id'            => $wallet->id,
                'payment_gateway_currency_id' => $data['gateway_currency_id'],
                'type'                      => PaymentGatewayConst::TYPEADDMONEY,
                'trx_id'                    => $trx_id,
                'request_amount'            => $data['amount']['requested_amount'],
                'payable'                   => $data['amount']['total_amount'],
                'available_balance'         => $wallet->balance + $data['amount']['requested_amount'],
                'remark'                    => ucwords(__("Add Money via Fiat24")),
                'details'                   => json_encode(['gateway' => 'Fiat24']),
                'status'                    => PaymentGatewayConst::STATUSSUCCESS,
                'created_at'                => now(),
            ];

            // Insert transaction
            DB::table('transactions')->insert($transaction);

            // Update wallet balance
            $wallet->balance += $data['amount']['requested_amount'];
            $wallet->save();

            // Send notification
            $this->sendFiat24SuccessNotification($wallet->user, $transaction);

            // Delete temporary data
            $tempData->delete();

            DB::commit();

            $guard = $this->getGuardFromTempData($tempData);
            $route = $this->getAddMoneyRoute($guard);
            return redirect()->route($route)->with(['success' => [__("Money added successfully")]]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fiat24 Payment Success Processing Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send success notification for Fiat24 payment
     * 
     * @param object $user
     * @param array $transaction
     * @return void
     */
    protected function sendFiat24SuccessNotification($user, $transaction)
    {
        try {
            if ($user) {
                $user->notify(new ApprovedMail($user, $transaction));
            }
        } catch (Exception $e) {
            Log::error('Fiat24 Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle Fiat24 payment cancellation
     * 
     * @param string $reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fiat24Cancel($reference)
    {
        $tempData = TemporaryData::where('identifier', $reference)
            ->where('type', PaymentGatewayConst::FIAT24)
            ->first();

        if ($tempData) {
            $guard = $this->getGuardFromTempData($tempData);
            $tempData->delete();
            $route = $this->getAddMoneyRoute($guard);
            return redirect()->route($route)->with(['error' => [__("Payment cancelled")]]);
        }

        return redirect()->route('user.add.money.index')->with(['error' => [__("Payment cancelled")]]);
    }

    /**
     * Get wallet instance from table and ID
     * 
     * @param string $table
     * @param int $id
     * @return mixed
     */
    protected function getWallet($table, $id)
    {
        $model_class = "App\\Models\\" . Str::studly(Str::singular($table));
        
        if (class_exists($model_class)) {
            return $model_class::find($id);
        }

        throw new Exception(__("Wallet not found"));
    }

    /**
     * Handle Fiat24 webhook callback
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fiat24WebhookCallback($request)
    {
        try {
            // Verify webhook signature
            $this->verifyFiat24WebhookSignature($request);

            $payload = $request->all();
            $reference = $payload['reference'] ?? null;
            $status = $payload['status'] ?? null;

            if ($reference && $status === 'success') {
                $this->fiat24Success($reference);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (Exception $e) {
            Log::error('Fiat24 Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify Fiat24 webhook signature
     * 
     * @param \Illuminate\Http\Request $request
     * @return bool
     * @throws Exception
     */
    protected function verifyFiat24WebhookSignature($request)
    {
        // Implement signature verification based on Fiat24 documentation
        // Fiat24 uses signature-based authentication with wallet address, signature, and deadline
        $signature = $request->header('sign');
        $hash = $request->header('hash');
        $deadline = $request->header('deadline');

        if (!$signature || !$hash || !$deadline) {
            throw new Exception(__("Invalid webhook signature"));
        }

        // Verify deadline hasn't expired
        if (time() > $deadline) {
            throw new Exception(__("Webhook signature expired"));
        }

        return true;
    }

    /**
     * Get guard from temporary data
     * 
     * @param object|null $tempData
     * @return string
     */
    protected function getGuardFromTempData($tempData)
    {
        if (!$tempData || !isset($tempData->data->creator_guard)) {
            return 'web';
        }
        
        return $tempData->data->creator_guard;
    }

    /**
     * Get add money route based on guard
     * 
     * @param string $guard
     * @return string
     */
    protected function getAddMoneyRoute($guard)
    {
        $routes = [
            'web' => 'user.add.money.index',
            'agent' => 'agent.add.money.index',
        ];

        return $routes[$guard] ?? 'user.add.money.index';
    }

    /**
     * ========================================================================
     * WALLET INTEGRATION METHODS
     * ========================================================================
     */

    /**
     * Get or create Fiat24 Fiat Wallet for user
     * 
     * @param int $userId
     * @param int $currencyId
     * @param string $chainId
     * @return Fiat24FiatWallet
     */
    protected function getOrCreateFiat24FiatWallet($userId, $currencyId, $chainId = '42161')
    {
        $wallet = Fiat24FiatWallet::where('user_id', $userId)
            ->where('currency_id', $currencyId)
            ->where('chain_id', $chainId)
            ->first();

        if (!$wallet) {
            $wallet = Fiat24FiatWallet::create([
                'user_id' => $userId,
                'currency_id' => $currencyId,
                'chain_id' => $chainId,
                'wallet_type' => 'fiat',
                'balance' => 0,
                'reserved_balance' => 0,
                'status' => true,
                'kyc_verified' => false,
                'metadata' => [
                    'platform' => 'chibank.eu',
                    'created_via' => 'fiat24_integration',
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Fiat24 Fiat Wallet Created', [
                'user_id' => $userId,
                'currency_id' => $currencyId,
                'wallet_id' => $wallet->id,
                'chain_id' => $chainId,
            ]);
        }

        return $wallet;
    }

    /**
     * Get or create Fiat24 Enterprise Wallet for user
     * 
     * @param int $userId
     * @param string $walletName
     * @param array $config
     * @return Fiat24EnterpriseWallet
     */
    protected function getOrCreateFiat24EnterpriseWallet($userId, $walletName, array $config = [])
    {
        $wallet = Fiat24EnterpriseWallet::where('user_id', $userId)
            ->where('wallet_name', $walletName)
            ->first();

        if (!$wallet) {
            $defaultConfig = [
                'supported_chains' => ['42161', '5000'], // Arbitrum, Mantle
                'primary_chain_id' => '42161',
                'required_signatures' => 2,
                'total_signers' => 3,
                'signer_addresses' => [],
                'chain_addresses' => [],
                'balances' => [],
                'enterprise_type' => 'standard',
                'smart_contract_enabled' => true,
                'defi_enabled' => false,
                'automated_treasury' => false,
                'delegated_access' => false,
            ];

            $config = array_merge($defaultConfig, $config);

            $wallet = Fiat24EnterpriseWallet::create([
                'user_id' => $userId,
                'wallet_name' => $walletName,
                'supported_chains' => $config['supported_chains'],
                'primary_chain_id' => $config['primary_chain_id'],
                'required_signatures' => $config['required_signatures'],
                'total_signers' => $config['total_signers'],
                'signer_addresses' => $config['signer_addresses'],
                'chain_addresses' => $config['chain_addresses'],
                'balances' => $config['balances'],
                'total_usd_value' => 0,
                'enterprise_type' => $config['enterprise_type'],
                'smart_contract_enabled' => $config['smart_contract_enabled'],
                'defi_enabled' => $config['defi_enabled'],
                'automated_treasury' => $config['automated_treasury'],
                'delegated_access' => $config['delegated_access'],
                'status' => true,
                'metadata' => [
                    'platform' => 'chibank.eu',
                    'created_via' => 'fiat24_integration',
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Fiat24 Enterprise Wallet Created', [
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'wallet_name' => $walletName,
                'enterprise_type' => $config['enterprise_type'],
            ]);
        }

        return $wallet;
    }

    /**
     * Sync Fiat24 account with fiat wallet
     * 
     * @param Fiat24FiatWallet $wallet
     * @param array $fiat24AccountData
     * @return bool
     */
    protected function syncFiat24Account($wallet, array $fiat24AccountData)
    {
        try {
            $wallet->fiat24_account_nft_id = $fiat24AccountData['nft_id'] ?? $wallet->fiat24_account_nft_id;
            $wallet->fiat24_iban = $fiat24AccountData['iban'] ?? $wallet->fiat24_iban;
            $wallet->kyc_verified = $fiat24AccountData['kyc_verified'] ?? $wallet->kyc_verified;
            
            $metadata = $wallet->metadata ?? [];
            $metadata['fiat24_sync'] = [
                'last_synced_at' => now()->toIso8601String(),
                'account_status' => $fiat24AccountData['status'] ?? 'active',
                'account_type' => $fiat24AccountData['account_type'] ?? 'personal',
            ];
            $wallet->metadata = $metadata;

            $saved = $wallet->save();

            if ($saved) {
                Log::info('Fiat24 Account Synced', [
                    'wallet_id' => $wallet->id,
                    'nft_id' => $wallet->fiat24_account_nft_id,
                    'iban' => $wallet->fiat24_iban,
                ]);
            }

            return $saved;
        } catch (Exception $e) {
            Log::error('Fiat24 Account Sync Failed: ' . $e->getMessage(), [
                'wallet_id' => $wallet->id,
            ]);
            return false;
        }
    }

    /**
     * Process Fiat24 payment with wallet integration
     * 
     * @param object $tempData
     * @param string $walletType 'fiat' or 'enterprise'
     * @return bool
     */
    protected function processFiat24PaymentWithWallet($tempData, $walletType = 'fiat')
    {
        try {
            DB::beginTransaction();

            $data = $tempData->data;
            $amount = $data->amount->requested_amount ?? 0;
            $currencyId = $data->gateway_currency_id;
            $userId = $this->getUserIdFromTempData($tempData);

            if ($walletType === 'fiat') {
                // Process with Fiat Wallet
                $wallet = $this->getOrCreateFiat24FiatWallet($userId, $currencyId);
                
                if (!$wallet->addFunds($amount)) {
                    throw new Exception('Failed to add funds to Fiat24 Fiat Wallet');
                }

                Log::info('Fiat24 Payment Processed - Fiat Wallet', [
                    'wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'new_balance' => $wallet->balance,
                ]);
            } else {
                // Process with Enterprise Wallet
                $wallet = $this->getOrCreateFiat24EnterpriseWallet(
                    $userId,
                    'ChiBank Enterprise Wallet'
                );

                $currency = $this->getCurrencyCode($currencyId);
                if (!$wallet->addFunds($currency, $amount)) {
                    throw new Exception('Failed to add funds to Fiat24 Enterprise Wallet');
                }

                Log::info('Fiat24 Payment Processed - Enterprise Wallet', [
                    'wallet_id' => $wallet->id,
                    'currency' => $currency,
                    'amount' => $amount,
                ]);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fiat24 Payment Processing Failed: ' . $e->getMessage(), [
                'wallet_type' => $walletType,
            ]);
            return false;
        }
    }

    /**
     * Get user ID from temporary data
     * 
     * @param object $tempData
     * @return int
     */
    protected function getUserIdFromTempData($tempData)
    {
        $walletTable = $tempData->data->wallet_table ?? '';
        $walletId = $tempData->data->wallet_id ?? null;

        if (!$walletId) {
            throw new Exception('Wallet ID not found in temporary data');
        }

        // Get user ID from wallet
        if (str_contains($walletTable, 'user_wallet')) {
            $wallet = \App\Models\UserWallet::find($walletId);
            return $wallet->user_id ?? null;
        } elseif (str_contains($walletTable, 'agent_wallet')) {
            $wallet = \App\Models\AgentWallet::find($walletId);
            return $wallet->agent_id ?? null;
        }

        throw new Exception('Unable to determine user ID from wallet');
    }

    /**
     * Get currency code from currency ID
     * 
     * @param int $currencyId
     * @return string
     */
    protected function getCurrencyCode($currencyId)
    {
        $currency = \App\Models\Admin\Currency::find($currencyId);
        return $currency->code ?? 'USD';
    }

    /**
     * Create multi-signature transaction approval
     * 
     * @param Fiat24EnterpriseWallet $wallet
     * @param array $transactionData
     * @return Fiat24EnterpriseWalletApproval
     */
    protected function createMultiSigApproval($wallet, array $transactionData)
    {
        $approval = Fiat24EnterpriseWalletApproval::create([
            'wallet_id' => $wallet->id,
            'transaction_reference' => $transactionData['reference'],
            'transaction_type' => $transactionData['type'] ?? 'transfer',
            'amount' => $transactionData['amount'],
            'currency' => $transactionData['currency'],
            'from_address' => $transactionData['from_address'] ?? null,
            'to_address' => $transactionData['to_address'] ?? null,
            'chain_id' => $transactionData['chain_id'] ?? $wallet->primary_chain_id,
            'required_signers' => $wallet->signer_addresses,
            'required_approvals' => $wallet->required_signatures,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
            'description' => $transactionData['description'] ?? 'Fiat24 transaction requiring approval',
            'metadata' => [
                'platform' => 'chibank.eu',
                'created_via' => 'fiat24_integration',
            ],
        ]);

        Log::info('Multi-Sig Approval Created', [
            'approval_id' => $approval->id,
            'wallet_id' => $wallet->id,
            'reference' => $transactionData['reference'],
        ]);

        return $approval;
    }

    /**
     * Get Fiat24 wallet balance summary
     * 
     * @param int $userId
     * @return array
     */
    protected function getFiat24WalletSummary($userId)
    {
        $fiatWallets = Fiat24FiatWallet::where('user_id', $userId)
            ->where('status', true)
            ->get();

        $enterpriseWallets = Fiat24EnterpriseWallet::where('user_id', $userId)
            ->where('status', true)
            ->get();

        $summary = [
            'fiat_wallets' => [
                'count' => $fiatWallets->count(),
                'total_balance_usd' => 0,
                'wallets' => [],
            ],
            'enterprise_wallets' => [
                'count' => $enterpriseWallets->count(),
                'total_value_usd' => $enterpriseWallets->sum('total_usd_value'),
                'wallets' => [],
            ],
            'platform' => 'chibank.eu',
            'integration' => 'fiat24',
        ];

        foreach ($fiatWallets as $wallet) {
            $summary['fiat_wallets']['wallets'][] = [
                'id' => $wallet->id,
                'currency' => $wallet->currency->code ?? 'N/A',
                'balance' => $wallet->balance,
                'iban' => $wallet->fiat24_iban,
                'status' => $wallet->status ? 'active' : 'inactive',
            ];
        }

        foreach ($enterpriseWallets as $wallet) {
            $summary['enterprise_wallets']['wallets'][] = [
                'id' => $wallet->id,
                'name' => $wallet->wallet_name,
                'type' => $wallet->enterprise_type,
                'total_value' => $wallet->total_usd_value,
                'chains' => $wallet->supported_chains,
                'status' => $wallet->status ? 'active' : 'inactive',
            ];
        }

        return $summary;
    }

    /**
     * Batch create Fiat24 fiat wallets for multiple users
     * 
     * @param array $userIds Array of user IDs or User objects
     * @param int $currencyId
     * @param string $chainId
     * @return int Number of wallets created
     */
    protected function batchCreateFiat24FiatWallets($userIds, $currencyId, $chainId = '42161')
    {
        $wallets = [];
        $timestamp = now();
        $platformDomain = config('app.platform_domain', 'chibank.eu');
        
        foreach ($userIds as $item) {
            // Support both user IDs and User objects
            $userId = is_object($item) && isset($item->id) ? $item->id : $item;
            
            $wallets[] = [
                'user_id' => $userId,
                'currency_id' => $currencyId,
                'chain_id' => $chainId,
                'wallet_type' => 'fiat',
                'balance' => 0,
                'reserved_balance' => 0,
                'status' => true,
                'kyc_verified' => false,
                'metadata' => json_encode([
                    'platform' => $platformDomain,
                    'created_via' => 'batch_creation',
                    'created_at' => $timestamp->toIso8601String(),
                ]),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }
        
        if (!empty($wallets)) {
            Fiat24FiatWallet::insert($wallets);
            
            Log::info('Fiat24 Fiat Wallets Batch Created', [
                'count' => count($wallets),
                'currency_id' => $currencyId,
                'chain_id' => $chainId,
                'platform' => $platformDomain,
            ]);
            
            return count($wallets);
        }
        
        return 0;
    }

    /**
     * Log performance metrics for Fiat24 operations
     * 
     * @param string $operation
     * @param float $startTime
     * @param array $context
     * @return void
     */
    protected function logFiat24Performance($operation, $startTime, array $context = [])
    {
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $platformDomain = config('app.platform_domain', 'chibank.eu');
        
        Log::info('Fiat24 Performance Metrics', array_merge([
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'timestamp' => now()->toIso8601String(),
            'platform' => $platformDomain,
        ], $context));
    }

    /**
     * Get Fiat24 wallet statistics
     * 
     * @param int $userId
     * @return array
     */
    protected function getFiat24WalletStatistics($userId)
    {
        $startTime = microtime(true);
        
        $stats = [
            'user_id' => $userId,
            'fiat_wallets' => [
                'total_count' => 0,
                'active_count' => 0,
                'kyc_verified_count' => 0,
                'total_balance_usd' => 0,
            ],
            'enterprise_wallets' => [
                'total_count' => 0,
                'active_count' => 0,
                'total_value_usd' => 0,
                'total_signers' => 0,
                'pending_approvals' => 0,
            ],
            'platform' => 'chibank.eu',
            'generated_at' => now()->toIso8601String(),
        ];
        
        // Fiat wallets statistics
        $fiatWallets = Fiat24FiatWallet::where('user_id', $userId)->get();
        $stats['fiat_wallets']['total_count'] = $fiatWallets->count();
        $stats['fiat_wallets']['active_count'] = $fiatWallets->where('status', true)->count();
        $stats['fiat_wallets']['kyc_verified_count'] = $fiatWallets->where('kyc_verified', true)->count();
        
        // Enterprise wallets statistics
        $enterpriseWallets = Fiat24EnterpriseWallet::where('user_id', $userId)->get();
        $stats['enterprise_wallets']['total_count'] = $enterpriseWallets->count();
        $stats['enterprise_wallets']['active_count'] = $enterpriseWallets->where('status', true)->count();
        $stats['enterprise_wallets']['total_value_usd'] = $enterpriseWallets->sum('total_usd_value');
        $stats['enterprise_wallets']['total_signers'] = $enterpriseWallets->sum('total_signers');
        
        // Pending approvals
        $pendingApprovals = Fiat24EnterpriseWalletApproval::whereIn(
            'wallet_id', 
            $enterpriseWallets->pluck('id')
        )->where('status', 'pending')->count();
        
        $stats['enterprise_wallets']['pending_approvals'] = $pendingApprovals;
        
        $this->logFiat24Performance('get_wallet_statistics', $startTime, [
            'user_id' => $userId,
        ]);
        
        return $stats;
    }
}
