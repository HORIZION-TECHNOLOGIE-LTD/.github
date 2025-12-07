<?php

namespace App\Services\Mcp;

use App\Services\Bot\WalletBot;
use Illuminate\Support\Facades\Log;

/**
 * MCP (Model Context Protocol) Server Service.
 * 
 * This service handles incoming MCP requests from AI agents and routes them
 * to the appropriate bot handlers. It implements the MCP specification for
 * server-side integration with AI models.
 * 
 * Protocol Version: 2024-11-05
 * 
 * @see https://spec.modelcontextprotocol.io/
 */
class McpServerService
{
    /**
     * @var WalletBot The bot instance
     */
    protected WalletBot $bot;

    /**
     * @var string Protocol version
     */
    protected string $protocolVersion;

    /**
     * @var array Server capabilities
     */
    protected array $capabilities = [
        'tools' => ['listChanged' => true],
        'resources' => ['subscribe' => false, 'listChanged' => false],
        'prompts' => ['listChanged' => false],
    ];

    /**
     * Create a new MCP Server Service instance.
     */
    public function __construct()
    {
        $this->protocolVersion = config('mcp.protocol_version', '2024-11-05');
        
        $this->bot = new WalletBot([
            'bot_id' => 'mcp_server',
            'name' => 'ChiBank MCP Server',
            'mcp_server_name' => config('mcp.server_name', 'ChiBank MCP Server'),
        ]);
    }

    /**
     * Handle an incoming MCP request.
     *
     * @param array $request The JSON-RPC request
     * @return array The JSON-RPC response
     */
    public function handleRequest(array $request): array
    {
        $method = $request['method'] ?? '';
        $id = $request['id'] ?? null;
        $params = $request['params'] ?? [];

        Log::info('MCP Server: Received request', ['method' => $method, 'id' => $id]);

        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize($params),
                'initialized' => $this->handleInitialized(),
                'ping' => $this->handlePing(),
                'tools/list' => $this->handleToolsList(),
                'tools/call' => $this->handleToolsCall($params),
                'resources/list' => $this->handleResourcesList(),
                'resources/read' => $this->handleResourcesRead($params),
                'prompts/list' => $this->handlePromptsList(),
                'prompts/get' => $this->handlePromptsGet($params),
                default => throw new \InvalidArgumentException("Unknown method: {$method}"),
            };

            return $this->buildResponse($id, $result);

        } catch (\Exception $e) {
            Log::error('MCP Server: Request error', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return $this->buildErrorResponse($id, -32600, $e->getMessage());
        }
    }

    /**
     * Handle initialize request.
     *
     * @param array $params
     * @return array
     */
    protected function handleInitialize(array $params): array
    {
        $clientInfo = $params['clientInfo'] ?? [];
        
        Log::info('MCP Server: Client initialized', ['client' => $clientInfo]);

        return [
            'protocolVersion' => $this->protocolVersion,
            'capabilities' => $this->capabilities,
            'serverInfo' => [
                'name' => config('mcp.server_name', 'ChiBank MCP Server'),
                'version' => config('mcp.server_version', '1.0.0'),
            ],
            'instructions' => 'ChiBank MCP Server for wallet operations. Use tools to check balances, transfer funds, and view transaction history.',
        ];
    }

    /**
     * Handle initialized notification.
     *
     * @return array
     */
    protected function handleInitialized(): array
    {
        return ['status' => 'ready'];
    }

    /**
     * Handle ping request.
     *
     * @return array
     */
    protected function handlePing(): array
    {
        return ['pong' => true];
    }

    /**
     * Handle tools/list request.
     *
     * @return array
     */
    protected function handleToolsList(): array
    {
        return [
            'tools' => [
                [
                    'name' => 'get_balance',
                    'description' => 'Get wallet balance for a user. Returns all wallets and their balances.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => [
                                'type' => 'integer',
                                'description' => 'The user ID to get balance for',
                            ],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'transfer_funds',
                    'description' => 'Transfer funds from one user to another.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'sender_id' => [
                                'type' => 'integer',
                                'description' => 'The sender user ID',
                            ],
                            'receiver_id' => [
                                'type' => 'integer',
                                'description' => 'The receiver user ID',
                            ],
                            'amount' => [
                                'type' => 'number',
                                'description' => 'The amount to transfer',
                            ],
                            'currency' => [
                                'type' => 'string',
                                'description' => 'Currency code (optional)',
                            ],
                        ],
                        'required' => ['sender_id', 'receiver_id', 'amount'],
                    ],
                ],
                [
                    'name' => 'get_transactions',
                    'description' => 'Get transaction history for a user.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => [
                                'type' => 'integer',
                                'description' => 'The user ID',
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Number of transactions to retrieve (default: 10, max: 50)',
                            ],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'bot_status',
                    'description' => 'Get the current status of the wallet bot.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Handle tools/call request.
     *
     * @param array $params
     * @return array
     */
    protected function handleToolsCall(array $params): array
    {
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        Log::info('MCP Server: Tool call', ['tool' => $toolName, 'arguments' => $arguments]);

        $result = match ($toolName) {
            'get_balance' => $this->executeGetBalance($arguments),
            'transfer_funds' => $this->executeTransferFunds($arguments),
            'get_transactions' => $this->executeGetTransactions($arguments),
            'bot_status' => $this->executeBotStatus(),
            default => throw new \InvalidArgumentException("Unknown tool: {$toolName}"),
        };

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result, JSON_PRETTY_PRINT),
                ],
            ],
            'isError' => !($result['success'] ?? true),
        ];
    }

    /**
     * Handle resources/list request.
     *
     * @return array
     */
    protected function handleResourcesList(): array
    {
        return [
            'resources' => [
                [
                    'uri' => 'chibank://status',
                    'name' => 'Bot Status',
                    'description' => 'Current status of the ChiBank bot',
                    'mimeType' => 'application/json',
                ],
            ],
        ];
    }

    /**
     * Handle resources/read request.
     *
     * @param array $params
     * @return array
     */
    protected function handleResourcesRead(array $params): array
    {
        $uri = $params['uri'] ?? '';

        if ($uri === 'chibank://status') {
            return [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'application/json',
                        'text' => json_encode($this->bot->getStatus()),
                    ],
                ],
            ];
        }

        throw new \InvalidArgumentException("Unknown resource: {$uri}");
    }

    /**
     * Handle prompts/list request.
     *
     * @return array
     */
    protected function handlePromptsList(): array
    {
        return [
            'prompts' => [
                [
                    'name' => 'wallet_assistant',
                    'description' => 'A helpful assistant for wallet operations',
                    'arguments' => [
                        [
                            'name' => 'user_id',
                            'description' => 'The user ID to assist',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Handle prompts/get request.
     *
     * @param array $params
     * @return array
     */
    protected function handlePromptsGet(array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        if ($name === 'wallet_assistant') {
            $userId = $arguments['user_id'] ?? 'unknown';
            return [
                'description' => "Wallet assistant for user {$userId}",
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            'type' => 'text',
                            'text' => "You are helping user {$userId} with their wallet. You can check their balance, help them transfer funds, and view their transaction history.",
                        ],
                    ],
                ],
            ];
        }

        throw new \InvalidArgumentException("Unknown prompt: {$name}");
    }

    /**
     * Execute get_balance tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeGetBalance(array $arguments): array
    {
        $userId = $arguments['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'error' => 'user_id is required'];
        }

        return $this->bot->getWalletBalance((int) $userId);
    }

    /**
     * Execute transfer_funds tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeTransferFunds(array $arguments): array
    {
        $senderId = $arguments['sender_id'] ?? null;
        $receiverId = $arguments['receiver_id'] ?? null;
        $amount = $arguments['amount'] ?? null;
        $currency = $arguments['currency'] ?? null;

        if (!$senderId || !$receiverId || !$amount) {
            return ['success' => false, 'error' => 'sender_id, receiver_id, and amount are required'];
        }

        return $this->bot->transferFunds((int) $senderId, (int) $receiverId, (float) $amount, $currency);
    }

    /**
     * Execute get_transactions tool.
     *
     * @param array $arguments
     * @return array
     */
    protected function executeGetTransactions(array $arguments): array
    {
        $userId = $arguments['user_id'] ?? null;
        $limit = $arguments['limit'] ?? 10;

        if (!$userId) {
            return ['success' => false, 'error' => 'user_id is required'];
        }

        return $this->bot->getTransactionHistory((int) $userId, (int) $limit);
    }

    /**
     * Execute bot_status tool.
     *
     * @return array
     */
    protected function executeBotStatus(): array
    {
        return [
            'success' => true,
            'status' => $this->bot->getStatus(),
        ];
    }

    /**
     * Build a successful JSON-RPC response.
     *
     * @param mixed $id
     * @param mixed $result
     * @return array
     */
    protected function buildResponse($id, $result): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    /**
     * Build an error JSON-RPC response.
     *
     * @param mixed $id
     * @param int $code
     * @param string $message
     * @return array
     */
    protected function buildErrorResponse($id, int $code, string $message): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }
}
