# Materialize v13.11.0 Admin Template

This directory contains the Materialize v13.11.0 Material Design Admin Template integration for QRPay Web.

## About Materialize v13.11.0

Materialize v13.11.0 is a comprehensive Material Design Admin Template that includes:

- **Modern Framework Support**: Next.js v13, Vue 3, Laravel, Django, ASP.NET
- **UI Components**: Built with MUI Core v5 (Material UI for React)
- **Architecture**: 100% React hooks & functional components
- **State Management**: Redux Toolkit & React Context API
- **Authentication**: JWT-based authentication and access control
- **Internationalization**: Multi-lingual and RTL (Right-to-Left) support
- **Apps & Charts**: Includes 5 apps and 3 chart libraries
- **Responsive Design**: Fully responsive layout with unlimited color options

## Features

- ✅ Built with Next.js v13 and Vue 3
- ✅ TypeScript support
- ✅ Code splitting and lazy loading
- ✅ ESLint & Prettier for code quality
- ✅ User authentication via JWT
- ✅ Role-based access control
- ✅ Multiple theme options
- ✅ Modern development workflow

## Directory Structure

The template files should be organized as follows:

```
materialize_v13.11.0/
├── README.md                 # This file
├── package.json              # Node.js dependencies
├── next.config.js            # Next.js configuration (if using Next.js)
├── src/                      # Source files
│   ├── components/           # React/Vue components
│   ├── pages/                # Page components
│   ├── styles/               # CSS/SCSS styles
│   ├── utils/                # Utility functions
│   └── configs/              # Configuration files
├── public/                   # Static assets
└── docs/                     # Documentation
```

## Installation

To install and use this template:

1. **Install Dependencies**
   ```bash
   cd qrpay-web/materialize_v13.11.0
   npm install
   # or
   yarn install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Run Development Server**
   ```bash
   npm run dev
   # or
   yarn dev
   ```

4. **Build for Production**
   ```bash
   npm run build
   # or
   yarn build
   ```

## Integration with QRPay

This template is designed to work alongside the existing QRPay system:

- **Admin Panel**: Enhanced admin interface with Material Design
- **Dashboard**: Real-time statistics and transaction monitoring
- **User Management**: Advanced user and merchant management
- **API Integration**: Seamless integration with QRPay backend APIs

## Configuration

Key configuration files to customize:

1. **Environment Variables** (`.env`)
   - API endpoints
   - Authentication keys
   - Database connections

2. **Theme Configuration** (`src/configs/theme.js`)
   - Primary/secondary colors
   - Typography
   - Layout preferences

3. **API Configuration** (`src/configs/api.js`)
   - Base URLs
   - Timeout settings
   - Request interceptors

## Usage with Existing Admin

The existing admin panel at `/admin/index.html` uses Materialize CSS v1.0.0 from CDN. This template provides:

- Enhanced UI components
- Better state management
- Modern development workflow
- Improved performance

You can:
- Run both admin interfaces side-by-side
- Gradually migrate features from the old admin to the new one
- Use the new template for new features

## Development

### Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run start` - Start production server
- `npm run lint` - Run ESLint
- `npm run test` - Run tests

### Code Style

The project follows standard React/Vue best practices:
- Functional components with hooks
- TypeScript for type safety
- ESLint and Prettier for code formatting

## Documentation

For detailed documentation on Materialize v13.11.0 template:

- Official Documentation: Check the vendor documentation
- Component Gallery: Browse available UI components
- API Reference: Backend integration guides

## Support

For issues or questions:

1. Check the existing documentation
2. Review the component examples
3. Consult the QRPay development team

## Version

- **Template Version**: 13.11.0
- **QRPay Version**: Check main project package.json or composer.json
- **Last Updated**: See git commit history

## Notes

- This template requires Node.js 16+ and npm/yarn
- Make sure to configure CORS properly for API calls
- Test thoroughly before deploying to production
- Keep dependencies up to date for security

## License

This template integration is part of the QRPay project. Please refer to the main project LICENSE file for details.
