<?php

namespace App\Services\Bot\Contracts;

/**
 * Interface for bots that can perform wallet operations.
 */
interface WalletCapable
{
    /**
     * Get wallet balance for a user.
     *
     * @param int $userId The user ID
     * @return array The wallet balance information
     */
    public function getWalletBalance(int $userId): array;

    /**
     * Transfer funds between wallets.
     *
     * @param int $senderId The sender user ID
     * @param int $receiverId The receiver user ID
     * @param float $amount The amount to transfer
     * @param string|null $currency The currency code
     * @return array The transfer result
     */
    public function transferFunds(int $senderId, int $receiverId, float $amount, ?string $currency = null): array;

    /**
     * Get transaction history for a user.
     *
     * @param int $userId The user ID
     * @param int $limit Number of transactions to retrieve
     * @return array The transaction history
     */
    public function getTransactionHistory(int $userId, int $limit = 10): array;
}
