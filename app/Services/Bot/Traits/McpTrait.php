<?php

namespace App\Services\Bot\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Provides MCP (Model Context Protocol) capabilities for bots.
 * 
 * MCP is a protocol for AI model interactions that enables structured
 * communication between bots and AI services.
 * 
 * This trait can be combined with other traits for multiple inheritance.
 */
trait McpTrait
{
    /**
     * @var array Available MCP tools
     */
    protected array $mcpTools = [];

    /**
     * @var array MCP resources
     */
    protected array $mcpResources = [];

    /**
     * @var string|null MCP server name
     */
    protected ?string $mcpServerName = null;

    /**
     * @var string MCP protocol version
     */
    protected string $mcpVersion = '2024-11-05';

    /**
     * Initialize MCP capabilities.
     *
     * @param string $serverName
     * @return void
     */
    protected function initializeMcp(string $serverName): void
    {
        $this->mcpServerName = $serverName;
        $this->registerDefaultMcpTools();
    }

    /**
     * Register default MCP tools.
     *
     * @return void
     */
    protected function registerDefaultMcpTools(): void
    {
        // Register wallet tools if wallet capability is available
        if (method_exists($this, 'getWalletBalance')) {
            $this->registerMcpTool('get_balance', 'Get wallet balance for a user', [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'The user ID'],
                ],
                'required' => ['user_id'],
            ]);

            $this->registerMcpTool('transfer_funds', 'Transfer funds between users', [
                'type' => 'object',
                'properties' => [
                    'sender_id' => ['type' => 'integer', 'description' => 'Sender user ID'],
                    'receiver_id' => ['type' => 'integer', 'description' => 'Receiver user ID'],
                    'amount' => ['type' => 'number', 'description' => 'Amount to transfer'],
                    'currency' => ['type' => 'string', 'description' => 'Currency code (optional)'],
                ],
                'required' => ['sender_id', 'receiver_id', 'amount'],
            ]);

            $this->registerMcpTool('get_transactions', 'Get transaction history', [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'The user ID'],
                    'limit' => ['type' => 'integer', 'description' => 'Number of transactions'],
                ],
                'required' => ['user_id'],
            ]);
        }
    }

    /**
     * Register an MCP tool.
     *
     * @param string $name Tool name
     * @param string $description Tool description
     * @param array $inputSchema JSON schema for input
     * @return void
     */
    public function registerMcpTool(string $name, string $description, array $inputSchema = []): void
    {
        $this->mcpTools[$name] = [
            'name' => $name,
            'description' => $description,
            'inputSchema' => $inputSchema,
        ];
    }

    /**
     * Register an MCP resource.
     *
     * @param string $uri Resource URI
     * @param string $name Resource name
     * @param string $mimeType MIME type
     * @param string|null $description Description
     * @return void
     */
    public function registerMcpResource(string $uri, string $name, string $mimeType = 'application/json', ?string $description = null): void
    {
        $this->mcpResources[$uri] = [
            'uri' => $uri,
            'name' => $name,
            'mimeType' => $mimeType,
            'description' => $description,
        ];
    }

    /**
     * Handle an MCP request.
     *
     * @param array $request The MCP request
     * @return array The MCP response
     */
    public function handleMcpRequest(array $request): array
    {
        $method = $request['method'] ?? '';
        $id = $request['id'] ?? null;
        $params = $request['params'] ?? [];

        try {
            $result = match ($method) {
                'initialize' => $this->handleMcpInitialize($params),
                'tools/list' => $this->handleMcpToolsList(),
                'tools/call' => $this->handleMcpToolsCall($params),
                'resources/list' => $this->handleMcpResourcesList(),
                'resources/read' => $this->handleMcpResourcesRead($params),
                default => throw new \InvalidArgumentException("Unknown method: {$method}"),
            };

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            Log::error("MCP request error: {$method} - " . $e->getMessage());
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32600,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Handle MCP initialize request.
     *
     * @param array $params
     * @return array
     */
    protected function handleMcpInitialize(array $params): array
    {
        return [
            'protocolVersion' => $this->mcpVersion,
            'capabilities' => [
                'tools' => ['listChanged' => true],
                'resources' => ['subscribe' => false, 'listChanged' => false],
            ],
            'serverInfo' => [
                'name' => $this->mcpServerName ?? 'ChiBank Bot MCP Server',
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * Handle MCP tools/list request.
     *
     * @return array
     */
    protected function handleMcpToolsList(): array
    {
        return [
            'tools' => array_values($this->mcpTools),
        ];
    }

    /**
     * Handle MCP tools/call request.
     *
     * @param array $params
     * @return array
     */
    protected function handleMcpToolsCall(array $params): array
    {
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        if (!isset($this->mcpTools[$toolName])) {
            throw new \InvalidArgumentException("Unknown tool: {$toolName}");
        }

        $result = match ($toolName) {
            'get_balance' => $this->executeMcpGetBalance($arguments),
            'transfer_funds' => $this->executeMcpTransferFunds($arguments),
            'get_transactions' => $this->executeMcpGetTransactions($arguments),
            default => throw new \InvalidArgumentException("Tool not implemented: {$toolName}"),
        };

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result),
                ],
            ],
        ];
    }

    /**
     * Handle MCP resources/list request.
     *
     * @return array
     */
    protected function handleMcpResourcesList(): array
    {
        return [
            'resources' => array_values($this->mcpResources),
        ];
    }

    /**
     * Handle MCP resources/read request.
     *
     * @param array $params
     * @return array
     */
    protected function handleMcpResourcesRead(array $params): array
    {
        $uri = $params['uri'] ?? '';

        if (!isset($this->mcpResources[$uri])) {
            throw new \InvalidArgumentException("Unknown resource: {$uri}");
        }

        // Override in implementing class for custom resource handling
        return [
            'contents' => [
                [
                    'uri' => $uri,
                    'mimeType' => $this->mcpResources[$uri]['mimeType'],
                    'text' => '{}',
                ],
            ],
        ];
    }

    /**
     * Execute get_balance MCP tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeMcpGetBalance(array $arguments): array
    {
        $userId = $arguments['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'error' => 'user_id is required'];
        }

        if (method_exists($this, 'getWalletBalance')) {
            return $this->getWalletBalance($userId);
        }

        return ['success' => false, 'error' => 'Wallet capability not available'];
    }

    /**
     * Execute transfer_funds MCP tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeMcpTransferFunds(array $arguments): array
    {
        $senderId = $arguments['sender_id'] ?? null;
        $receiverId = $arguments['receiver_id'] ?? null;
        $amount = $arguments['amount'] ?? null;
        $currency = $arguments['currency'] ?? null;

        if (!$senderId || !$receiverId || !$amount) {
            return ['success' => false, 'error' => 'sender_id, receiver_id, and amount are required'];
        }

        if (method_exists($this, 'transferFunds')) {
            return $this->transferFunds($senderId, $receiverId, $amount, $currency);
        }

        return ['success' => false, 'error' => 'Wallet capability not available'];
    }

    /**
     * Execute get_transactions MCP tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeMcpGetTransactions(array $arguments): array
    {
        $userId = $arguments['user_id'] ?? null;
        $limit = $arguments['limit'] ?? 10;

        if (!$userId) {
            return ['success' => false, 'error' => 'user_id is required'];
        }

        if (method_exists($this, 'getTransactionHistory')) {
            return $this->getTransactionHistory($userId, $limit);
        }

        return ['success' => false, 'error' => 'Wallet capability not available'];
    }
}
