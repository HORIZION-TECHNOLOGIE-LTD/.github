<?php

namespace App\Services\Bot\Contracts;

/**
 * Base interface for all bots in the system.
 * 
 * This interface defines the core contract that all bot implementations must follow.
 * Bots can combine multiple traits to inherit functionality from various sources
 * (multiple inheritance pattern using PHP traits).
 */
interface BotInterface
{
    /**
     * Get the unique identifier for this bot.
     *
     * @return string
     */
    public function getBotId(): string;

    /**
     * Get the bot name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Process an incoming message/request.
     *
     * @param array $input The input data to process
     * @return array The response data
     */
    public function processInput(array $input): array;

    /**
     * Get the bot's current status.
     *
     * @return array
     */
    public function getStatus(): array;

    /**
     * Check if the bot is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
