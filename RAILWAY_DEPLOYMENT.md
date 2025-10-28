# Railway Deployment Guide

This guide will help you deploy the Ticket Management System to Railway.

## Fixed Issues

The following issues have been resolved to fix the 502 error on Railway:

1. **Procfile Configuration**: Simplified to avoid Nixpacks build issues
2. **Database Storage**: Modified to use Railway's ephemeral filesystem (`/tmp`)
3. **Session Configuration**: Added proper session settings for Railway environment
4. **Error Handling**: Enhanced error reporting and debugging
5. **Health Checks**: Added Railway-specific configuration
6. **Nixpacks Issues**: Removed complex build process to avoid derivation errors

## Files Modified/Created

- `Procfile` - Simplified to direct PHP commands
- `railway.toml` - Railway configuration without Nixpacks
- `config/database.php` - Modified for Railway filesystem
- `index.php` - Enhanced error handling and session config
- `setup.php` - Updated for Railway environment detection
- `.htaccess` - Apache configuration for proper routing

## Deployment Steps

1. **Push to GitHub** (if not already done):
   ```bash
   git add .
   git commit -m "Fix Railway deployment issues"
   git push origin main
   ```

2. **Deploy to Railway**:
   - Go to [railway.app](https://railway.app)
   - Click "New Project"
   - Connect your GitHub repository
   - Select the ticket management repository
   - Railway will automatically detect the PHP application

3. **Environment Variables** (if needed):
   - Railway automatically sets the `PORT` environment variable
   - The application detects Railway environment automatically

4. **Deployment Verification**:
   - Railway will build and deploy the application
   - The startup script will automatically initialize the database
   - Health checks ensure the application is running properly

## Troubleshooting

If you still encounter issues:

1. **Check Railway Logs**: View the deployment logs in Railway dashboard
2. **Verify Database**: The database is automatically created in `/tmp/data.json`
3. **Check Permissions**: The startup script sets proper permissions
4. **Health Check**: The application responds to `/` for health checks

## Application Features

- User authentication (Admin, Agent, User roles)
- Ticket management system
- SQLite database (JSON file-based for Railway compatibility)
- Twig templating engine
- Responsive design

## Default Login Credentials

- Admin: john@example.com / password
- Agent: jane@example.com / password
- User: tom@example.com / password

## Technical Details

- **PHP Version**: 8.0+
- **Framework**: Custom PHP with Twig
- **Database**: JSON file-based (SQLite alternative)
- **Server**: PHP built-in server
- **Deployment**: Railway with Nixpacks builder

The application is now fully configured for Railway deployment and should resolve the 502 error issues.