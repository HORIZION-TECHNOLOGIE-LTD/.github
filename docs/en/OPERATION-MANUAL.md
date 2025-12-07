# ChiBank/QRPay Operation Manual

## Table of Contents

1. [System Overview](#system-overview)
2. [System Requirements](#system-requirements)
3. [Installation Guide](#installation-guide)
4. [Configuration Guide](#configuration-guide)
5. [User Manual](#user-manual)
6. [Administrator Manual](#administrator-manual)
7. [API Documentation](#api-documentation)
8. [FAQ](#faq)
9. [Troubleshooting](#troubleshooting)

---

## System Overview

ChiBank/QRPay is a full-featured payment gateway system built on the Laravel framework, supporting multiple payment methods and user roles.

### Key Features

- **Multi-role Support**: Users, Agents, Merchants, Administrators
- **Multiple Payment Methods**: 
  - Stripe
  - PayPal
  - Flutterwave
  - SSLCommerz
  - Perfect Money
  - Paytm
- **Add Money and Withdraw**: Complete fund management workflow
- **Payment Links**: Merchants and users can create and share payment links
- **Mobile Top-up**: Mobile phone recharge service
- **Two-Factor Authentication**: Google 2FA support
- **Multi-language Support**: Internationalization features
- **Mobile Applications**: Flutter-based mobile clients

---

## System Requirements

### Server Requirements

- **Web Server**: Apache/Nginx
- **PHP Version**: >= 8.0.2
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Extensions**:
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - BCMath PHP Extension
  - GD Library
  - Fileinfo Extension

### Recommended Configuration

- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 10GB available space
- **CPU**: 2 cores or more

---

## Installation Guide

### Step 1: Download Source Code

```bash
git clone https://github.com/LILIANSRL/chibank-.git
cd chibank-
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

### Step 3: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Database Configuration

Edit the `.env` file and configure database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 5: Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

### Step 6: Create Storage Link

```bash
php artisan storage:link
```

### Step 7: Compile Assets

```bash
npm run build
```

### Step 8: Start Application

```bash
php artisan serve
```

Visit `http://localhost:8000` to view the application.

---

## Configuration Guide

### Basic Configuration

#### Application Configuration

Set in `.env` file:

```env
APP_NAME="ChiBank"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_MODE=live
```

#### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Payment Gateway Configuration

#### Stripe Configuration

1. Login to admin panel
2. Go to **Settings > Payment Gateway > Stripe**
3. Enter API keys:
   - Publishable Key
   - Secret Key
4. Save configuration

#### PayPal Configuration

1. Go to **Settings > Payment Gateway > PayPal**
2. Enter:
   - Client ID
   - Secret
   - Mode (Sandbox/Production)
3. Save configuration

#### SSLCommerz Configuration

1. Go to **Settings > Payment Gateway > SSLCommerz**
2. Enter:
   - Store ID
   - Store Password
3. Save configuration

### Cache and Queue Configuration

#### Cache Configuration

```bash
# Clear configuration cache
php artisan config:clear

# Cache configuration
php artisan config:cache

# Clear route cache
php artisan route:clear

# Cache routes
php artisan route:cache
```

#### Queue Configuration

```env
QUEUE_CONNECTION=database
```

Start queue worker:

```bash
php artisan queue:work
```

---

## User Manual

### Registration and Login

#### Register New Account

1. Visit registration page
2. Fill required information:
   - Email
   - Password
   - Confirm Password
   - Other personal information
3. Accept terms and conditions
4. Click "Register"
5. Verify email

#### Login

1. Visit login page
2. Enter email and password
3. If 2FA is enabled, enter verification code
4. Click "Login"

### Account Management

#### Profile Settings

1. Login to account
2. Go to **Profile**
3. Update personal information
4. Upload avatar
5. Save changes

#### Security Settings

1. Go to **Security Settings**
2. Enable Two-Factor Authentication (2FA)
3. Change password
4. View login history

### Add Money Operations

#### Add Money via Payment Gateway

1. Go to **Add Money** page
2. Select payment gateway
3. Enter amount
4. Click "Continue"
5. Complete payment on gateway page
6. Wait for confirmation

#### Manual Add Money

1. Go to **Add Money** page
2. Select "Manual Payment"
3. Follow transfer instructions
4. Upload payment proof
5. Wait for admin approval

### Withdraw Operations

1. Go to **Withdraw** page
2. Select withdrawal method
3. Enter amount
4. Enter withdrawal account details
5. Submit request
6. Wait for processing

### Payment Links

#### Create Payment Link

1. Go to **Payment Links**
2. Click "Create New Link"
3. Set:
   - Title
   - Amount
   - Currency
   - Validity period
4. Generate link
5. Share link

#### Manage Payment Links

1. View all payment links
2. Check payment status
3. Delete or disable links

### Transaction History

1. Go to **Transaction History**
2. View all transaction records
3. Use filters:
   - Date range
   - Transaction type
   - Status
4. Export transaction reports

---

## Administrator Manual

### Dashboard

The admin dashboard displays:
- Total users
- Total transactions
- Today's revenue
- Pending requests
- System health status

### User Management

#### View Users

1. Go to **User Management**
2. View all users list
3. Use search and filter features

#### Edit User

1. Select user
2. Edit user information
3. Adjust user balance
4. Set user status (Active/Disabled)
5. Save changes

#### User Verification

1. Go to **KYC Verification**
2. Review pending documents
3. Approve or reject verification
4. Add notes

### Transaction Management

#### View Transactions

1. Go to **Transaction Management**
2. View all transactions
3. View transaction details
4. Export reports

#### Process Withdraw Requests

1. Go to **Withdraw Requests**
2. View pending requests
3. Verify request details
4. Approve or reject
5. Add processing notes

### Payment Gateway Management

#### Configure Payment Gateways

1. Go to **Payment Gateway Settings**
2. Select payment gateway
3. Enable/disable gateway
4. Configure API keys
5. Set fees
6. Save configuration

#### Test Payment Gateway

1. Test in sandbox mode
2. Make test transactions
3. Verify callbacks
4. Check logs

### System Settings

#### Basic Settings

1. Go to **System Settings**
2. Configure:
   - Site name
   - Logo
   - Contact information
   - Timezone
   - Currency
3. Save settings

#### Email Templates

1. Go to **Email Templates**
2. Edit templates:
   - Welcome email
   - Transaction confirmation
   - Withdrawal notification
3. Use variable placeholders
4. Preview and save

#### Fee Settings

1. Go to **Fee Settings**
2. Set:
   - Add money fees
   - Withdrawal fees
   - Transfer fees
3. By percentage or fixed amount
4. Save configuration

### Reports and Analytics

#### Generate Reports

1. Go to **Reports**
2. Select report type:
   - Financial reports
   - User reports
   - Transaction reports
3. Set date range
4. Generate and export

#### View Analytics

1. Go to **Analytics**
2. View charts:
   - Revenue trends
   - User growth
   - Transaction volume
3. Use filters

---

## API Documentation

### Authentication

All API requests require authentication using Laravel Passport OAuth2.

#### Get Access Token

**Endpoint**: `POST /api/access-token`

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Login Successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_at": "2025-12-18 05:07:38"
  }
}
```

### User Endpoints

#### Get User Profile

**Endpoint**: `GET /api/user/profile`

**Headers**:
```
Authorization: Bearer {access_token}
```

**Response**:
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john@example.com",
      "balance": 1000.00
    }
  }
}
```

#### Add Money

**Endpoint**: `POST /api/add-money/submit`

**Headers**:
```
Authorization: Bearer {access_token}
```

**Request Body**:
```json
{
  "gateway": "stripe",
  "amount": 100.00,
  "currency": "USD"
}
```

### Payment Link Endpoints

#### Create Payment Link

**Endpoint**: `POST /api/payment-link/create`

**Headers**:
```
Authorization: Bearer {access_token}
```

**Request Body**:
```json
{
  "title": "Product Payment",
  "amount": 50.00,
  "currency": "USD",
  "limit": 10
}
```

### Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 400  | Bad Request |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 500  | Server Error |

---

## FAQ

### 1. How to reset password?

Click "Forgot Password" link on login page, enter email, and follow email instructions to reset password.

### 2. Which payment gateways are supported?

The system supports Stripe, PayPal, Flutterwave, SSLCommerz, Perfect Money, Paytm, and more.

### 3. How are fees calculated?

Fees are set by administrators in the backend and can be either a fixed amount or a percentage of the transaction.

### 4. How long does withdrawal processing take?

Usually processed within 24-48 hours, depending on admin review and payment gateway processing speed.

### 5. How to enable Two-Factor Authentication?

Go to Security Settings in your profile, scan QR code, and enter verification code to enable.

### 6. Where to download mobile app?

Mobile app source code is in `qrpay-user-app` directory and needs to be compiled and published.

---

## Troubleshooting

### Payment Failed

**Possible Causes**:
1. Payment gateway misconfigured
2. API keys expired
3. Insufficient balance
4. Network connection issues

**Solutions**:
1. Check payment gateway configuration
2. Update API keys
3. Verify account balance
4. Check network connection
5. Review log files

### Email Sending Failed

**Possible Causes**:
1. SMTP configuration error
2. Mail server issues
3. Firewall blocking

**Solutions**:
1. Verify SMTP settings in `.env`
2. Test mail server connection
3. Check firewall settings
4. Review `storage/logs` logs

### Page Loading Slow

**Solutions**:
1. Enable caching:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
2. Optimize database queries
3. Use Redis cache
4. Enable CDN

### Database Connection Error

**Solutions**:
1. Check `.env` database configuration
2. Confirm database service is running
3. Verify user permissions
4. Check firewall rules

### Permission Issues

**Solutions**:
```bash
# Set correct permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Technical Support

For more help, contact:

- **Email**: support@chibank.com
- **Documentation**: https://chibank.com/docs
- **Community Forum**: https://forum.chibank.com

---

**Version**: 5.0.0  
**Last Updated**: November 18, 2025
