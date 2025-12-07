<?php

namespace App\Services\Bot\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Provides command handling capabilities for bots.
 * 
 * This trait can be combined with other traits for multiple inheritance.
 */
trait CommandTrait
{
    /**
     * @var array Registered commands with handlers
     */
    protected array $commands = [];

    /**
     * Initialize command capabilities.
     *
     * @return void
     */
    protected function initializeCommands(): void
    {
        $this->registerDefaultCommands();
    }

    /**
     * Register default commands.
     *
     * @return void
     */
    protected function registerDefaultCommands(): void
    {
        $this->registerCommand('help', 'Show available commands', [$this, 'handleHelpCommand']);
        $this->registerCommand('status', 'Show bot status', [$this, 'handleStatusCommand']);
    }

    /**
     * Register a command.
     *
     * @param string $name Command name
     * @param string $description Command description
     * @param callable $handler Command handler
     * @return void
     */
    public function registerCommand(string $name, string $description, callable $handler): void
    {
        $this->commands[strtolower($name)] = [
            'name' => $name,
            'description' => $description,
            'handler' => $handler,
        ];
    }

    /**
     * Get all available commands.
     *
     * @return array
     */
    public function getAvailableCommands(): array
    {
        return array_map(function ($cmd) {
            return [
                'name' => $cmd['name'],
                'description' => $cmd['description'],
            ];
        }, $this->commands);
    }

    /**
     * Check if a command is valid.
     *
     * @param string $command
     * @return bool
     */
    public function isValidCommand(string $command): bool
    {
        return isset($this->commands[strtolower($command)]);
    }

    /**
     * Execute a command.
     *
     * @param string $command
     * @param array $arguments
     * @param array $context
     * @return array
     */
    public function executeCommand(string $command, array $arguments = [], array $context = []): array
    {
        $command = strtolower($command);

        if (!$this->isValidCommand($command)) {
            return [
                'success' => false,
                'error' => "Unknown command: {$command}",
                'available_commands' => array_keys($this->commands),
            ];
        }

        try {
            $handler = $this->commands[$command]['handler'];
            return call_user_func($handler, $arguments, $context);
        } catch (\Exception $e) {
            Log::error("Command execution error: {$command} - " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Command execution failed',
            ];
        }
    }

    /**
     * Handle help command.
     *
     * @param array $arguments
     * @param array $context
     * @return array
     */
    protected function handleHelpCommand(array $arguments, array $context): array
    {
        return [
            'success' => true,
            'commands' => $this->getAvailableCommands(),
        ];
    }

    /**
     * Handle status command.
     *
     * @param array $arguments
     * @param array $context
     * @return array
     */
    protected function handleStatusCommand(array $arguments, array $context): array
    {
        return [
            'success' => true,
            'status' => method_exists($this, 'getStatus') ? $this->getStatus() : ['enabled' => true],
        ];
    }
}
