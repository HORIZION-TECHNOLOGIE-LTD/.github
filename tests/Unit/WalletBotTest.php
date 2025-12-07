<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Bot\WalletBot;
use App\Services\Bot\Contracts\BotInterface;
use App\Services\Bot\Contracts\MessagingCapable;
use App\Services\Bot\Contracts\WalletCapable;
use App\Services\Bot\Contracts\CommandCapable;

/**
 * Unit tests for WalletBot.
 */
class WalletBotTest extends TestCase
{
    /**
     * Test that WalletBot class exists.
     *
     * @return void
     */
    public function test_wallet_bot_class_exists()
    {
        $this->assertTrue(class_exists(WalletBot::class));
    }

    /**
     * Test that WalletBot implements BotInterface.
     *
     * @return void
     */
    public function test_wallet_bot_implements_bot_interface()
    {
        $reflection = new \ReflectionClass(WalletBot::class);
        $this->assertTrue($reflection->implementsInterface(BotInterface::class));
    }

    /**
     * Test that WalletBot implements MessagingCapable.
     *
     * @return void
     */
    public function test_wallet_bot_implements_messaging_capable()
    {
        $reflection = new \ReflectionClass(WalletBot::class);
        $this->assertTrue($reflection->implementsInterface(MessagingCapable::class));
    }

    /**
     * Test that WalletBot implements WalletCapable.
     *
     * @return void
     */
    public function test_wallet_bot_implements_wallet_capable()
    {
        $reflection = new \ReflectionClass(WalletBot::class);
        $this->assertTrue($reflection->implementsInterface(WalletCapable::class));
    }

    /**
     * Test that WalletBot implements CommandCapable.
     *
     * @return void
     */
    public function test_wallet_bot_implements_command_capable()
    {
        $reflection = new \ReflectionClass(WalletBot::class);
        $this->assertTrue($reflection->implementsInterface(CommandCapable::class));
    }

    /**
     * Test that WalletBot uses multiple traits (multiple inheritance).
     *
     * @return void
     */
    public function test_wallet_bot_uses_multiple_traits()
    {
        $traits = class_uses(WalletBot::class);
        
        $this->assertContains(\App\Services\Bot\Traits\BaseBotTrait::class, $traits);
        $this->assertContains(\App\Services\Bot\Traits\MessagingTrait::class, $traits);
        $this->assertContains(\App\Services\Bot\Traits\WalletTrait::class, $traits);
        $this->assertContains(\App\Services\Bot\Traits\CommandTrait::class, $traits);
        $this->assertContains(\App\Services\Bot\Traits\McpTrait::class, $traits);
    }

    /**
     * Test that WalletBot can be instantiated.
     *
     * @return void
     */
    public function test_wallet_bot_can_be_instantiated()
    {
        $bot = new WalletBot(['bot_id' => 'test_bot', 'name' => 'Test Bot']);
        $this->assertInstanceOf(WalletBot::class, $bot);
    }

    /**
     * Test that WalletBot returns correct bot ID.
     *
     * @return void
     */
    public function test_wallet_bot_returns_correct_bot_id()
    {
        $bot = new WalletBot(['bot_id' => 'test_bot_123']);
        $this->assertEquals('test_bot_123', $bot->getBotId());
    }

    /**
     * Test that WalletBot returns correct name.
     *
     * @return void
     */
    public function test_wallet_bot_returns_correct_name()
    {
        $bot = new WalletBot(['name' => 'My Test Bot']);
        $this->assertEquals('My Test Bot', $bot->getName());
    }

    /**
     * Test that WalletBot is enabled by default.
     *
     * @return void
     */
    public function test_wallet_bot_is_enabled_by_default()
    {
        $bot = new WalletBot();
        $this->assertTrue($bot->isEnabled());
    }

    /**
     * Test that WalletBot can be disabled via config.
     *
     * @return void
     */
    public function test_wallet_bot_can_be_disabled()
    {
        $bot = new WalletBot(['enabled' => false]);
        $this->assertFalse($bot->isEnabled());
    }

    /**
     * Test that WalletBot returns status array.
     *
     * @return void
     */
    public function test_wallet_bot_returns_status_array()
    {
        $bot = new WalletBot(['bot_id' => 'test', 'name' => 'Test']);
        $status = $bot->getStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('bot_id', $status);
        $this->assertArrayHasKey('name', $status);
        $this->assertArrayHasKey('enabled', $status);
        $this->assertArrayHasKey('capabilities', $status);
    }

    /**
     * Test that WalletBot has available commands.
     *
     * @return void
     */
    public function test_wallet_bot_has_available_commands()
    {
        $bot = new WalletBot();
        $commands = $bot->getAvailableCommands();

        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);
    }

    /**
     * Test that help command is valid.
     *
     * @return void
     */
    public function test_wallet_bot_has_help_command()
    {
        $bot = new WalletBot();
        $this->assertTrue($bot->isValidCommand('help'));
    }

    /**
     * Test that balance command is valid.
     *
     * @return void
     */
    public function test_wallet_bot_has_balance_command()
    {
        $bot = new WalletBot();
        $this->assertTrue($bot->isValidCommand('balance'));
    }

    /**
     * Test that send command is valid.
     *
     * @return void
     */
    public function test_wallet_bot_has_send_command()
    {
        $bot = new WalletBot();
        $this->assertTrue($bot->isValidCommand('send'));
    }

    /**
     * Test that history command is valid.
     *
     * @return void
     */
    public function test_wallet_bot_has_history_command()
    {
        $bot = new WalletBot();
        $this->assertTrue($bot->isValidCommand('history'));
    }

    /**
     * Test that invalid command returns false.
     *
     * @return void
     */
    public function test_wallet_bot_invalid_command_returns_false()
    {
        $bot = new WalletBot();
        $this->assertFalse($bot->isValidCommand('nonexistent_command'));
    }

    /**
     * Test that executeCommand returns error for unknown command.
     *
     * @return void
     */
    public function test_wallet_bot_execute_unknown_command_returns_error()
    {
        $bot = new WalletBot();
        $result = $bot->executeCommand('nonexistent_command');

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test that help command executes successfully.
     *
     * @return void
     */
    public function test_wallet_bot_help_command_executes()
    {
        $bot = new WalletBot();
        $result = $bot->executeCommand('help');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('commands', $result);
    }

    /**
     * Test that status command executes successfully.
     *
     * @return void
     */
    public function test_wallet_bot_status_command_executes()
    {
        $bot = new WalletBot();
        $result = $bot->executeCommand('status');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * Test that processInput handles disabled bot.
     *
     * @return void
     */
    public function test_wallet_bot_process_input_when_disabled()
    {
        $bot = new WalletBot(['enabled' => false]);
        $result = $bot->processInput(['type' => 'message', 'text' => 'hello']);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('Bot is disabled', $result['error']);
    }

    /**
     * Test that processInput handles message type.
     *
     * @return void
     */
    public function test_wallet_bot_process_input_message()
    {
        $bot = new WalletBot();
        $result = $bot->processInput(['type' => 'message', 'text' => 'hello']);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /**
     * Test that processInput handles command in message.
     *
     * @return void
     */
    public function test_wallet_bot_process_input_command_in_message()
    {
        $bot = new WalletBot();
        $result = $bot->processInput(['type' => 'message', 'text' => '/help']);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('commands', $result);
    }

    /**
     * Test that processInput handles unknown type.
     *
     * @return void
     */
    public function test_wallet_bot_process_input_unknown_type()
    {
        $bot = new WalletBot();
        $result = $bot->processInput(['type' => 'unknown']);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }
}
