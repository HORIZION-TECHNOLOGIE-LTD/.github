<?php

namespace App\Services\Bot\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Provides basic bot functionality.
 * 
 * This trait can be combined with other traits to create bots with
 * multiple inherited capabilities (multiple inheritance pattern).
 */
trait BaseBotTrait
{
    /**
     * @var string The bot identifier
     */
    protected string $botId;

    /**
     * @var string The bot name
     */
    protected string $botName;

    /**
     * @var bool Whether the bot is enabled
     */
    protected bool $enabled = true;

    /**
     * @var array Bot configuration
     */
    protected array $config = [];

    /**
     * Initialize the base bot.
     *
     * @param string $botId
     * @param string $botName
     * @param array $config
     * @return void
     */
    protected function initializeBot(string $botId, string $botName, array $config = []): void
    {
        $this->botId = $botId;
        $this->botName = $botName;
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? true;
    }

    /**
     * Get the bot ID.
     *
     * @return string
     */
    public function getBotId(): string
    {
        return $this->botId;
    }

    /**
     * Get the bot name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->botName;
    }

    /**
     * Check if the bot is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the bot's current status.
     *
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'bot_id' => $this->botId,
            'name' => $this->botName,
            'enabled' => $this->enabled,
            'capabilities' => $this->getCapabilities(),
        ];
    }

    /**
     * Get the bot's capabilities based on implemented traits.
     *
     * @return array
     */
    protected function getCapabilities(): array
    {
        $capabilities = [];
        $uses = class_uses($this);

        if (in_array(MessagingTrait::class, $uses) || method_exists($this, 'sendMessage')) {
            $capabilities[] = 'messaging';
        }
        if (in_array(WalletTrait::class, $uses) || method_exists($this, 'getWalletBalance')) {
            $capabilities[] = 'wallet';
        }
        if (in_array(CommandTrait::class, $uses) || method_exists($this, 'executeCommand')) {
            $capabilities[] = 'commands';
        }
        if (in_array(McpTrait::class, $uses) || method_exists($this, 'handleMcpRequest')) {
            $capabilities[] = 'mcp';
        }

        return $capabilities;
    }

    /**
     * Log bot activity.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logActivity(string $level, string $message, array $context = []): void
    {
        $context['bot_id'] = $this->botId;
        $context['bot_name'] = $this->botName;
        Log::log($level, "Bot [{$this->botId}]: {$message}", $context);
    }
}
