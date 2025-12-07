# Vercel AI SDK Integration Guide

This document describes how to integrate and use AI models in ChiBank with Vercel AI SDK.

## Overview

ChiBank now supports seamless integration with multiple AI model providers through Vercel's AI SDK. This allows you to leverage powerful AI capabilities in your payment gateway application.

## Supported AI Providers

### OpenAI
- GPT-4
- GPT-3.5 Turbo
- GPT-4 Turbo

### Anthropic
- Claude 3 Opus
- Claude 3 Sonnet
- Claude 3 Haiku

### Google
- Gemini Pro
- Gemini Pro Vision

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
# Enable/Disable AI Features
AI_ENABLED=true
AI_PLACEHOLDER_MODE=false

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_ORGANIZATION=your_organization_id

# Anthropic Configuration
ANTHROPIC_API_KEY=your_anthropic_api_key

# Google AI Configuration
GOOGLE_API_KEY=your_google_api_key

# Default AI Model Settings
AI_DEFAULT_MODEL=gpt-3.5-turbo
AI_DEFAULT_TEMPERATURE=0.7
AI_DEFAULT_MAX_TOKENS=1024
```

**Important:** Set `AI_PLACEHOLDER_MODE=false` to enable actual AI integration. When set to `true`, the API will return placeholder responses.

### Obtaining API Keys

#### OpenAI
1. Visit https://platform.openai.com/api-keys
2. Create a new API key
3. Copy the key to your `.env` file

#### Anthropic
1. Visit https://console.anthropic.com/settings/keys
2. Create a new API key
3. Copy the key to your `.env` file

#### Google AI
1. Visit https://makersuite.google.com/app/apikey
2. Create a new API key
3. Copy the key to your `.env` file

## API Endpoints

### Health Check
```http
GET /api/ai/health
```

**Response:**
```json
{
  "status": "ok",
  "service": "ChiBank AI Integration",
  "providers": {
    "openai": true,
    "anthropic": false,
    "google": true
  },
  "timestamp": "2025-12-05T21:58:00Z"
}
```

### List Available Models
```http
GET /api/ai/models
```

**Response:**
```json
{
  "models": [
    {
      "id": "gpt-4",
      "name": "GPT-4",
      "provider": "openai",
      "available": true
    },
    {
      "id": "claude-3-opus",
      "name": "Claude 3 Opus",
      "provider": "anthropic",
      "available": false
    }
  ],
  "message": "Available AI models"
}
```

### Chat Completion
```http
POST /api/ai/chat
```

**Request Body:**
```json
{
  "messages": [
    {
      "role": "system",
      "content": "You are a helpful assistant for ChiBank payment gateway."
    },
    {
      "role": "user",
      "content": "How do I process a payment?"
    }
  ],
  "model": "gpt-3.5-turbo",
  "temperature": 0.7,
  "max_tokens": 1024
}
```

**Response:**
```json
{
  "id": "chatcmpl-abc123",
  "object": "chat.completion",
  "created": 1701820680,
  "model": "gpt-3.5-turbo",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "To process a payment in ChiBank..."
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 25,
    "completion_tokens": 100,
    "total_tokens": 125
  }
}
```

### Text Completion
```http
POST /api/ai/complete
```

**Request Body:**
```json
{
  "prompt": "Generate a payment receipt message for transaction #12345",
  "model": "gpt-3.5-turbo",
  "temperature": 0.5,
  "max_tokens": 512
}
```

### Content Generation
```http
POST /api/ai/generate
```

**Request Body:**
```json
{
  "prompt": "Create a welcome email template",
  "type": "text",
  "model": "gpt-4"
}
```

## Deploying to Vercel

### Prerequisites
- A Vercel account
- GitHub repository connected to Vercel
- AI provider API keys

### Step 1: Install Vercel CLI
```bash
npm install -g vercel
```

### Step 2: Configure Environment Variables
In your Vercel project dashboard:
1. Go to Settings > Environment Variables
2. Add all required API keys:
   - `OPENAI_API_KEY`
   - `ANTHROPIC_API_KEY`
   - `GOOGLE_API_KEY`
   - Database credentials
   - Other app-specific variables

### Step 3: Deploy
```bash
# Link your project
vercel link

# Deploy to production
vercel --prod
```

### Step 4: Verify Deployment
```bash
curl https://your-domain.vercel.app/api/ai/health
```

## Using Vercel AI SDK in Frontend

### Installation
```bash
npm install ai @ai-sdk/openai
```

### Example: React Chat Component
```typescript
import { useChat } from 'ai/react';

export default function Chat() {
  const { messages, input, handleInputChange, handleSubmit } = useChat({
    api: '/api/ai/chat',
  });

  return (
    <div>
      {messages.map(m => (
        <div key={m.id}>
          {m.role}: {m.content}
        </div>
      ))}

      <form onSubmit={handleSubmit}>
        <input
          value={input}
          onChange={handleInputChange}
          placeholder="Say something..."
        />
      </form>
    </div>
  );
}
```

### Example: Text Completion
```typescript
import { experimental_useCompletion as useCompletion } from 'ai/react';

export default function Completion() {
  const { completion, complete } = useCompletion({
    api: '/api/ai/complete',
  });

  return (
    <div>
      <button onClick={() => complete('Generate payment receipt')}>
        Generate Receipt
      </button>
      <div>{completion}</div>
    </div>
  );
}
```

## Integration with ChiBank Features

### 1. Customer Support Chatbot
Use AI chat to provide instant customer support for payment-related queries.

### 2. Transaction Description Generation
Automatically generate human-readable transaction descriptions.

### 3. Fraud Detection Assistance
Use AI to analyze transaction patterns and flag suspicious activities.

### 4. Receipt and Invoice Generation
Generate professional receipts and invoices using AI templates.

### 5. Multilingual Support
Translate payment messages and notifications into multiple languages.

## Best Practices

### 1. API Key Security
- Never commit API keys to version control
- Use Vercel's environment variables feature
- Rotate keys regularly
- Use different keys for development and production

### 2. Rate Limiting
- Implement rate limiting on AI endpoints
- Cache responses when appropriate
- Monitor API usage and costs

### 3. Error Handling
- Implement graceful fallbacks
- Log errors for debugging
- Provide user-friendly error messages

### 4. Cost Optimization
- Use appropriate models for each task
- Implement request caching
- Set reasonable token limits
- Monitor usage patterns

## Troubleshooting

### Issue: "AI model not configured"
**Solution:** Ensure the appropriate API key is set in your environment variables.

### Issue: Rate limit exceeded
**Solution:** Implement request throttling or upgrade your API plan.

### Issue: Slow response times
**Solution:** 
- Use faster models for simple tasks
- Implement caching
- Consider streaming responses

## Resources

- [Vercel AI SDK Documentation](https://sdk.vercel.ai/docs)
- [OpenAI API Documentation](https://platform.openai.com/docs)
- [Anthropic API Documentation](https://docs.anthropic.com/)
- [Google AI Documentation](https://ai.google.dev/)

## Support

For issues related to:
- ChiBank integration: Create a GitHub issue
- Vercel deployment: Contact Vercel support
- AI provider issues: Contact the respective provider

## License

This integration is part of ChiBank and follows the same MIT license.
