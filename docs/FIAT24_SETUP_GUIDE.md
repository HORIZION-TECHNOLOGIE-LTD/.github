# Fiat24 Gateway Quick Setup Guide

## Prerequisites

- ChiBank platform installed and configured
- Admin panel access
- Fiat24 Developer NFT acquired from Fiat24
- API credentials from Fiat24 dashboard

## Setup Steps

### 1. Acquire Fiat24 Developer NFT

Visit [Fiat24 Developer Portal](https://fiat24.com/developer) and acquire a Developer NFT:

- **4-digit NFT**: Simple integration (starting point)
- **2-digit NFT**: Advanced features with delegated access
- **1-digit NFT**: Full access with high-volume support

### 2. Activate Your NFT

After purchasing, activate your NFT with Fiat24:

1. Login to your Fiat24 account
2. Navigate to Developer Settings
3. Activate your NFT
4. Note down your credentials

### 3. Get API Credentials

From Fiat24 Developer Dashboard, obtain:

- API Key (Client ID)
- API Secret (Client Secret)
- NFT ID (Token ID of your Developer NFT)
- Chain ID (42161 for Arbitrum, or use Mantle)

### 4. Add Gateway in Admin Panel

1. Login to ChiBank Admin Panel
2. Navigate to **Payment Gateways** → **Add Money**
3. Click "Add New Gateway" or "Edit Gateway"
4. Configure as follows:

**Basic Information:**
- Name: `Fiat24`
- Alias: `fiat24`
- Type: `Automatic`
- Code: `240` (or next available code)
- Status: `Active`

**Credentials:** (as JSON array)
```json
[
  {
    "label": "API Key",
    "placeholder": "Enter API Key",
    "name": "api-key",
    "value": "YOUR_API_KEY_HERE"
  },
  {
    "label": "API Secret",
    "placeholder": "Enter API Secret",
    "name": "api-secret",
    "value": "YOUR_API_SECRET_HERE"
  },
  {
    "label": "NFT ID",
    "placeholder": "Enter NFT ID",
    "name": "nft-id",
    "value": "YOUR_NFT_ID_HERE"
  },
  {
    "label": "Chain ID",
    "placeholder": "Enter Chain ID",
    "name": "chain-id",
    "value": "42161"
  }
]
```

**Supported Currencies:**
```json
["USD24", "EUR24", "CHF24", "CNH24"]
```

**Environment:**
- For testing: `SANDBOX`
- For production: `PRODUCTION`

### 5. Add Gateway Currencies

After creating the gateway, add supported currency conversions:

**For USD24:**
- Currency Code: `USD24`
- Currency Symbol: `$`
- Rate: `1.00` (or your preferred rate)
- Min Limit: `10.00`
- Max Limit: `10000.00`
- Percent Charge: `1.00`
- Fixed Charge: `1.00`

Repeat for EUR24, CHF24, CNH24 with appropriate rates.

### 6. Test the Integration

**Sandbox Testing:**

1. Ensure environment is set to `SANDBOX`
2. Use test NFT ID and credentials
3. Try adding money through user account
4. Verify transaction flow

**Production Deployment:**

1. Switch environment to `PRODUCTION`
2. Update credentials with production values
3. Test with small amount first
4. Monitor logs for any issues

### 7. Configure Webhooks (Optional)

For real-time payment notifications:

1. In Fiat24 dashboard, add webhook URL:
   ```
   https://yourdomain.com/api/fiat24/webhook
   ```

2. Configure webhook secret in admin panel

3. Test webhook delivery

## Verification Checklist

- [ ] Developer NFT acquired and activated
- [ ] API credentials obtained
- [ ] Gateway added in admin panel
- [ ] Gateway currencies configured
- [ ] Sandbox testing completed successfully
- [ ] Environment switched to production
- [ ] Production testing completed
- [ ] Webhooks configured (optional)
- [ ] Documentation reviewed by team

## Troubleshooting

### Gateway Not Appearing

- Check gateway status is `Active`
- Verify alias is exactly `fiat24`
- Clear application cache: `php artisan cache:clear`

### Payment Failing

- Verify API credentials are correct
- Check environment setting (SANDBOX vs PRODUCTION)
- Review application logs for errors
- Verify NFT is activated with Fiat24

### Redirect Issues

- Check callback URLs are accessible
- Verify domain is whitelisted in Fiat24
- Ensure HTTPS is enabled for production

### Balance Not Updating

- Check transaction status in database
- Review logs for processing errors
- Verify webhook is configured correctly

## Support

**Fiat24 Support:**
- Documentation: https://docs.fiat24.com
- Telegram Bot: https://t.me/fiat24bot
- Email: support@fiat24.com

**ChiBank Support:**
- Check application logs in `storage/logs`
- Review documentation in `/docs/FIAT24_INTEGRATION.md`
- Contact development team

## Additional Resources

- [Fiat24 Integration Documentation](/docs/FIAT24_INTEGRATION.md)
- [Fiat24 Official Guide](https://docs.fiat24.com/developer/integration-guide)
- [Fiat24 Smart Contracts](https://docs.fiat24.com/developer/integration-guide/part-1-smart-contracts)
- [Fiat24 API Reference](https://docs.fiat24.com/developer/integration-guide/part-2-api-reference)

## Security Notes

- Keep API credentials secure
- Never commit credentials to version control
- Use environment variables for sensitive data
- Enable rate limiting on webhooks
- Monitor transaction logs regularly
- Set appropriate transaction limits

## Next Steps

After successful setup:

1. **Customize Branding**: Work with Fiat24 to customize debit cards with your branding
2. **Add Token Metadata**: Ensure wallet apps support Fiat24 token display
3. **Deep Integration**: Explore advanced features like:
   - Multi-signature wallets
   - Recurring payments
   - IBAN-based transfers
   - Smart contract interactions

## Compliance

Fiat24 handles:
- ✅ KYC/AML verification
- ✅ Swiss banking regulations
- ✅ SEPA compliance
- ✅ Data protection (GDPR)

Your responsibility:
- Proper transaction recording
- User data protection
- Audit trail maintenance

---

**Last Updated**: 2024-11-24
**ChiBank Version**: 5.0.0
**Fiat24 Integration Version**: 1.0.0
