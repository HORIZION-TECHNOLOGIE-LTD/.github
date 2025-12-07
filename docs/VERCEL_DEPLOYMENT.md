# Deploying ChiBank to Vercel

Quick guide for deploying ChiBank to Vercel with AI integration.

## Prerequisites

1. A Vercel account (sign up at https://vercel.com)
2. GitHub repository connected to Vercel
3. API keys for AI providers (optional but recommended):
   - OpenAI API key
   - Anthropic API key
   - Google AI API key

## Quick Deploy

### Option 1: Deploy via Vercel Dashboard

1. **Import Project**
   - Go to https://vercel.com/new
   - Import your GitHub repository
   - Vercel will auto-detect the framework

2. **Configure Environment Variables**
   Add these variables in the Vercel dashboard:
   ```
   APP_NAME=ChiBank
   APP_KEY=base64:your_app_key_here
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.vercel.app
   
   DB_CONNECTION=mysql
   DB_HOST=your_database_host
   DB_PORT=3306
   DB_DATABASE=chibank
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   OPENAI_API_KEY=your_openai_key
   ANTHROPIC_API_KEY=your_anthropic_key
   GOOGLE_API_KEY=your_google_key
   ```

3. **Deploy**
   - Click "Deploy"
   - Wait for the build to complete
   - Your app will be live at `https://your-project.vercel.app`

### Option 2: Deploy via Vercel CLI

```bash
# Install Vercel CLI
npm install -g vercel

# Login to Vercel
vercel login

# Deploy
vercel

# For production deployment
vercel --prod
```

## Database Setup

Vercel doesn't provide a database. You'll need to use an external database service:

### Recommended Options:
1. **PlanetScale** (MySQL-compatible, serverless)
2. **Railway** (PostgreSQL/MySQL)
3. **Supabase** (PostgreSQL)
4. **AWS RDS** (MySQL/PostgreSQL)

### Setup Steps:
1. Create a database on your chosen provider
2. Add the connection details to Vercel environment variables
3. Run migrations (see below)

## Running Migrations

After deployment, you need to initialize the database:

```bash
# Using Vercel CLI
vercel env pull .env.production
php artisan migrate --force --env=production

# Or use a serverless migration endpoint
curl -X POST https://your-domain.vercel.app/api/migrate \
  -H "Authorization: Bearer your-admin-token"
```

## AI Integration

The AI endpoints are available at:
- `/api/ai/health` - Check AI service status
- `/api/ai/models` - List available models
- `/api/ai/chat` - Chat with AI
- `/api/ai/complete` - Text completion
- `/api/ai/generate` - Content generation

See [VERCEL_AI_INTEGRATION.md](VERCEL_AI_INTEGRATION.md) for detailed documentation.

## Custom Domain

1. Go to your project settings in Vercel
2. Navigate to Domains
3. Add your custom domain
4. Update DNS records as instructed
5. Wait for DNS propagation (usually 5-10 minutes)

## Environment Variables

### Required Variables:
- `APP_KEY` - Laravel application key
- `APP_URL` - Your Vercel deployment URL
- Database credentials

### Optional (AI Integration):
- `OPENAI_API_KEY` - For GPT models
- `ANTHROPIC_API_KEY` - For Claude models
- `GOOGLE_API_KEY` - For Gemini models

## Troubleshooting

### Build Fails
- Check the build logs in Vercel dashboard
- Ensure all dependencies are in package.json and composer.json
- Verify PHP version compatibility (PHP 8.0.2+)

### Database Connection Issues
- Verify database credentials
- Check if database allows connections from Vercel IPs
- Ensure database is accessible from the internet

### AI Endpoints Not Working
- Verify API keys are set in environment variables
- Check that routes are registered correctly
- Review logs for specific errors

## Performance Optimization

1. **Enable Caching**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Use Edge Functions** (when available)
   Configure edge functions in vercel.json for better latency

3. **CDN for Static Assets**
   Vercel automatically serves static assets via CDN

## Monitoring

- View deployment logs: `vercel logs`
- Real-time logs: `vercel logs --follow`
- Check analytics in Vercel dashboard

## Scaling

Vercel automatically scales based on traffic. For database scaling:
- Use connection pooling
- Implement query caching
- Consider read replicas for high-traffic scenarios

## Security

1. **Never commit secrets** - Use environment variables
2. **Enable HTTPS** - Automatic with Vercel
3. **Rate limiting** - Configure in your Laravel app
4. **CORS** - Configure allowed origins

## Cost Considerations

- Vercel Pro plan recommended for production
- Monitor AI API usage (costs vary by provider)
- Database costs depend on your provider
- Consider implementing caching to reduce API calls

## Support

- Vercel Documentation: https://vercel.com/docs
- ChiBank Issues: https://github.com/hhongli1979-coder/chibank999/issues
- AI Integration Guide: [VERCEL_AI_INTEGRATION.md](VERCEL_AI_INTEGRATION.md)

## Next Steps

After deployment:
1. Test all endpoints
2. Set up monitoring and alerts
3. Configure custom domain
4. Set up CI/CD pipeline
5. Implement logging and error tracking

---

**Note:** For detailed AI integration instructions, see [VERCEL_AI_INTEGRATION.md](./VERCEL_AI_INTEGRATION.md)
