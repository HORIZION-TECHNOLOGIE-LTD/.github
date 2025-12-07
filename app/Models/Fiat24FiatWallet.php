<?php

namespace App\Models;

use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Fiat24 Fiat Wallet Model
 * 
 * Represents a fixed fiat currency wallet integrated with Fiat24
 * - Swiss IBAN account
 * - ERC-721 NFT representation
 * - Traditional banking features
 * - Supports CHF, EUR, USD, CNH
 */
class Fiat24FiatWallet extends Model
{
    use HasFactory;

    protected $table = 'fiat24_fiat_wallets';
    
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'currency_id',
        'fiat24_account_nft_id',
        'fiat24_iban',
        'fiat24_account_type',
        'wallet_type',
        'balance',
        'reserved_balance',
        'status',
        'kyc_verified',
        'chain_id',
        'metadata',
    ];
    
    protected $casts = [
        'user_id' => 'integer',
        'currency_id' => 'integer',
        'balance' => 'double',
        'reserved_balance' => 'double',
        'status' => 'boolean',
        'kyc_verified' => 'boolean',
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
     * Scope to get KYC verified wallets
     */
    public function scopeKycVerified($query)
    {
        return $query->where('kyc_verified', true);
    }

    /**
     * Scope to get wallets by chain
     */
    public function scopeOnChain($query, $chainId)
    {
        return $query->where('chain_id', $chainId);
    }

    /**
     * Get the user that owns the wallet
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the currency associated with the wallet
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get available balance (total balance - reserved)
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->reserved_balance;
    }

    /**
     * Cache configuration constants
     */
    const CACHE_KEY_PREFIX = 'fiat24_fiat_balance_';
    const CACHE_TTL_SECONDS = 60; // 1 minute

    /**
     * Get balance with cache (1 minute)
     */
    public function getBalanceWithCache()
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX . $this->id,
            self::CACHE_TTL_SECONDS,
            fn() => $this->balance
        );
    }

    /**
     * Clear balance cache
     */
    public function clearBalanceCache()
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $this->id);
    }

    /**
     * Reserve balance for pending transaction
     */
    public function reserveBalance($amount)
    {
        if ($this->available_balance < $amount) {
            return false;
        }

        $this->reserved_balance += $amount;
        return $this->save();
    }

    /**
     * Release reserved balance
     */
    public function releaseBalance($amount)
    {
        $this->reserved_balance = max(0, $this->reserved_balance - $amount);
        return $this->save();
    }

    /**
     * Add funds to wallet
     */
    public function addFunds($amount)
    {
        $this->balance += $amount;
        $result = $this->save();
        
        // Clear cache after balance update
        $this->clearBalanceCache();
        
        return $result;
    }

    /**
     * Deduct funds from wallet
     */
    public function deductFunds($amount)
    {
        if ($this->available_balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $result = $this->save();
        
        // Clear cache after balance update
        $this->clearBalanceCache();
        
        return $result;
    }

    /**
     * Get wallet display name
     */
    public function getDisplayNameAttribute()
    {
        return "Fiat24 {$this->currency->code} Wallet";
    }

    /**
     * Get formatted IBAN
     */
    public function getFormattedIbanAttribute()
    {
        if (!$this->fiat24_iban) {
            return 'N/A';
        }

        // Format IBAN with spaces: CH93 0000 0000 0000 0000 0
        return trim(chunk_split($this->fiat24_iban, 4, ' '));
    }

    /**
     * Check if wallet is ready for transactions
     */
    public function isReady()
    {
        return $this->status && 
               $this->kyc_verified && 
               !empty($this->fiat24_iban) && 
               !empty($this->fiat24_account_nft_id);
    }

    /**
     * Get blockchain explorer URL for NFT
     */
    public function getNftExplorerUrlAttribute()
    {
        if (!$this->fiat24_account_nft_id) {
            return null;
        }

        $explorer = $this->chain_id == '42161' 
            ? 'https://arbiscan.io' 
            : 'https://explorer.mantle.xyz';

        return "{$explorer}/token/FIAT24_NFT_CONTRACT/{$this->fiat24_account_nft_id}";
    }
}
