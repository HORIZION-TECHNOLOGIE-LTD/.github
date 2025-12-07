# Multiple Inheritance Bot System with MCP Server Integration

This document describes the bot system with multiple inheritance support and MCP (Model Context Protocol) server integration for ChiBank.

## Overview

The bot system is designed using PHP traits to achieve multiple inheritance, allowing bots to combine various capabilities:

- **BaseBotTrait**: Core bot functionality (ID, name, status, logging)
- **MessagingTrait**: Message sending and receiving capabilities
- **WalletTrait**: Wallet operations (balance, transfer, history)
- **CommandTrait**: Command handling system
- **McpTrait**: Model Context Protocol support for AI integration

## Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                         WalletBot                                │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐    │
│  │BaseBotTrait│ │MessagingTr │ │WalletTrait │ │CommandTrait│    │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘    │
│                      ┌────────────┐                              │
│                      │  McpTrait  │                              │
│                      └────────────┘                              │
└──────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────────┐
│                      McpServerService                            │
│  - Handles JSON-RPC requests                                     │
│  - Exposes tools for AI agents                                   │
│  - Supports SSE for real-time updates                            │
└──────────────────────────────────────────────────────────────────┘
```

## Interfaces (Contracts)

### BotInterface

The core contract that all bots must implement:

```php
interface BotInterface
{
    public function getBotId(): string;
    public function getName(): string;
    public function processInput(array $input): array;
    public function getStatus(): array;
    public function isEnabled(): bool;
}
```

### MessagingCapable

For bots that can send and receive messages:

```php
interface MessagingCapable
{
    public function sendMessage($recipient, string $message, array $options = []): ?array;
    public function handleIncomingMessage(array $messageData): void;
}
```

### WalletCapable

For bots that can perform wallet operations:

```php
interface WalletCapable
{
    public function getWalletBalance(int $userId): array;
    public function transferFunds(int $senderId, int $receiverId, float $amount, ?string $currency = null): array;
    public function getTransactionHistory(int $userId, int $limit = 10): array;
}
```

### CommandCapable

For bots that can handle commands:

```php
interface CommandCapable
{
    public function getAvailableCommands(): array;
    public function executeCommand(string $command, array $arguments = [], array $context = []): array;
    public function isValidCommand(string $command): bool;
}
```

## Creating a New Bot with Multiple Inheritance

To create a new bot, combine the traits you need:

```php
use App\Services\Bot\Contracts\BotInterface;
use App\Services\Bot\Traits\BaseBotTrait;
use App\Services\Bot\Traits\MessagingTrait;
use App\Services\Bot\Traits\CommandTrait;

class MyCustomBot implements BotInterface
{
    use BaseBotTrait;
    use MessagingTrait;
    use CommandTrait;
    
    public function __construct(array $config = [])
    {
        $this->initializeBot($config['bot_id'], $config['name'], $config);
        $this->initializeCommands();
        // Register custom commands
        $this->registerCommand('custom', 'My custom command', [$this, 'handleCustomCommand']);
    }
    
    public function processInput(array $input): array
    {
        // Custom input processing
    }
    
    protected function handleCustomCommand(array $args, array $context): array
    {
        // Handle the custom command
        return ['success' => true, 'result' => 'Custom command executed'];
    }
}
```

## MCP Server Integration

### Configuration

Add the following to your `.env` file:

```env
MCP_SERVER_NAME=ChiBank MCP Server
MCP_SERVER_VERSION=1.0.0
MCP_ENABLED=true
MCP_AUTH_ENABLED=false
MCP_AUTH_TOKEN=
MCP_RATE_LIMIT_ENABLED=true
MCP_RATE_LIMIT_MAX=60
MCP_RATE_LIMIT_DECAY=1
MCP_LOGGING_ENABLED=true
MCP_LOG_CHANNEL=stack
```

### Available MCP Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/mcp` | POST | Main JSON-RPC endpoint |
| `/mcp/info` | GET | Server information |
| `/mcp/sse` | GET | Server-Sent Events for real-time updates |

### MCP Tools

The MCP server exposes the following tools for AI agents:

| Tool | Description |
|------|-------------|
| `get_balance` | Get wallet balance for a user |
| `transfer_funds` | Transfer funds between users |
| `get_transactions` | Get transaction history |
| `bot_status` | Get bot status |

### Example MCP Request

```json
{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/call",
    "params": {
        "name": "get_balance",
        "arguments": {
            "user_id": 123
        }
    }
}
```

### Example MCP Response

```json
{
    "jsonrpc": "2.0",
    "id": 1,
    "result": {
        "content": [
            {
                "type": "text",
                "text": "{\"success\":true,\"wallets\":[{\"currency_code\":\"USD\",\"balance\":\"1,000.00\"}]}"
            }
        ]
    }
}
```

## Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Bot/
│           └── McpController.php
├── Services/
│   ├── Bot/
│   │   ├── Contracts/
│   │   │   ├── BotInterface.php
│   │   │   ├── MessagingCapable.php
│   │   │   ├── WalletCapable.php
│   │   │   └── CommandCapable.php
│   │   ├── Traits/
│   │   │   ├── BaseBotTrait.php
│   │   │   ├── MessagingTrait.php
│   │   │   ├── WalletTrait.php
│   │   │   ├── CommandTrait.php
│   │   │   └── McpTrait.php
│   │   └── WalletBot.php
│   └── Mcp/
│       └── McpServerService.php
config/
└── mcp.php
routes/
└── mcp.php
tests/
└── Unit/
    ├── BotContractsTest.php
    ├── BotTraitsTest.php
    ├── WalletBotTest.php
    └── McpServerServiceTest.php
```

## Running Tests

```bash
# Run all bot tests
./vendor/bin/phpunit tests/Unit/BotContractsTest.php tests/Unit/BotTraitsTest.php tests/Unit/WalletBotTest.php tests/Unit/McpServerServiceTest.php --testdox

# Run individual test files
./vendor/bin/phpunit tests/Unit/WalletBotTest.php --testdox
```

## Security Considerations

- MCP endpoints support optional authentication via token
- Rate limiting is enabled by default
- Wallet transfers use database locks to prevent race conditions
- All input is validated before processing

## Extending the System

To add new capabilities:

1. Create a new interface in `app/Services/Bot/Contracts/`
2. Create a new trait in `app/Services/Bot/Traits/`
3. Add the trait to your bot class
4. Register any new MCP tools in `McpTrait::registerDefaultMcpTools()`
