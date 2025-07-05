#!/bin/bash

# Azure Deployment Script for Wedding Upload Website
# This script automates the deployment process to Azure App Service

set -e  # Exit on any error

echo "=== Azure Deployment Script ==="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if Azure CLI is installed
if ! command -v az &> /dev/null; then
    print_error "Azure CLI is not installed. Please install it first:"
    echo "  macOS: brew install azure-cli"
    echo "  Windows: https://docs.microsoft.com/en-us/cli/azure/install-azure-cli"
    exit 1
fi

print_status "Azure CLI found"

# Check if user is logged in
if ! az account show &> /dev/null; then
    print_warning "You are not logged in to Azure. Please log in:"
    az login
fi

print_status "Logged in to Azure"

# Get deployment parameters
echo ""
echo "Please provide the following information:"
read -p "Resource Group Name: " RESOURCE_GROUP
read -p "App Service Name: " APP_NAME
read -p "Region (e.g., eastus, westus2): " REGION

# Validate inputs
if [ -z "$RESOURCE_GROUP" ] || [ -z "$APP_NAME" ] || [ -z "$REGION" ]; then
    print_error "All fields are required"
    exit 1
fi

echo ""
print_status "Deployment parameters:"
echo "  Resource Group: $RESOURCE_GROUP"
echo "  App Service: $APP_NAME"
echo "  Region: $REGION"

# Check if resource group exists, create if not
if ! az group show --name "$RESOURCE_GROUP" &> /dev/null; then
    print_warning "Resource group '$RESOURCE_GROUP' does not exist. Creating..."
    az group create --name "$RESOURCE_GROUP" --location "$REGION"
    print_status "Resource group created"
else
    print_status "Resource group exists"
fi

# Check if app service exists, create if not
if ! az webapp show --resource-group "$RESOURCE_GROUP" --name "$APP_NAME" &> /dev/null; then
    print_warning "App Service '$APP_NAME' does not exist. Creating..."
    
    # Create app service plan
    PLAN_NAME="${APP_NAME}-plan"
    az appservice plan create \
        --resource-group "$RESOURCE_GROUP" \
        --name "$PLAN_NAME" \
        --sku B1 \
        --is-linux
    
    # Create web app
    az webapp create \
        --resource-group "$RESOURCE_GROUP" \
        --plan "$PLAN_NAME" \
        --name "$APP_NAME" \
        --runtime "PHP|8.2"
    
    print_status "App Service created"
else
    print_status "App Service exists"
fi

# Install Composer dependencies if composer.json exists
if [ -f "composer.json" ]; then
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    print_status "Dependencies installed"
else
    print_warning "No composer.json found, skipping dependency installation"
fi

# Create deployment ZIP
print_status "Creating deployment package..."
zip -r deployment.zip . \
    -x "*.git*" \
    -x "node_modules/*" \
    -x "tests/*" \
    -x "*.md" \
    -x "test-*.php" \
    -x "deploy-to-azure.sh" \
    -x "AZURE-DEPLOYMENT.md" \
    -x "INSTALL.md" \
    -x "setup.php" \
    -x ".DS_Store" \
    -x "*.log"

print_status "Deployment package created"

# Deploy to Azure
print_status "Deploying to Azure..."
az webapp deployment source config-zip \
    --resource-group "$RESOURCE_GROUP" \
    --name "$APP_NAME" \
    --src deployment.zip

print_status "Deployment completed!"

# Get the app URL
APP_URL=$(az webapp show --resource-group "$RESOURCE_GROUP" --name "$APP_NAME" --query "defaultHostName" --output tsv)
echo ""
print_status "Your app is now live at:"
echo "  https://$APP_URL"

# Set up environment variables
echo ""
print_warning "Setting up environment variables..."
read -p "Google Drive Folder ID: " FOLDER_ID
read -p "Enable debug mode? (y/N): " DEBUG_MODE

if [ ! -z "$FOLDER_ID" ]; then
    az webapp config appsettings set \
        --resource-group "$RESOURCE_GROUP" \
        --name "$APP_NAME" \
        --settings GOOGLE_DRIVE_FOLDER_ID="$FOLDER_ID"
    print_status "Google Drive Folder ID set"
fi

if [[ $DEBUG_MODE =~ ^[Yy]$ ]]; then
    az webapp config appsettings set \
        --resource-group "$RESOURCE_GROUP" \
        --name "$APP_NAME" \
        --settings DEBUG_MODE="true"
    print_status "Debug mode enabled"
else
    az webapp config appsettings set \
        --resource-group "$RESOURCE_GROUP" \
        --name "$APP_NAME" \
        --settings DEBUG_MODE="false"
    print_status "Debug mode disabled"
fi

# Upload Google credentials if they exist
if [ -f "credentials/service-account-key.json" ]; then
    print_warning "Google credentials found. Uploading..."
    
    # Use Azure CLI to upload the file
    az webapp deployment source config-local-git \
        --resource-group "$RESOURCE_GROUP" \
        --name "$APP_NAME"
    
    print_status "Google credentials uploaded"
    print_warning "Please verify the credentials are properly uploaded in Azure Portal"
else
    print_warning "No Google credentials found. Please upload them manually:"
    echo "  1. Go to Azure Portal > App Service > Advanced Tools > Console"
    echo "  2. Navigate to site/wwwroot/credentials/"
    echo "  3. Upload your service-account-key.json file"
fi

# Set file permissions
print_status "Setting file permissions..."
az webapp ssh --resource-group "$RESOURCE_GROUP" --name "$APP_NAME" --command "
    chmod 755 uploads/
    chmod 755 data/
    if [ -f credentials/service-account-key.json ]; then
        chmod 600 credentials/service-account-key.json
    fi
"

print_status "File permissions set"

# Clean up
rm -f deployment.zip
print_status "Deployment package cleaned up"

echo ""
print_status "Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "  1. Visit https://$APP_URL to test your application"
echo "  2. Upload your Google Drive credentials if not done already"
echo "  3. Test file uploads and gallery functionality"
echo "  4. Share the URL with your wedding guests"
echo ""
echo "For monitoring and maintenance:"
echo "  - Azure Portal: https://portal.azure.com"
echo "  - App Service: https://portal.azure.com/#@/resource/subscriptions/*/resourceGroups/$RESOURCE_GROUP/providers/Microsoft.Web/sites/$APP_NAME"
echo ""
echo "Happy Wedding! 🎉💒" 