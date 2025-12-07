# Configs Directory

This directory contains configuration files for the application.

## Structure

```
configs/
├── api.ts           # API configuration
├── theme.ts         # Theme configuration
├── routes.ts        # Route definitions
└── constants.ts     # App-wide constants
```

## Usage

Import configurations as needed:

```typescript
import { API_CONFIG } from '@/configs/api';
import { THEME_CONFIG } from '@/configs/theme';
```

## Environment

Use environment variables from `.env` for sensitive data.
