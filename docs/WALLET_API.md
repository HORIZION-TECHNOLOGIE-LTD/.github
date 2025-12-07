# ChiBank Wallet API Documentation

## Overview

The ChiBank Wallet API provides comprehensive wallet management functionality for all platforms (Web, Mobile App, External API). This API supports multiple user types (Users, Agents, Merchants) with multi-currency support including both Fiat and Crypto currencies.

## 钱包API概述

ChiBank钱包API为所有平台（Web、移动应用、外部API）提供全面的钱包管理功能。该API支持多种用户类型（用户、代理、商户），并支持多币种，包括法币和加密货币。

---

## Authentication (认证)

All wallet API endpoints require authentication using Bearer token.

```
Authorization: Bearer {your_access_token}
```

---

## User Wallet API Endpoints (用户钱包API端点)

Base URL: `/api/user/wallets`

### 1. Get All Wallets (获取所有钱包)

**GET** `/api/user/wallets`

Returns all wallets with statistics.

**Response:**
```json
{
    "message": {
        "success": ["Wallets retrieved successfully"]
    },
    "data": {
        "wallets": [
            {
                "id": 1,
                "currency_code": "USD",
                "currency_name": "US Dollar",
                "currency_symbol": "$",
                "currency_type": "fiat",
                "balance": 1500.50,
                "formatted_balance": "1,500.50 USD",
                "status": "active",
                "rate": 1,
                "flag": "us.png",
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-15T12:30:00.000000Z"
            }
        ],
        "statistics": {
            "total_wallets": 5,
            "fiat_wallets": 3,
            "crypto_wallets": 2,
            "active_wallets": 5,
            "inactive_wallets": 0,
            "total_balance": 2500.00,
            "base_currency": "USD"
        },
        "base_currency": "USD"
    }
}
```

### 2. Get Fiat Wallets Only (仅获取法币钱包)

**GET** `/api/user/wallets/fiat`

Returns only fiat currency wallets.

**Response:**
```json
{
    "message": {
        "success": ["Fiat wallets retrieved successfully"]
    },
    "data": {
        "wallets": [...],
        "total_count": 3,
        "total_balance": 1800.00
    }
}
```

### 3. Get Crypto Wallets Only (仅获取加密货币钱包)

**GET** `/api/user/wallets/crypto`

Returns only cryptocurrency wallets.

**Response:**
```json
{
    "message": {
        "success": ["Crypto wallets retrieved successfully"]
    },
    "data": {
        "wallets": [...],
        "total_count": 2,
        "wallets_with_balance": 1
    }
}
```

### 4. Get Wallet Balance (获取钱包余额)

**GET** `/api/user/wallets/balance?currency={code}`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| currency | string | Yes | Currency code (e.g., USD, EUR, BTC) |

**Response:**
```json
{
    "message": {
        "success": ["Balance retrieved successfully"]
    },
    "data": {
        "currency": "USD",
        "balance": 1500.50,
        "formatted_balance": "1,500.50 USD",
        "status": "active"
    }
}
```

### 5. Get Wallet Details (获取钱包详情)

**GET** `/api/user/wallets/details?currency={code}`

Returns detailed wallet information including recent transactions.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| currency | string | Yes | Currency code |

**Response:**
```json
{
    "message": {
        "success": ["Wallet details retrieved successfully"]
    },
    "data": {
        "wallet": {
            "id": 1,
            "currency_code": "USD",
            "currency_name": "US Dollar",
            "balance": 1500.50,
            "status": "active"
        },
        "recent_transactions": [
            {
                "id": 100,
                "trx_id": "TRX123456789",
                "type": "ADD-MONEY",
                "attribute": "RECEIVED",
                "request_amount": "500.00",
                "payable": "495.00",
                "status": "Completed",
                "created_at": "2024-01-15T10:00:00.000000Z"
            }
        ],
        "can_send": true,
        "can_receive": true
    }
}
```

### 6. Get Wallet Statistics (获取钱包统计)

**GET** `/api/user/wallets/statistics`

Returns comprehensive wallet statistics.

**Response:**
```json
{
    "message": {
        "success": ["Wallet statistics retrieved successfully"]
    },
    "data": {
        "total_wallets": 5,
        "fiat_wallets": 3,
        "crypto_wallets": 2,
        "active_wallets": 5,
        "inactive_wallets": 0,
        "total_balance": 2500.00,
        "base_currency": "USD",
        "fiat_balance": 1800.00,
        "crypto_wallets_with_balance": 1,
        "total_add_money": "5000.00",
        "total_withdraw": "2000.00",
        "total_sent": "500.00",
        "total_received": "300.00"
    }
}
```

### 7. Internal Wallet Transfer (内部钱包转账)

**POST** `/api/user/wallets/transfer`

Transfer funds between your own wallets.

**Request Body:**
```json
{
    "from_currency": "USD",
    "to_currency": "EUR",
    "amount": 100.00
}
```

**Response:**
```json
{
    "message": {
        "success": ["Transfer completed successfully"]
    },
    "data": {
        "transfer": {
            "from_wallet": {
                "currency": "USD",
                "amount_deducted": 100.00,
                "new_balance": 1400.50
            },
            "to_wallet": {
                "currency": "EUR",
                "amount_added": 92.35,
                "new_balance": 292.35
            },
            "conversion_rate": 0.9235
        },
        "message": "Successfully transferred 100.00 USD to EUR wallet"
    }
}
```

### 8. Get Wallet Transactions (获取钱包交易记录)

**GET** `/api/user/wallets/transactions`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| currency | string | No | Filter by currency code |
| type | string | No | Filter by transaction type |
| limit | integer | No | Results per page (default: 20, max: 100) |
| page | integer | No | Page number (default: 1) |

**Response:**
```json
{
    "message": {
        "success": ["Transactions retrieved successfully"]
    },
    "data": {
        "transactions": [
            {
                "id": 100,
                "trx_id": "TRX123456789",
                "type": "ADD-MONEY",
                "attribute": "RECEIVED",
                "request_amount": "500.00",
                "payable": "495.00",
                "total_charge": "5.00",
                "status": "Completed",
                "remark": "Added via PayPal",
                "created_at": "2024-01-15T10:00:00.000000Z",
                "updated_at": "2024-01-15T10:01:00.000000Z"
            }
        ],
        "pagination": {
            "total": 150,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 8,
            "has_more": true
        }
    }
}
```

### 9. Get Supported Currencies (获取支持的货币)

**GET** `/api/user/wallets/currencies`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | No | Filter by type: "fiat" or "crypto" |

**Response:**
```json
{
    "message": {
        "success": ["Currencies retrieved successfully"]
    },
    "data": {
        "currencies": [
            {
                "id": 1,
                "code": "USD",
                "name": "US Dollar",
                "symbol": "$",
                "type": "fiat",
                "rate": 1,
                "flag": "us.png",
                "sender": true,
                "receiver": true
            }
        ],
        "total_count": 10,
        "base_currency": "USD"
    }
}
```

### 10. Check Balance Sufficiency (检查余额是否充足)

**POST** `/api/user/wallets/check-balance`

**Request Body:**
```json
{
    "currency": "USD",
    "amount": 500.00
}
```

**Response:**
```json
{
    "message": {
        "success": ["Balance check completed"]
    },
    "data": {
        "currency": "USD",
        "requested_amount": "500.00",
        "available_balance": "1,500.50",
        "sufficient": true,
        "shortfall": 0
    }
}
```

---

## Agent Wallet API Endpoints (代理钱包API端点)

Base URL: `/api/agent/wallets`

The Agent Wallet API follows the same structure as the User Wallet API with identical endpoints:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/` | GET | Get all wallets |
| `/fiat` | GET | Get fiat wallets only |
| `/crypto` | GET | Get crypto wallets only |
| `/balance` | GET | Get specific wallet balance |
| `/details` | GET | Get wallet details |
| `/statistics` | GET | Get wallet statistics |
| `/transfer` | POST | Internal wallet transfer |
| `/transactions` | GET | Get wallet transactions |
| `/check-balance` | POST | Check balance sufficiency |

---

## Merchant Wallet API Endpoints (商户钱包API端点)

Base URL: `/api/merchant/wallets`

The Merchant Wallet API includes all standard endpoints plus additional merchant-specific features:

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/` | GET | Get all wallets |
| `/fiat` | GET | Get fiat wallets only |
| `/crypto` | GET | Get crypto wallets only |
| `/balance` | GET | Get specific wallet balance |
| `/details` | GET | Get wallet details |
| `/statistics` | GET | Get wallet statistics (includes payment data) |
| `/transfer` | POST | Internal wallet transfer |
| `/transactions` | GET | Get wallet transactions |
| `/check-balance` | POST | Check balance sufficiency |
| `/update-status` | POST | Update wallet status (active/inactive) |

### Merchant Statistics Response (商户统计响应)

```json
{
    "message": {
        "success": ["Wallet statistics retrieved successfully"]
    },
    "data": {
        "total_wallets": 3,
        "fiat_wallets": 2,
        "crypto_wallets": 1,
        "active_wallets": 3,
        "total_balance": 5000.00,
        "base_currency": "USD",
        "total_received": "10000.00",
        "total_withdraw": "5000.00",
        "total_payments": 150,
        "total_payment_links": 25
    }
}
```

---

## Error Responses (错误响应)

### Validation Error (验证错误)
```json
{
    "message": {
        "error": ["The currency field is required."]
    },
    "data": null
}
```

### Wallet Not Found (钱包未找到)
```json
{
    "message": {
        "error": ["Wallet not found for currency: XYZ"]
    },
    "data": null
}
```

### Insufficient Balance (余额不足)
```json
{
    "message": {
        "error": ["Insufficient balance in source wallet"]
    },
    "data": null
}
```

### Unauthorized (未授权)
```json
{
    "message": {
        "error": ["Unauthorized"]
    },
    "data": null
}
```

---

## Transaction Types (交易类型)

| Type | Description | 中文描述 |
|------|-------------|---------|
| ADD-MONEY | Deposit/Add funds | 充值 |
| MONEY-OUT | Withdrawal | 提现 |
| TRANSFER-MONEY | User transfer | 转账 |
| MERCHANT-PAYMENT | Payment to merchant | 商户支付 |
| BILL-PAY | Bill payment | 账单支付 |
| MOBILE-TOPUP | Mobile recharge | 手机充值 |
| VIRTUAL-CARD | Virtual card operations | 虚拟卡操作 |
| REMITTANCE | Cross-border remittance | 跨境汇款 |
| REQUEST-MONEY | Money request | 请求收款 |
| PAY-LINK | Payment link transaction | 支付链接交易 |
| GIFT-CARD | Gift card purchase | 礼品卡购买 |

---

## Rate Limiting (速率限制)

- Standard rate limit: 60 requests per minute
- Authenticated users: 120 requests per minute

---

## SDK Integration (SDK集成)

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class WalletService {
  final String baseUrl;
  final String token;
  
  WalletService({required this.baseUrl, required this.token});
  
  Future<Map<String, dynamic>> getAllWallets() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/user/wallets'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load wallets');
    }
  }
  
  Future<Map<String, dynamic>> getBalance(String currency) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/user/wallets/balance?currency=$currency'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to get balance');
    }
  }
  
  Future<Map<String, dynamic>> transfer({
    required String fromCurrency,
    required String toCurrency,
    required double amount,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/user/wallets/transfer'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'from_currency': fromCurrency,
        'to_currency': toCurrency,
        'amount': amount,
      }),
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to transfer');
    }
  }
}
```

### JavaScript/Web Example

```javascript
class WalletAPI {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
  }
  
  async getAllWallets() {
    const response = await fetch(`${this.baseUrl}/api/user/wallets`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json',
      },
    });
    
    if (!response.ok) {
      throw new Error('Failed to load wallets');
    }
    
    return response.json();
  }
  
  async getBalance(currency) {
    const response = await fetch(`${this.baseUrl}/api/user/wallets/balance?currency=${currency}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json',
      },
    });
    
    if (!response.ok) {
      throw new Error('Failed to get balance');
    }
    
    return response.json();
  }
  
  async transfer(fromCurrency, toCurrency, amount) {
    const response = await fetch(`${this.baseUrl}/api/user/wallets/transfer`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from_currency: fromCurrency,
        to_currency: toCurrency,
        amount: amount,
      }),
    });
    
    if (!response.ok) {
      throw new Error('Failed to transfer');
    }
    
    return response.json();
  }
}
```

---

## Support (支持)

For API support and questions:
- Email: support@chibank.com
- Documentation: https://docs.chibank.com

---

**Version**: 5.0.0  
**Last Updated**: November 2024
