# Admin API Documentation

## Overview
This document describes the API endpoints for connecting the admin frontend with the Laravel backend.

## Base URL
The admin API endpoints are available at:
```
https://chibank.eu/admin/api/
```

For local development:
```
http://localhost/admin/api/
```

## CORS Configuration
CORS is enabled for admin API endpoints with the following settings:
- Allowed methods: All (`*`)
- Allowed origins: All (`*`)
- Allowed headers: All (`*`)
- Credentials: Supported

## Authentication
Admin API endpoints require admin authentication. Ensure the user is logged in as an admin before making requests.

## Endpoints

### 1. Get Statistics
Returns dashboard statistics including today's transactions, orders, new users, and refunds.

**Endpoint:** `GET /admin/api/stats`

**Response:**
```json
{
  "todayAmount": 12345.67,
  "orders": 128,
  "newUsers": 12,
  "refunds": 3
}
```

**Response Fields:**
- `todayAmount` (float): Total transaction amount for today
- `orders` (integer): Number of orders today
- `newUsers` (integer): Number of new users registered today
- `refunds` (integer): Number of refunds/rejected transactions today

**Example:**
```bash
curl -X GET https://chibank.eu/admin/api/stats \
  -H "Accept: application/json" \
  --cookie "laravel_session=..."
```

---

### 2. Get Transactions
Returns a list of transactions with details.

**Endpoint:** `GET /admin/api/transactions`

**Query Parameters:**
- `status` (optional): Filter by transaction status (0=pending, 1=success, 2=awaiting, 3=processing, 4=refunded)
- `type` (optional): Filter by transaction type

**Response:**
```json
[
  {
    "orderId": "TRX123456789",
    "user": "alice",
    "amount": 199.00,
    "status": "成功",
    "time": "2025-01-01 10:20:30"
  },
  {
    "orderId": "TRX123456790",
    "user": "bob",
    "amount": 59.90,
    "status": "待支付",
    "time": "2025-01-01 11:05:12"
  }
]
```

**Response Fields:**
- `orderId` (string): Transaction ID
- `user` (string): Username who created the transaction
- `amount` (float): Transaction amount
- `status` (string): Transaction status in Chinese (待处理, 成功, 待支付, 处理中, 已退款)
- `time` (string): Transaction creation time (Y-m-d H:i:s)

**Example:**
```bash
curl -X GET https://chibank.eu/admin/api/transactions?status=1 \
  -H "Accept: application/json" \
  --cookie "laravel_session=..."
```

---

### 3. Get Users
Returns a list of users with their details.

**Endpoint:** `GET /admin/api/users`

**Query Parameters:**
- `role` (optional): Filter by user role

**Response:**
```json
[
  {
    "id": 1,
    "username": "alice",
    "email": "alice@example.com",
    "role": "user",
    "status": "active",
    "created_at": "2024-12-01 08:30:00"
  },
  {
    "id": 2,
    "username": "bob",
    "email": "bob@example.com",
    "role": "user",
    "status": "active",
    "created_at": "2024-12-02 14:15:00"
  }
]
```

**Response Fields:**
- `id` (integer): User ID
- `username` (string): Username
- `email` (string): User email address
- `role` (string): User role (user, admin, merchant, agent)
- `status` (string): Account status (active, inactive)
- `created_at` (string): Account creation time (Y-m-d H:i:s)

**Example:**
```bash
curl -X GET https://chibank.eu/admin/api/users \
  -H "Accept: application/json" \
  --cookie "laravel_session=..."
```

---

### 4. Get/Update Settings
Get or update system settings.

**Endpoint:** `GET /admin/api/settings` or `POST /admin/api/settings`

**GET Response:**
```json
{
  "siteName": "QRPAY",
  "callbackUrl": "https://chibank.eu/api/callback"
}
```

**POST Request Body:**
```json
{
  "siteName": "My New Site Name",
  "callbackUrl": "https://example.com/callback"
}
```

**POST Response:**
```json
{
  "success": true,
  "message": "设置已保存 (请注意: 当前实现不持久化设置)"
}
```

**⚠️ Important Note:**
The current implementation of the settings update endpoint validates input but does not persist changes. This is intentional for the initial version. To implement persistence, you should:
1. Create a settings table in the database
2. Update the controller to save/retrieve from the database
3. Or implement a proper settings management system

**Example GET:**
```bash
curl -X GET https://chibank.eu/admin/api/settings \
  -H "Accept: application/json" \
  --cookie "laravel_session=..."
```

**Example POST:**
```bash
curl -X POST https://chibank.eu/admin/api/settings \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"siteName":"New Name","callbackUrl":"https://example.com/callback"}' \
  --cookie "laravel_session=..."
```

---

## Frontend Integration

### Configuration
The frontend config is located at `/admin/config.js`:

```javascript
const CONFIG = {
  API_BASE: "https://chibank.eu",
  USE_PROXY: false,
  PROXY_BASE: "/api-proxy"
};
```

### Usage in Frontend
The frontend uses the `request()` function to make API calls:

```javascript
// Get statistics
const res = await request('api/stats');
const data = await res.json();

// Get transactions
const res = await request('api/transactions');
const list = await res.json();

// Get users
const res = await request('api/users');
const users = await res.json();

// Update settings
const res = await request('api/settings', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ siteName, callbackUrl })
});
```

### Fallback to Local Mock
If the API is not available, the frontend will automatically fall back to local JSON mock files:
- `/admin/api/stats.json`
- `/admin/api/transactions.json`
- `/admin/api/users.json`

---

## Error Handling

All API endpoints return appropriate HTTP status codes:
- `200 OK`: Success
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Not authenticated
- `403 Forbidden`: Not authorized
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

Example error response:
```json
{
  "error": "Unauthorized",
  "message": "You must be logged in as admin"
}
```

---

## Testing

### Local Development
1. Ensure Laravel is running:
   ```bash
   php artisan serve
   ```

2. Open the admin frontend:
   ```
   http://localhost:8000/admin/index.html
   ```

3. Check browser console for API calls and responses

### Production
1. Deploy Laravel backend to production server
2. Update `CONFIG.API_BASE` in `/admin/config.js` to your production URL
3. Ensure CORS is properly configured
4. Test all API endpoints

---

## Security Notes

1. **Authentication**: All endpoints should be protected by admin authentication middleware in production
2. **CSRF Protection**: Consider adding CSRF token validation for POST/PUT/DELETE requests
3. **Rate Limiting**: Implement rate limiting to prevent abuse
4. **Input Validation**: All user inputs are validated before processing
5. **SQL Injection**: Laravel's query builder protects against SQL injection
6. **XSS Prevention**: Always sanitize output in the frontend

---

## Maintenance

### Database Optimization
- Add indexes on frequently queried columns
- Optimize transaction queries with proper joins
- Cache frequently accessed data

### Monitoring
- Monitor API response times
- Track error rates
- Log suspicious activities

### Updates
- Keep Laravel and dependencies up to date
- Review security advisories regularly
- Test thoroughly before deploying updates

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode in `.env` for detailed errors (development only)
3. Review CORS configuration if having cross-origin issues
4. Verify database connection and credentials

---

**Last Updated:** 2024-12-05
**Version:** 1.0.0
**Author:** ChiBank Development Team
