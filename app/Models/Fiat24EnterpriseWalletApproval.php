<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Fiat24 Enterprise Wallet Approval Model
 * 
 * Tracks multi-signature approval process for enterprise wallet transactions
 */
class Fiat24EnterpriseWalletApproval extends Model
{
    use HasFactory;

    protected $table = 'fiat24_enterprise_wallet_approvals';
    
    public $timestamps = true;
    
    protected $fillable = [
        'wallet_id',
        'transaction_hash',
        'transaction_reference',
        'transaction_type',
        'amount',
        'currency',
        'from_address',
        'to_address',
        'chain_id',
        'required_signers',
        'approved_by',
        'rejected_by',
        'approvals_count',
        'rejections_count',
        'required_approvals',
        'status',
        'executed_at',
        'expires_at',
        'description',
        'metadata',
    ];
    
    protected $casts = [
        'wallet_id' => 'integer',
        'amount' => 'double',
        'required_signers' => 'array',
        'approved_by' => 'array',
        'rejected_by' => 'array',
        'approvals_count' => 'integer',
        'rejections_count' => 'integer',
        'required_approvals' => 'integer',
        'executed_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the wallet that owns the approval
     */
    public function wallet()
    {
        return $this->belongsTo(Fiat24EnterpriseWallet::class, 'wallet_id');
    }

    /**
     * Scope to get pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope to get approved approvals
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get executed approvals
     */
    public function scopeExecuted($query)
    {
        return $query->where('status', 'executed');
    }

    /**
     * Scope to get expired approvals
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
                     ->where('expires_at', '<=', now());
    }

    /**
     * Check if approval is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if approval threshold is met
     */
    public function isThresholdMet()
    {
        return $this->approvals_count >= $this->required_approvals;
    }

    /**
     * Add approval from signer
     */
    public function addApproval($signerAddress)
    {
        if ($this->status !== 'pending' || $this->isExpired()) {
            return false;
        }

        $approvedBy = $this->approved_by ?? [];
        
        if (in_array($signerAddress, $approvedBy)) {
            return false; // Already approved
        }

        $approvedBy[] = $signerAddress;
        $this->approved_by = $approvedBy;
        $this->approvals_count = count($approvedBy);

        // Check if threshold is met
        if ($this->isThresholdMet()) {
            $this->status = 'approved';
        }

        return $this->save();
    }

    /**
     * Add rejection from signer
     */
    public function addRejection($signerAddress)
    {
        if ($this->status !== 'pending' || $this->isExpired()) {
            return false;
        }

        $rejectedBy = $this->rejected_by ?? [];
        
        if (in_array($signerAddress, $rejectedBy)) {
            return false; // Already rejected
        }

        $rejectedBy[] = $signerAddress;
        $this->rejected_by = $rejectedBy;
        $this->rejections_count = count($rejectedBy);

        // If too many rejections, mark as rejected
        $remainingSigners = $this->wallet->total_signers - $this->rejections_count;
        if ($remainingSigners < $this->required_approvals) {
            $this->status = 'rejected';
        }

        return $this->save();
    }

    /**
     * Mark as executed
     */
    public function markAsExecuted($transactionHash = null)
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'executed';
        $this->executed_at = now();
        
        if ($transactionHash) {
            $this->transaction_hash = $transactionHash;
        }

        return $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed()
    {
        $this->status = 'failed';
        return $this->save();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired()
    {
        if ($this->status === 'pending' && $this->isExpired()) {
            $this->status = 'expired';
            return $this->save();
        }
        return false;
    }

    /**
     * Check if signer has approved
     */
    public function hasApproved($signerAddress)
    {
        return in_array($signerAddress, $this->approved_by ?? []);
    }

    /**
     * Check if signer has rejected
     */
    public function hasRejected($signerAddress)
    {
        return in_array($signerAddress, $this->rejected_by ?? []);
    }

    /**
     * Get formatted transaction details
     */
    public function getFormattedDetailsAttribute()
    {
        return [
            'type' => ucfirst($this->transaction_type),
            'amount' => number_format($this->amount, 4) . ' ' . $this->currency,
            'from' => $this->from_address ? substr($this->from_address, 0, 10) . '...' : 'N/A',
            'to' => $this->to_address ? substr($this->to_address, 0, 10) . '...' : 'N/A',
            'chain' => $this->getChainName(),
            'status' => ucfirst($this->status),
            'approvals' => "{$this->approvals_count}/{$this->required_approvals}",
        ];
    }

    /**
     * Get chain name
     */
    public function getChainName()
    {
        $chains = [
            '42161' => 'Arbitrum',
            '5000' => 'Mantle',
            '1' => 'Ethereum',
            '56' => 'BSC',
            '137' => 'Polygon',
        ];

        return $chains[$this->chain_id] ?? 'Chain ' . $this->chain_id;
    }

    /**
     * Get blockchain explorer URL
     */
    public function getExplorerUrlAttribute()
    {
        if (!$this->transaction_hash) {
            return null;
        }

        $explorers = [
            '42161' => 'https://arbiscan.io',
            '5000' => 'https://explorer.mantle.xyz',
            '1' => 'https://etherscan.io',
            '56' => 'https://bscscan.com',
            '137' => 'https://polygonscan.com',
        ];

        $explorer = $explorers[$this->chain_id] ?? null;
        
        return $explorer ? "{$explorer}/tx/{$this->transaction_hash}" : null;
    }
}
