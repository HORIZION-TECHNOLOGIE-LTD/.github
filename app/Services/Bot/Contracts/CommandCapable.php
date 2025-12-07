<?php

namespace App\Services\Bot\Contracts;

/**
 * Interface for bots that can handle commands.
 */
interface CommandCapable
{
    /**
     * Get all available commands.
     *
     * @return array List of available commands with descriptions
     */
    public function getAvailableCommands(): array;

    /**
     * Execute a command.
     *
     * @param string $command The command name
     * @param array $arguments Command arguments
     * @param array $context Additional context (user info, etc.)
     * @return array The command result
     */
    public function executeCommand(string $command, array $arguments = [], array $context = []): array;

    /**
     * Check if a command is valid.
     *
     * @param string $command The command name
     * @return bool
     */
    public function isValidCommand(string $command): bool;
}
