<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Bot Contracts interfaces.
 */
class BotContractsTest extends TestCase
{
    /**
     * Test that BotInterface exists.
     *
     * @return void
     */
    public function test_bot_interface_exists()
    {
        $this->assertTrue(interface_exists(\App\Services\Bot\Contracts\BotInterface::class));
    }

    /**
     * Test that MessagingCapable interface exists.
     *
     * @return void
     */
    public function test_messaging_capable_interface_exists()
    {
        $this->assertTrue(interface_exists(\App\Services\Bot\Contracts\MessagingCapable::class));
    }

    /**
     * Test that WalletCapable interface exists.
     *
     * @return void
     */
    public function test_wallet_capable_interface_exists()
    {
        $this->assertTrue(interface_exists(\App\Services\Bot\Contracts\WalletCapable::class));
    }

    /**
     * Test that CommandCapable interface exists.
     *
     * @return void
     */
    public function test_command_capable_interface_exists()
    {
        $this->assertTrue(interface_exists(\App\Services\Bot\Contracts\CommandCapable::class));
    }

    /**
     * Test that BotInterface has required methods.
     *
     * @return void
     */
    public function test_bot_interface_has_required_methods()
    {
        $reflection = new \ReflectionClass(\App\Services\Bot\Contracts\BotInterface::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertContains('getBotId', $methods);
        $this->assertContains('getName', $methods);
        $this->assertContains('processInput', $methods);
        $this->assertContains('getStatus', $methods);
        $this->assertContains('isEnabled', $methods);
    }

    /**
     * Test that MessagingCapable has required methods.
     *
     * @return void
     */
    public function test_messaging_capable_has_required_methods()
    {
        $reflection = new \ReflectionClass(\App\Services\Bot\Contracts\MessagingCapable::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertContains('sendMessage', $methods);
        $this->assertContains('handleIncomingMessage', $methods);
    }

    /**
     * Test that WalletCapable has required methods.
     *
     * @return void
     */
    public function test_wallet_capable_has_required_methods()
    {
        $reflection = new \ReflectionClass(\App\Services\Bot\Contracts\WalletCapable::class);
        $methods = array_map(fn($m) => $m->getName(), $reflection->getMethods());

        $this->assertContains('getWalletBalance', $methods);
        $this->assertContains('transferFunds', $methods);
        $this->assertContains('getTransactionHistory', $methods);
    }
}
