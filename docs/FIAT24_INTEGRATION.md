# Fiat24 Payment Gateway Integration

## Overview

This integration implements the Fiat24 payment gateway for the ChiBank platform, enabling Swiss digital banking services with blockchain integration.

## About Fiat24

Fiat24 is a Swiss-based digital banking platform that provides:

- **ERC-20 Tokens**: USD24, EUR24, CHF24, CNH24 on Arbitrum/Mantle blockchains
- **ERC-721 NFTs**: Representing Swiss bank accounts with IBAN
- **RESTful API**: With signature-based authentication
- **Smart Contracts**: Blockchain payment integration
- **Swiss Banking**: Full banking capabilities with Swiss IBAN

## Integration Documentation

Official Fiat24 Developer Documentation: [https://docs.fiat24.com/developer/integration-guide](https://docs.fiat24.com/developer/integration-guide)

### Key Documentation Sections

1. **Part 1: Smart Contracts** - https://docs.fiat24.com/developer/integration-guide/part-1-smart-contracts
2. **Part 2: API Reference** - https://docs.fiat24.com/developer/integration-guide/part-2-api-reference
3. **Part 3: Client Onboarding** - https://docs.fiat24.com/developer/integration-guide/part-3-client-onboarding
4. **Part 5: Authorized Access Model** - https://docs.fiat24.com/developer/integration-guide/part-5-authorized-access-model

## Implementation Details

### Files Modified/Created

1. **Payment Gateway Trait**
   - `app/Traits/PaymentGateway/Fiat24Trait.php` - Main payment processing logic

2. **Constants**
   - `app/Constants/PaymentGatewayConst.php` - Added Fiat24 constants

3. **Helper Classes**
   - `app/Http/Helpers/PaymentGateway.php` - Added Fiat24Trait usage

4. **Controllers**
   - `app/Http/Controllers/User/AddMoneyController.php` - Added callback methods
   - `app/Http/Controllers/Agent/AddMoneyController.php` - Added callback methods

5. **Routes**
   - `routes/user.php` - Added Fiat24 payment routes
   - `routes/agent.php` - Added Fiat24 payment routes

### Configuration Requirements

To enable Fiat24 in the admin panel, configure the following credentials:

1. **API Key** (client_id) - Your Fiat24 API key
2. **API Secret** (client_secret) - Your Fiat24 API secret
3. **NFT ID** (nft_id) - Your Developer NFT ID (token ID)
4. **Chain ID** (chain_id) - Blockchain network ID (default: 42161 for Arbitrum)
5. **Environment Mode** - Sandbox or Production

### Gateway Features

The Fiat24 integration supports:

- ✅ Add Money functionality
- ✅ Payment Link support
- ✅ Success/Cancel callbacks
- ✅ Transaction tracking
- ✅ Webhook support (signature verification)
- ✅ Multi-currency support (USD24, EUR24, CHF24, CNH24)
- ✅ Blockchain integration
- ✅ IBAN-based transfers

### Authentication

Fiat24 uses signature-based authentication:

```
Headers Required:
- tokenid: Token ID of user
- network: Chain ID (42161 for Arbitrum)
- sign: Wallet signature
- hash: Original text that was hashed
- deadline: Deadline used for signing
```

### Integration Methods

#### Method 1: No-Code Integration
Redirect users to Fiat24's onboarding page:
```
https://id.fiat24.com/login?wallet={walletTokenId}
```

#### Method 2: Code-Based Integration
Use Fiat24's REST API for custom onboarding flow with full control over the user experience.

### Smart Contract Addresses

**Arbitrum Network (Chain ID: 42161)**
- USD24 Token: Configured in Fiat24 documentation
- EUR24 Token: Configured in Fiat24 documentation
- CHF24 Token: Configured in Fiat24 documentation
- CNH24 Token: Configured in Fiat24 documentation
- Account NFT: Represents Swiss bank account with IBAN

### Payment Flow

1. User initiates "Add Money" with Fiat24 gateway
2. System creates temporary transaction record
3. User redirects to Fiat24 onboarding/payment page
4. User completes payment/onboarding on Fiat24
5. Fiat24 redirects back to success/cancel callback
6. System verifies payment and updates wallet balance
7. User receives confirmation notification

### Webhook Implementation

The integration includes webhook support for real-time payment notifications:

- Signature verification for security
- Deadline check to prevent replay attacks
- Automatic transaction status updates

### Error Handling

The integration includes comprehensive error handling:

- Invalid credentials
- Transaction verification failures
- Network errors
- Expired signatures
- Invalid webhook payloads

### Testing

#### Sandbox Mode
Configure gateway in Sandbox mode for testing:
- Base URL: `https://api-sandbox.fiat24.com`
- Use test NFT ID and credentials

#### Production Mode
Configure gateway in Production mode for live transactions:
- Base URL: `https://api.fiat24.com`
- Use production NFT ID and credentials

### Security Considerations

1. **Signature Verification**: All webhooks are verified using signature-based authentication
2. **Deadline Check**: Prevents replay attacks
3. **HTTPS Only**: All API calls use secure HTTPS
4. **Credential Storage**: Gateway credentials are encrypted in database
5. **Transaction Logging**: All transactions are logged for audit trail

### API Rate Limits

Fiat24 API has rate limits:
- Small projects: 80 requests/minute
- Large projects: 500 requests/minute

### Supported Currencies

- USD24 (US Dollar)
- EUR24 (Euro)
- CHF24 (Swiss Franc)
- CNH24 (Chinese Yuan)

### NFT Types

Fiat24 offers different NFT types for developers:
- **4-digit NFT**: Simple integration
- **2-digit NFT**: Advanced features with delegated access
- **1-digit NFT**: Full access with high-volume support

### Admin Panel Configuration

1. Navigate to Admin Panel → Payment Gateways
2. Add New Gateway or Edit existing Fiat24 gateway
3. Configure credentials:
   - Name: Fiat24
   - Alias: fiat24
   - Type: Automatic
   - API Key: [Your API Key]
   - API Secret: [Your API Secret]
   - NFT ID: [Your Developer NFT ID]
   - Chain ID: 42161 (Arbitrum)
   - Environment: Sandbox/Production
4. Enable the gateway
5. Configure supported currencies
6. Set transaction limits

### User Flow

**For Users:**
1. Go to "Add Money" section
2. Select Fiat24 as payment method
3. Enter amount
4. Click "Submit"
5. Redirect to Fiat24 onboarding/payment page
6. Complete KYC/AML if required (handled by Fiat24)
7. Authorize payment
8. Redirect back to ChiBank
9. See updated balance

**For Agents:**
Same flow as users but with agent-specific routes and permissions.

### Blockchain Integration

The integration supports blockchain features:
- Smart contract interaction
- ERC-20 token transfers
- ERC-721 NFT account management
- Multi-chain support (Arbitrum, Mantle)

### IBAN Integration

Each Fiat24 account comes with a Swiss IBAN:
- Unique IBAN per account
- SEPA payments support
- International transfers
- Bank-grade security

### Client Service

Fiat24 provides client support via Telegram Bot:
- Bot URL: https://t.me/fiat24bot
- Can be embedded in your application
- Handles customer inquiries
- Compliance and KYC support

## Maintenance

### Monitoring
- Monitor webhook delivery success rate
- Track API response times
- Check error logs regularly
- Monitor transaction success rates

### Updates
- Keep credentials up to date
- Monitor Fiat24 documentation for API changes
- Update chain IDs if network changes
- Test in sandbox before deploying to production

## Support

For Fiat24-specific issues:
- Visit: https://docs.fiat24.com
- Telegram: https://t.me/fiat24bot
- Email: support@fiat24.com

For ChiBank integration issues:
- Check application logs
- Review webhook delivery
- Verify credentials configuration
- Contact ChiBank support team

## Future Enhancements

Potential improvements for future versions:
- [ ] Advanced smart contract integration
- [ ] Direct ERC-20 token transfers
- [ ] NFT account management UI
- [ ] Multi-signature wallet support
- [ ] Recurring payments
- [ ] Subscription management
- [ ] Enhanced analytics and reporting
- [ ] Mobile SDK integration

## Changelog

### Version 1.0.0 (2024-11-24)
- Initial Fiat24 integration
- Add Money support
- Payment Link support
- Webhook implementation
- Success/Cancel callbacks
- User and Agent support
- Multi-currency support
- Blockchain integration foundation

## License

This integration follows the ChiBank platform license.

## References

- Fiat24 Developer Guide: https://docs.fiat24.com/developer/integration-guide
- Fiat24 Smart Contracts: https://docs.fiat24.com/developer/integration-guide/part-1-smart-contracts
- Fiat24 API Reference: https://docs.fiat24.com/developer/integration-guide/part-2-api-reference
- Arbitrum Network: https://arbitrum.io
- ERC-20 Standard: https://eips.ethereum.org/EIPS/eip-20
- ERC-721 Standard: https://eips.ethereum.org/EIPS/eip-721
