# Admin Panel - Frontend to Backend Connection Guide

## Overview
This admin panel connects to the Laravel backend API to display real-time statistics, transactions, and user data.

## Setup

### 1. Development (mock data):
- Open `admin/index.html` in browser. Data loads from `admin/api/*.json`.
- This is useful for frontend development without a backend connection.

### 2. Production (real backend API):

#### Step 1: Configure Backend URL
Edit `admin/config.js` and set `CONFIG.API_BASE` to your backend base URL:
```javascript
const CONFIG = { 
  API_BASE: "https://chibank.eu",  // Your production URL
  USE_PROXY: false,
  PROXY_BASE: "/api-proxy"
};
```

#### Step 2: Backend Endpoints
The backend now exposes these endpoints (automatically configured):
- GET `/admin/api/stats` -> `{ todayAmount, orders, newUsers, refunds }`
- GET `/admin/api/transactions` -> array of `{ orderId, user, amount, status, time }`
- GET `/admin/api/users` -> array of `{ id, username, email, role, status, created_at }`
- GET/POST `/admin/api/settings` -> `{ siteName, callbackUrl }`

#### Step 3: Deploy
- Host `admin/` folder under your web server
- The page will automatically fetch data from `API_BASE + /admin/api/*`
- Ensure CORS is enabled (already configured in `config/cors.php`)

### 3. Optional: Route Integration
- If using Laravel routing, serve `admin/index.html` at `/admin` route
- The API endpoints are already integrated in `routes/admin.php`

## Features

### Real-time Data
- Dashboard statistics (today's amount, orders, new users, refunds)
- Transaction list with search functionality
- User management interface
- System settings

### Automatic Fallback
If the backend API is unavailable, the admin panel automatically falls back to local mock data from `admin/api/*.json` files.

## API Documentation

For complete API documentation, see:
- `/ADMIN_API_DOCUMENTATION.md` - Detailed API endpoint reference
- Includes request/response examples, error handling, and security notes

## Architecture

```
┌─────────────────┐         ┌──────────────────┐
│  Admin Frontend │  HTTP   │  Laravel Backend │
│  (HTML/JS/CSS)  │ ──────> │  (API Endpoints) │
│                 │         │                  │
│  /admin/        │ <────── │  /admin/api/*    │
└─────────────────┘  JSON   └──────────────────┘
```

### Data Flow:
1. Frontend calls `request('api/stats')`
2. Config adds base URL: `https://chibank.eu/admin/api/stats`
3. Backend controller processes request
4. Returns JSON data
5. Frontend renders data in UI

## Testing

### Local Testing:
```bash
# Start Laravel backend
cd /path/to/chibank999
php artisan serve

# Open admin panel
# Visit: http://localhost:8000/admin/index.html
```

### Production Testing:
1. Deploy backend to production server
2. Update `CONFIG.API_BASE` in `config.js`
3. Test endpoints with browser developer tools
4. Verify CORS headers in network tab

## Troubleshooting

### CORS Issues
If you see CORS errors in console:
- Check `config/cors.php` includes `'admin/api/*'`
- Verify backend allows your domain
- Check credentials are included in requests

### 401 Unauthorized
- Ensure admin is logged in
- Check session cookies are sent with requests
- Verify authentication middleware is configured

### API Not Found
- Verify routes are registered in `routes/admin.php`
- Clear route cache: `php artisan route:clear`
- Check nginx/apache configuration for URL rewrites

## Security

### Production Checklist:
- [ ] Change `APP_DEBUG=false` in `.env`
- [ ] Use HTTPS for API_BASE
- [ ] Implement CSRF protection for POST/PUT/DELETE
- [ ] Add rate limiting to API endpoints
- [ ] Enable authentication middleware for all admin API routes
- [ ] Review CORS allowed origins (change from `*` to specific domains)
- [ ] Monitor API access logs

## Files Structure

```
admin/
├── index.html          # Main admin page
├── config.js           # Frontend configuration (API_BASE)
├── README.md           # This file
└── api/               # Mock data (fallback)
    ├── stats.json
    ├── transactions.json
    └── users.json

Backend:
├── routes/admin.php                              # API routes
├── app/Http/Controllers/Admin/AdminApiController.php  # API logic
├── config/cors.php                               # CORS configuration
└── ADMIN_API_DOCUMENTATION.md                    # API docs
```

## Next Steps

1. ✅ Backend API endpoints created
2. ✅ Frontend configuration updated
3. ✅ CORS configured
4. ✅ Documentation created
5. ⏳ Test with real data
6. ⏳ Add authentication middleware
7. ⏳ Deploy to production

## Support

For issues or questions:
- Check `ADMIN_API_DOCUMENTATION.md` for API reference
- Review Laravel logs: `storage/logs/laravel.log`
- Check browser console for JavaScript errors
- Verify network requests in browser DevTools

---

**Last Updated:** 2024-12-05
**Status:** ✅ Backend Connected
**Version:** 1.0.0
