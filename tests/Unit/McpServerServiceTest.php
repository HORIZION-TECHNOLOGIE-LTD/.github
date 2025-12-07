<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Mcp\McpServerService;

/**
 * Unit tests for MCP Server Service.
 */
class McpServerServiceTest extends TestCase
{
    /**
     * Test that McpServerService class exists.
     *
     * @return void
     */
    public function test_mcp_server_service_class_exists()
    {
        $this->assertTrue(class_exists(McpServerService::class));
    }

    /**
     * Test that McpController class exists.
     *
     * @return void
     */
    public function test_mcp_controller_class_exists()
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\Bot\McpController::class));
    }

    /**
     * Test MCP config file exists.
     *
     * @return void
     */
    public function test_mcp_config_file_exists()
    {
        $configPath = __DIR__ . '/../../config/mcp.php';
        $this->assertFileExists($configPath);
    }

    /**
     * Test MCP routes file exists.
     *
     * @return void
     */
    public function test_mcp_routes_file_exists()
    {
        $routesPath = __DIR__ . '/../../routes/mcp.php';
        $this->assertFileExists($routesPath);
    }
}
