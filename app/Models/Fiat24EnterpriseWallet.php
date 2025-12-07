<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Fiat24 Enterprise Wallet Model
 * 
 * Represents a multi-signature multi-chain enterprise wallet
 * - Multiple blockchain support (Arbitrum, Mantle, etc.)
 * - Multi-signature security
 * - Enterprise treasury management
 * - DeFi integration capabilities
 * - Delegated access features
 */
class Fiat24EnterpriseWallet extends Model
{
    use HasFactory;

    protected $table = 'fiat24_enterprise_wallets';
    
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'wallet_name',
        'supported_chains',
        'primary_chain_id',
        'required_signatures',
        'total_signers',
        'signer_addresses',
        'signer_roles',
        'chain_addresses',
        'balances',
        'total_usd_value',
        'enterprise_type',
        'smart_contract_enabled',
        'defi_enabled',
        'automated_treasury',
        'fiat24_enterprise_nft_id',
        'delegated_access',
        'status',
        'security_settings',
        'compliance_settings',
        'last_activity_at',
        'metadata',
    ];
    
    protected $casts = [
        'user_id' => 'integer',
        'supported_chains' => 'array',
        'required_signatures' => 'integer',
        'total_signers' => 'integer',
        'signer_addresses' => 'array',
        'signer_roles' => 'array',
        'chain_addresses' => 'array',
        'balances' => 'array',
        'total_usd_value' => 'double',
        'smart_contract_enabled' => 'boolean',
        'defi_enabled' => 'boolean',
        'automated_treasury' => 'boolean',
        'delegated_access' => 'boolean',
        'status' => 'boolean',
        'security_settings' => 'array',
        'compliance_settings' => 'array',
        'last_activity_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Scope to get authenticated user's wallets
     */
    public function scopeAuth($query)
    {
        return $query->where('user_id', auth()->user()->id);
    }

    /**
     * Scope to get active wallets
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get wallets by enterprise type
     */
    public function scopeType($query, $type)
    {
        return $query->where('enterprise_type', $type);
    }

    /**
     * Get the user that owns the wallet
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get pending approvals for this wallet
     */
    public function pendingApprovals()
    {
        return $this->hasMany(Fiat24EnterpriseWalletApproval::class, 'wallet_id')
                    ->where('status', 'pending');
    }

    /**
     * Get all approvals for this wallet
     */
    public function approvals()
    {
        return $this->hasMany(Fiat24EnterpriseWalletApproval::class, 'wallet_id');
    }

    /**
     * Get balance for specific currency on specific chain
     */
    public function getBalance($currency, $chainId = null)
    {
        $chainId = $chainId ?? $this->primary_chain_id;
        $balances = $this->balances ?? [];
        
        return $balances[$chainId][$currency] ?? 0;
    }

    /**
     * Update balance for specific currency on specific chain
     */
    public function updateBalance($currency, $amount, $chainId = null)
    {
        $chainId = $chainId ?? $this->primary_chain_id;
        $balances = $this->balances ?? [];
        
        if (!isset($balances[$chainId])) {
            $balances[$chainId] = [];
        }
        
        $balances[$chainId][$currency] = $amount;
        $this->balances = $balances;
        
        return $this->save();
    }

    /**
     * Add funds to specific currency on specific chain
     */
    public function addFunds($currency, $amount, $chainId = null)
    {
        $chainId = $chainId ?? $this->primary_chain_id;
        $currentBalance = $this->getBalance($currency, $chainId);
        
        return $this->updateBalance($currency, $currentBalance + $amount, $chainId);
    }

    /**
     * Deduct funds from specific currency on specific chain
     */
    public function deductFunds($currency, $amount, $chainId = null)
    {
        $chainId = $chainId ?? $this->primary_chain_id;
        $currentBalance = $this->getBalance($currency, $chainId);
        
        // Use epsilon for floating point comparison
        $epsilon = 0.00000001;
        if (($currentBalance + $epsilon) < $amount) {
            return false;
        }
        
        return $this->updateBalance($currency, $currentBalance - $amount, $chainId);
    }

    /**
     * Check if address is authorized signer
     */
    public function isAuthorizedSigner($address)
    {
        return in_array(strtolower($address), array_map('strtolower', $this->signer_addresses ?? []));
    }

    /**
     * Get wallet address for specific chain
     */
    public function getChainAddress($chainId)
    {
        $addresses = $this->chain_addresses ?? [];
        return $addresses[$chainId] ?? null;
    }

    /**
     * Check if wallet supports specific chain
     */
    public function supportsChain($chainId)
    {
        return in_array($chainId, $this->supported_chains ?? []);
    }

    /**
     * Get total balance across all chains in USD
     */
    public function getTotalUsdValue()
    {
        return $this->total_usd_value;
    }

    /**
     * Update last activity timestamp
     */
    public function touchActivity()
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Check if multi-sig threshold is met
     */
    public function isMultiSigThresholdMet($approvalsCount)
    {
        return $approvalsCount >= $this->required_signatures;
    }

    /**
     * Get wallet display name with chain info
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->wallet_name} (Multi-Chain Enterprise)";
    }

    /**
     * Get formatted signer list
     */
    public function getFormattedSignersAttribute()
    {
        $signers = $this->signer_addresses ?? [];
        return array_map(function($address) {
            return substr($address, 0, 6) . '...' . substr($address, -4);
        }, $signers);
    }

    /**
     * Check if wallet has DeFi capabilities
     */
    public function canUseDeFi()
    {
        return $this->status && $this->defi_enabled && $this->smart_contract_enabled;
    }

    /**
     * Check if wallet has automated treasury
     */
    public function hasAutomatedTreasury()
    {
        return $this->status && $this->automated_treasury;
    }

    /**
     * Get blockchain explorer URLs for all chains
     */
    public function getExplorerUrlsAttribute()
    {
        $urls = [];
        $explorers = [
            '42161' => 'https://arbiscan.io',
            '5000' => 'https://explorer.mantle.xyz',
            '1' => 'https://etherscan.io',
            '56' => 'https://bscscan.com',
            '137' => 'https://polygonscan.com',
        ];

        foreach ($this->supported_chains ?? [] as $chainId) {
            $address = $this->getChainAddress($chainId);
            if ($address && isset($explorers[$chainId])) {
                $urls[$chainId] = $explorers[$chainId] . '/address/' . $address;
            }
        }

        return $urls;
    }

    /**
     * Get enterprise tier benefits
     */
    public function getTierBenefitsAttribute()
    {
        $tiers = [
            'standard' => [
                'max_signers' => 5,
                'max_chains' => 3,
                'defi_access' => false,
                'api_rate_limit' => 100,
            ],
            'premium' => [
                'max_signers' => 10,
                'max_chains' => 5,
                'defi_access' => true,
                'api_rate_limit' => 500,
            ],
            'enterprise' => [
                'max_signers' => 20,
                'max_chains' => 10,
                'defi_access' => true,
                'api_rate_limit' => 1000,
            ],
        ];

        return $tiers[$this->enterprise_type] ?? $tiers['standard'];
    }
}
