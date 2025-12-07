<?php

namespace App\Services\Bot;

use App\Services\Bot\Contracts\BotInterface;
use App\Services\Bot\Contracts\MessagingCapable;
use App\Services\Bot\Contracts\WalletCapable;
use App\Services\Bot\Contracts\CommandCapable;
use App\Services\Bot\Traits\BaseBotTrait;
use App\Services\Bot\Traits\MessagingTrait;
use App\Services\Bot\Traits\WalletTrait;
use App\Services\Bot\Traits\CommandTrait;
use App\Services\Bot\Traits\McpTrait;

/**
 * WalletBot - A bot with multiple inherited capabilities.
 * 
 * This class demonstrates multiple inheritance in PHP using traits.
 * The bot inherits functionality from:
 * - BaseBotTrait: Core bot functionality
 * - MessagingTrait: Message sending/receiving
 * - WalletTrait: Wallet operations (balance, transfer, history)
 * - CommandTrait: Command handling
 * - McpTrait: Model Context Protocol support
 * 
 * Each trait provides independent, reusable functionality that can be
 * mixed and matched to create different types of bots.
 */
class WalletBot implements BotInterface, MessagingCapable, WalletCapable, CommandCapable
{
    use BaseBotTrait;
    use MessagingTrait;
    use WalletTrait;
    use CommandTrait;
    use McpTrait;

    /**
     * Create a new WalletBot instance.
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->initializeBot(
            $config['bot_id'] ?? 'wallet_bot',
            $config['name'] ?? 'ChiBank Wallet Bot',
            $config
        );

        // Initialize messaging if configured
        if (!empty($config['messaging_endpoint']) && !empty($config['messaging_token'])) {
            $this->initializeMessaging(
                $config['messaging_endpoint'],
                $config['messaging_token']
            );
        }

        // Initialize commands
        $this->initializeCommands();
        $this->registerBotCommands();

        // Initialize MCP
        $this->initializeMcp($config['mcp_server_name'] ?? 'ChiBank MCP Server');
    }

    /**
     * Register bot-specific commands.
     *
     * @return void
     */
    protected function registerBotCommands(): void
    {
        $this->registerCommand('balance', 'Check wallet balance', [$this, 'handleBalanceCommand']);
        $this->registerCommand('send', 'Send money to another user', [$this, 'handleSendCommand']);
        $this->registerCommand('history', 'View transaction history', [$this, 'handleHistoryCommand']);
    }

    /**
     * Process an incoming message/request.
     *
     * @param array $input
     * @return array
     */
    public function processInput(array $input): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Bot is disabled'];
        }

        $type = $input['type'] ?? 'message';

        return match ($type) {
            'message' => $this->processMessage($input),
            'command' => $this->processCommand($input),
            'mcp' => $this->handleMcpRequest($input),
            default => ['success' => false, 'error' => 'Unknown input type'],
        };
    }

    /**
     * Process a message input.
     *
     * @param array $input
     * @return array
     */
    protected function processMessage(array $input): array
    {
        $text = $input['text'] ?? '';

        // Check for command prefix
        if (str_starts_with($text, '/')) {
            $parts = explode(' ', $text);
            $command = ltrim(array_shift($parts), '/');
            return $this->executeCommand($command, $parts, $input);
        }

        return [
            'success' => true,
            'message' => 'Message received',
            'response' => 'Use /help to see available commands',
        ];
    }

    /**
     * Process a command input.
     *
     * @param array $input
     * @return array
     */
    protected function processCommand(array $input): array
    {
        $command = $input['command'] ?? '';
        $arguments = $input['arguments'] ?? [];
        return $this->executeCommand($command, $arguments, $input);
    }

    /**
     * Handle balance command.
     *
     * @param array $arguments
     * @param array $context
     * @return array
     */
    protected function handleBalanceCommand(array $arguments, array $context): array
    {
        $userId = $context['user_id'] ?? ($arguments[0] ?? null);

        if (!$userId) {
            return ['success' => false, 'error' => 'User ID is required'];
        }

        return $this->getWalletBalance((int) $userId);
    }

    /**
     * Handle send command.
     *
     * @param array $arguments
     * @param array $context
     * @return array
     */
    protected function handleSendCommand(array $arguments, array $context): array
    {
        if (count($arguments) < 2) {
            return ['success' => false, 'error' => 'Usage: /send [receiver_id] [amount]'];
        }

        $senderId = $context['user_id'] ?? null;
        if (!$senderId) {
            return ['success' => false, 'error' => 'Sender ID is required'];
        }

        $receiverId = (int) $arguments[0];
        $amount = (float) $arguments[1];

        return $this->transferFunds($senderId, $receiverId, $amount);
    }

    /**
     * Handle history command.
     *
     * @param array $arguments
     * @param array $context
     * @return array
     */
    protected function handleHistoryCommand(array $arguments, array $context): array
    {
        $userId = $context['user_id'] ?? ($arguments[0] ?? null);
        $limit = $arguments[1] ?? $arguments[0] ?? 10;

        if (!$userId && !isset($context['user_id'])) {
            return ['success' => false, 'error' => 'User ID is required'];
        }

        return $this->getTransactionHistory((int) $userId, (int) $limit);
    }
}
