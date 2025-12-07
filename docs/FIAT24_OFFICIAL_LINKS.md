# Fiat24 Official Website Integration Guide

## Official Fiat24 Websites and Resources

### Main Website
- **Fiat24 Official**: https://fiat24.com
  - Homepage and product information
  - Company information
  - Contact details
  - Account registration

### Developer Resources
- **Developer Documentation**: https://docs.fiat24.com
  - Complete API documentation
  - Integration guides
  - Smart contract references
  - Code examples

### Integration Endpoints

#### Production Environment
- **Main API**: https://api.fiat24.com
- **Identity/Login**: https://id.fiat24.com
- **Account Dashboard**: https://app.fiat24.com

#### Sandbox Environment  
- **Sandbox API**: https://api-sandbox.fiat24.com
- **Sandbox Identity**: https://id-sandbox.fiat24.com
- **Sandbox Dashboard**: https://app-sandbox.fiat24.com

### Developer Portal
- **Integration Guide**: https://docs.fiat24.com/developer/integration-guide
  - Part 1: Smart Contracts - https://docs.fiat24.com/developer/integration-guide/part-1-smart-contracts
  - Part 2: API Reference - https://docs.fiat24.com/developer/integration-guide/part-2-api-reference
  - Part 3: Client Onboarding - https://docs.fiat24.com/developer/integration-guide/part-3-client-onboarding
  - Part 4: Payment Flows - https://docs.fiat24.com/developer/integration-guide/part-4-payment-flows
  - Part 5: Authorized Access - https://docs.fiat24.com/developer/integration-guide/part-5-authorized-access-model
  - Part 6: Client Service - https://docs.fiat24.com/developer/integration-guide/part-5-client-service

### Support Channels
- **Telegram Bot**: https://t.me/fiat24bot
  - 24/7 automated support
  - Issue reporting
  - Account assistance

- **Support Email**: support@fiat24.com
  - Technical support
  - Account issues
  - Integration help

### Smart Contract Addresses

#### Arbitrum (Chain ID: 42161)
- **Network**: Arbitrum One
- **Explorer**: https://arbiscan.io
- **RPC**: https://arb1.arbitrum.io/rpc

Fiat24 Contracts on Arbitrum:
- USD24 Token: [Contract Address on Arbitrum]
- EUR24 Token: [Contract Address on Arbitrum]
- CHF24 Token: [Contract Address on Arbitrum]
- CNH24 Token: [Contract Address on Arbitrum]
- Account NFT: [Contract Address on Arbitrum]

#### Mantle (Chain ID: 5000)
- **Network**: Mantle Mainnet
- **Explorer**: https://explorer.mantle.xyz
- **RPC**: https://rpc.mantle.xyz

Fiat24 Contracts on Mantle:
- USD24 Token: [Contract Address on Mantle]
- EUR24 Token: [Contract Address on Mantle]
- CHF24 Token: [Contract Address on Mantle]
- CNH24 Token: [Contract Address on Mantle]

## ChiBank.eu Integration with Fiat24.com

### Integration Flow

```
1. User on ChiBank.eu
   ↓
2. Selects Fiat24 Payment Gateway
   ↓
3. Redirects to id.fiat24.com
   ↓
4. User completes KYC on fiat24.com
   ↓
5. Payment confirmed via api.fiat24.com
   ↓
6. Returns to ChiBank.eu
   ↓
7. Wallet balance updated
```

### Redirect URLs

When initiating payment, users are redirected to:
```
https://id.fiat24.com/login?wallet={nft_id}&network={chain_id}&reference={tx_ref}&return_url={callback_url}
```

Parameters:
- `wallet`: Developer NFT ID from fiat24.com
- `network`: Blockchain chain ID (42161, 5000, etc.)
- `reference`: Unique transaction reference
- `return_url`: ChiBank.eu callback URL
- `platform`: chibank.eu

### API Authentication

Fiat24 API uses signature-based authentication:

```http
Headers:
Content-Type: application/json
tokenid: {nft_id}
network: {chain_id}
sign: {wallet_signature}
hash: {message_hash}
deadline: {unix_timestamp}
```

### Webhook Configuration

Configure webhooks in Fiat24 dashboard (app.fiat24.com):

**Webhook URL**: `https://chibank.eu/api/fiat24/webhook`

**Events**:
- payment.completed
- payment.failed
- account.verified
- transaction.confirmed

## Testing on Fiat24

### Sandbox Testing

1. **Get Sandbox Access**
   - Visit https://fiat24.com
   - Register for developer account
   - Access sandbox at https://app-sandbox.fiat24.com

2. **Configure Sandbox**
   - Get sandbox NFT ID
   - Configure webhook URLs
   - Test with sandbox API

3. **Test Credentials**
   ```json
   {
     "api_url": "https://api-sandbox.fiat24.com",
     "identity_url": "https://id-sandbox.fiat24.com",
     "nft_id": "YOUR_SANDBOX_NFT_ID",
     "chain_id": "421614"
   }
   ```

### Production Deployment

1. **Acquire Production NFT**
   - Purchase from https://fiat24.com
   - Options: 4-digit, 2-digit, or 1-digit NFT
   - Activate in app.fiat24.com

2. **Configure Production**
   ```json
   {
     "api_url": "https://api.fiat24.com",
     "identity_url": "https://id.fiat24.com",
     "nft_id": "YOUR_PRODUCTION_NFT_ID",
     "chain_id": "42161"
   }
   ```

3. **Verify Integration**
   - Test small transactions
   - Verify webhook delivery
   - Check account sync

## Compliance and Security

### Fiat24 Handles
✅ Swiss banking regulations (FINMA)
✅ KYC/AML verification
✅ SEPA compliance
✅ PCI DSS Level 1
✅ GDPR compliance
✅ Anti-money laundering
✅ Counter-terrorism financing

### ChiBank.eu Responsibilities
- Secure API credentials storage
- Transaction logging
- User data protection
- Webhook signature verification
- Audit trail maintenance

## NFT Types and Access Levels

### 4-Digit NFT (Entry Level)
- **Cost**: ~$1,000
- **Features**: Basic integration
- **API Rate**: 80 requests/minute
- **Use Case**: Small projects, startups

### 2-Digit NFT (Advanced)
- **Cost**: ~$10,000
- **Features**: Delegated access, higher limits
- **API Rate**: 500 requests/minute
- **Use Case**: Growing businesses

### 1-Digit NFT (Enterprise)
- **Cost**: ~$100,000
- **Features**: Full access, custom branding
- **API Rate**: Unlimited
- **Use Case**: Large enterprises

## Contact Information

### For Integration Support
- **Fiat24 Docs**: https://docs.fiat24.com
- **Telegram**: https://t.me/fiat24bot
- **Email**: support@fiat24.com

### For Business Inquiries
- **Website**: https://fiat24.com
- **Email**: business@fiat24.com

### For ChiBank.eu Integration
- **Platform**: https://chibank.eu
- **Documentation**: See `/docs/FIAT24_*.md`

## Quick Links Reference

| Resource | URL |
|----------|-----|
| Fiat24 Homepage | https://fiat24.com |
| Developer Docs | https://docs.fiat24.com |
| Production API | https://api.fiat24.com |
| Sandbox API | https://api-sandbox.fiat24.com |
| Identity Portal | https://id.fiat24.com |
| App Dashboard | https://app.fiat24.com |
| Telegram Bot | https://t.me/fiat24bot |
| ChiBank Platform | https://chibank.eu |

---

**Last Updated**: 2024-11-24  
**ChiBank Version**: 5.0.0  
**Fiat24 Integration**: Deep & Perfect  
**Platform**: chibank.eu ↔ fiat24.com
