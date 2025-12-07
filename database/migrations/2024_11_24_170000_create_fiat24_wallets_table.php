<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates two types of Fiat24 wallets:
     * 1. Fiat Wallet - Fixed fiat currency wallet (CHF, EUR, USD, CNH)
     * 2. Enterprise Wallet - Multi-signature multi-chain wallet for business operations
     *
     * @return void
     */
    public function up()
    {
        // Fiat24 Fiat Wallet - Fixed fiat currency wallet
        Schema::create('fiat24_fiat_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('currency_id');
            
            // Fiat24 specific fields
            $table->string('fiat24_account_nft_id')->nullable()->unique()->comment('ERC-721 NFT ID representing the account');
            $table->string('fiat24_iban')->nullable()->unique()->comment('Swiss IBAN assigned by Fiat24');
            $table->string('fiat24_account_type')->default('personal')->comment('personal or business');
            $table->enum('wallet_type', ['fiat'])->default('fiat')->comment('Fixed fiat wallet type');
            
            // Balance and status
            $table->decimal('balance', 28, 8)->default(0);
            $table->decimal('reserved_balance', 28, 8)->default(0)->comment('Balance reserved for pending transactions');
            $table->boolean('status')->default(true);
            $table->boolean('kyc_verified')->default(false)->comment('KYC verification status from Fiat24');
            
            // Metadata
            $table->string('chain_id')->default('42161')->comment('Arbitrum: 42161, Mantle: 5000');
            $table->json('metadata')->nullable()->comment('Additional Fiat24 account metadata');
            
            $table->timestamps();
            
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes for performance
            $table->index('fiat24_account_nft_id');
            $table->index('fiat24_iban');
            $table->index(['user_id', 'currency_id']);
            $table->index(['user_id', 'status']); // For active wallet queries
            $table->index(['currency_id', 'status']); // For currency-based queries
            $table->index('kyc_verified'); // For KYC status queries
        });

        // Fiat24 Enterprise Wallet - Multi-signature multi-chain wallet
        Schema::create('fiat24_enterprise_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('wallet_name')->comment('Enterprise wallet name');
            
            // Multi-chain support
            $table->json('supported_chains')->comment('Array of supported chain IDs: [42161, 5000, etc]');
            $table->string('primary_chain_id')->default('42161')->comment('Primary chain for operations');
            
            // Multi-signature configuration
            $table->integer('required_signatures')->default(2)->comment('Number of signatures required');
            $table->integer('total_signers')->default(3)->comment('Total number of signers');
            $table->json('signer_addresses')->comment('Array of authorized signer wallet addresses');
            $table->json('signer_roles')->nullable()->comment('Roles and permissions for each signer');
            
            // Wallet addresses per chain
            $table->json('chain_addresses')->comment('Wallet addresses for each supported chain');
            
            // Balance tracking per currency per chain
            $table->json('balances')->comment('Multi-currency balances per chain');
            $table->decimal('total_usd_value', 28, 8)->default(0)->comment('Total value in USD equivalent');
            
            // Enterprise features
            $table->string('enterprise_type')->default('standard')->comment('standard, premium, enterprise');
            $table->boolean('smart_contract_enabled')->default(true);
            $table->boolean('defi_enabled')->default(false);
            $table->boolean('automated_treasury')->default(false)->comment('Automated treasury management');
            
            // Fiat24 integration
            $table->string('fiat24_enterprise_nft_id')->nullable()->comment('Enterprise NFT ID (2-digit or 1-digit)');
            $table->boolean('delegated_access')->default(false)->comment('Whether platform has delegated access');
            
            // Security and compliance
            $table->boolean('status')->default(true);
            $table->json('security_settings')->nullable()->comment('Security configurations');
            $table->json('compliance_settings')->nullable()->comment('Compliance and audit settings');
            $table->timestamp('last_activity_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes for performance
            $table->index('fiat24_enterprise_nft_id');
            $table->index(['user_id', 'primary_chain_id']);
            $table->index('wallet_name');
            $table->index(['user_id', 'status']); // For active wallet queries
            $table->index('enterprise_type'); // For tier-based queries
            $table->index('last_activity_at'); // For activity tracking
        });

        // Transaction approval tracking for multi-sig wallets
        Schema::create('fiat24_enterprise_wallet_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->string('transaction_hash')->nullable();
            $table->string('transaction_reference')->unique();
            $table->enum('transaction_type', ['transfer', 'swap', 'defi', 'withdrawal', 'deposit']);
            
            // Transaction details
            $table->decimal('amount', 28, 8);
            $table->string('currency');
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('chain_id');
            
            // Approval tracking
            $table->json('required_signers')->comment('List of required signer addresses');
            $table->json('approved_by')->nullable()->comment('List of signers who approved');
            $table->json('rejected_by')->nullable()->comment('List of signers who rejected');
            $table->integer('approvals_count')->default(0);
            $table->integer('rejections_count')->default(0);
            $table->integer('required_approvals');
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'executed', 'failed', 'expired'])->default('pending');
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('fiat24_enterprise_wallets')->onDelete('cascade')->onUpdate('cascade');
            
            // Indexes
            $table->index('transaction_reference');
            $table->index(['wallet_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiat24_enterprise_wallet_approvals');
        Schema::dropIfExists('fiat24_enterprise_wallets');
        Schema::dropIfExists('fiat24_fiat_wallets');
    }
};
