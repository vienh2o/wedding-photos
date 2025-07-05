# Azure Web Services Deployment Guide

This guide will walk you through deploying your wedding upload website to Azure Web Services.

## Prerequisites

- Azure account (free tier available)
- Google Cloud Platform account (for Google Drive API)
- Basic knowledge of Azure portal

## Method 1: Azure App Service (Recommended)

### Step 1: Prepare Your Application

1. **Install Composer Dependencies** (if you want to use Google Drive API):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Configure Google Drive API**:
   - Follow the setup instructions in `INSTALL.md`
   - Place your `service-account-key.json` in the `credentials/` folder
   - Update `config.php` with your Google Drive folder ID

3. **Set Production Configuration**:
   ```php
   // In config.php, change:
   define('DEBUG_MODE', false);
   ```

### Step 2: Create Azure App Service

1. **Login to Azure Portal**:
   - Go to [portal.azure.com](https://portal.azure.com)
   - Sign in with your Azure account

2. **Create App Service**:
   - Click "Create a resource"
   - Search for "Web App"
   - Click "Create"

3. **Configure App Service**:
   - **Subscription**: Choose your subscription
   - **Resource Group**: Create new or use existing
   - **Name**: `wedding-upload-website` (or your preferred name)
   - **Publish**: Code
   - **Runtime stack**: PHP 8.2
   - **Operating System**: Linux (recommended)
   - **Region**: Choose closest to your users
   - **App Service Plan**: 
     - **Sku and size**: Basic B1 (or Free F1 for testing)
   - Click "Review + create" then "Create"

### Step 3: Deploy Your Code

#### Option A: Using Azure CLI (Recommended)

1. **Install Azure CLI**:
   ```bash
   # macOS
   brew install azure-cli
   
   # Windows
   # Download from https://docs.microsoft.com/en-us/cli/azure/install-azure-cli
   ```

2. **Login to Azure**:
   ```bash
   az login
   ```

3. **Deploy using ZIP**:
   ```bash
   # Create deployment ZIP (exclude unnecessary files)
   zip -r deployment.zip . -x "*.git*" "node_modules/*" "tests/*" "*.md" "test-*.php"
   
   # Deploy to Azure
   az webapp deployment source config-zip \
     --resource-group YOUR_RESOURCE_GROUP \
     --name YOUR_APP_NAME \
     --src deployment.zip
   ```

#### Option B: Using GitHub Actions (Advanced)

1. **Create GitHub Repository**:
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/YOUR_USERNAME/wedding-upload.git
   git push -u origin main
   ```

2. **Create GitHub Action**:
   Create `.github/workflows/azure-deploy.yml`:
   ```yaml
   name: Deploy to Azure
   on:
     push:
       branches: [ main ]
   
   jobs:
     deploy:
       runs-on: ubuntu-latest
       steps:
       - uses: actions/checkout@v2
       
       - name: Setup PHP
         uses: shivammathur/setup-php@v2
         with:
           php-version: '8.2'
       
       - name: Install Composer Dependencies
         run: composer install --no-dev --optimize-autoloader
       
       - name: Deploy to Azure
         uses: azure/webapps-deploy@v2
         with:
           app-name: 'YOUR_APP_NAME'
           publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
   ```

3. **Configure Azure Publish Profile**:
   - In Azure Portal, go to your App Service
   - Click "Get publish profile"
   - Copy the content
   - In GitHub, go to Settings > Secrets
   - Add new secret: `AZURE_WEBAPP_PUBLISH_PROFILE`
   - Paste the publish profile content

#### Option C: Using Azure Portal (Simple)

1. **Upload Files**:
   - In Azure Portal, go to your App Service
   - Click "Advanced Tools" > "Go"
   - Click "Console"
   - Navigate to `site/wwwroot`
   - Upload your files using the file manager

### Step 4: Configure Application Settings

1. **Set Environment Variables**:
   - In Azure Portal, go to your App Service
   - Click "Configuration" > "Application settings"
   - Add these settings:
     ```
     GOOGLE_DRIVE_FOLDER_ID = your-folder-id
     DEBUG_MODE = false
     ```

2. **Upload Google Credentials**:
   - Use Azure Portal's file manager
   - Upload `credentials/service-account-key.json`
   - Or use Azure Key Vault for better security

3. **Set File Permissions**:
   - In Azure Portal Console:
   ```bash
   chmod 755 uploads/
   chmod 600 credentials/service-account-key.json
   chmod 755 data/
   ```

### Step 5: Configure Custom Domain (Optional)

1. **Add Custom Domain**:
   - In Azure Portal, go to your App Service
   - Click "Custom domains"
   - Add your domain (e.g., `wedding.yourdomain.com`)

2. **Configure DNS**:
   - Add CNAME record pointing to your Azure app
   - Or use Azure DNS if you have it

## Method 2: Azure Static Web Apps (Alternative)

If you want to separate frontend and backend:

1. **Create Static Web App**:
   - In Azure Portal, create "Static Web App"
   - Connect to your GitHub repository
   - Configure build settings

2. **Use Azure Functions for Backend**:
   - Create Azure Functions for upload processing
   - Deploy API endpoints separately

## Method 3: Azure Container Instances

For more control:

1. **Create Dockerfile**:
   ```dockerfile
   FROM php:8.2-apache
   
   # Install extensions
   RUN docker-php-ext-install curl json openssl mbstring
   
   # Install Composer
   COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
   
   # Copy application
   COPY . /var/www/html/
   
   # Install dependencies
   RUN composer install --no-dev --optimize-autoloader
   
   # Set permissions
   RUN chmod 755 uploads/ data/
   RUN chmod 600 credentials/service-account-key.json
   
   # Configure Apache
   COPY .htaccess /var/www/html/
   
   EXPOSE 80
   ```

2. **Deploy Container**:
   ```bash
   az container create \
     --resource-group YOUR_RESOURCE_GROUP \
     --name wedding-upload \
     --image YOUR_REGISTRY/wedding-upload:latest \
     --dns-name-label wedding-upload \
     --ports 80
   ```

## Configuration for Production

### 1. Update config.php for Production

```php
// Production settings
define('DEBUG_MODE', false);
define('ENABLE_CSRF_PROTECTION', true);

// Use environment variables
define('GOOGLE_DRIVE_FOLDER_ID', $_ENV['GOOGLE_DRIVE_FOLDER_ID'] ?? '');
```

### 2. Security Considerations

1. **Use Azure Key Vault**:
   - Store Google API credentials in Azure Key Vault
   - Access them securely in your application

2. **Enable HTTPS**:
   - Azure App Service provides free SSL certificates
   - Force HTTPS redirect in `.htaccess`

3. **Set Proper Permissions**:
   ```bash
   chmod 755 uploads/
   chmod 600 credentials/service-account-key.json
   chmod 755 data/
   ```

### 3. Performance Optimization

1. **Enable Caching**:
   - Use Azure CDN for static assets
   - Configure browser caching in `.htaccess`

2. **Database Option** (for larger scale):
   - Consider Azure Database for MySQL
   - Replace JSON storage with database

## Monitoring and Maintenance

### 1. Set Up Monitoring

1. **Application Insights**:
   - Enable Application Insights in Azure Portal
   - Monitor performance and errors

2. **Log Analytics**:
   - View application logs in Azure Portal
   - Set up alerts for errors

### 2. Backup Strategy

1. **Google Drive Backup**:
   - All uploaded files are automatically backed up to Google Drive
   - Consider additional backup for upload tracking data

2. **Azure Backup**:
   - Enable Azure Backup for your App Service
   - Regular backups of application data

## Troubleshooting

### Common Issues

1. **"Class not found" errors**:
   - Ensure Composer dependencies are installed
   - Check file permissions

2. **Upload failures**:
   - Verify Google Drive API credentials
   - Check file size limits in Azure

3. **Gallery not showing**:
   - Ensure `data/` directory is writable
   - Check upload tracking data

### Getting Help

1. **Azure Support**:
   - Use Azure Portal's built-in diagnostics
   - Check Azure status page

2. **Application Logs**:
   - View logs in Azure Portal > App Service > Log stream
   - Enable detailed error logging

## Cost Optimization

### Free Tier Options

1. **Azure App Service Free Tier**:
   - 1 GB RAM, 1 GB storage
   - 60 minutes/day compute time
   - Perfect for testing and small weddings

2. **Azure Functions Consumption Plan**:
   - Pay only for execution time
   - Good for sporadic uploads

### Scaling Options

1. **Basic Plan** ($13/month):
   - 1.75 GB RAM, 10 GB storage
   - Unlimited compute time
   - Custom domain support

2. **Standard Plan** ($73/month):
   - Auto-scaling capabilities
   - Better performance
   - Multiple instances

## Next Steps

1. **Test Your Deployment**:
   - Upload test photos and videos
   - Verify gallery functionality
   - Test on mobile devices

2. **Share with Guests**:
   - Send the URL to your wedding guests
   - Consider adding password protection if needed

3. **Monitor Usage**:
   - Track upload statistics
   - Monitor storage usage
   - Set up alerts for high usage

---

**Your wedding upload website is now live on Azure!** 🎉💒

For additional help, refer to:
- [Azure App Service Documentation](https://docs.microsoft.com/en-us/azure/app-service/)
- [Azure PHP Documentation](https://docs.microsoft.com/en-us/azure/app-service/configure-language-php)
- [Google Drive API Documentation](https://developers.google.com/drive/api) 