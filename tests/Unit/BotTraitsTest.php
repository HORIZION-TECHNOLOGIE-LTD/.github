<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Bot Traits.
 */
class BotTraitsTest extends TestCase
{
    /**
     * Test that BaseBotTrait exists.
     *
     * @return void
     */
    public function test_base_bot_trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Services\Bot\Traits\BaseBotTrait::class));
    }

    /**
     * Test that MessagingTrait exists.
     *
     * @return void
     */
    public function test_messaging_trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Services\Bot\Traits\MessagingTrait::class));
    }

    /**
     * Test that WalletTrait exists.
     *
     * @return void
     */
    public function test_wallet_trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Services\Bot\Traits\WalletTrait::class));
    }

    /**
     * Test that CommandTrait exists.
     *
     * @return void
     */
    public function test_command_trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Services\Bot\Traits\CommandTrait::class));
    }

    /**
     * Test that McpTrait exists.
     *
     * @return void
     */
    public function test_mcp_trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Services\Bot\Traits\McpTrait::class));
    }
}
