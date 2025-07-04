# Quick Installation Guide

## Prerequisites Check ✅
Your system is ready! PHP 8.2.2 and all required extensions are installed.

## Step 1: Install Composer

### On macOS (using Homebrew):
```bash
brew install composer
```

### On macOS (manual installation):
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### On Windows:
1. Download Composer from https://getcomposer.org/download/
2. Run the installer

### On Linux:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Step 2: Install Dependencies
After installing Composer, run:
```bash
composer install
```

## Step 3: Set Up Google Drive API

### 3.1 Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Note your project ID

### 3.2 Enable Google Drive API
1. Navigate to "APIs & Services" > "Library"
2. Search for "Google Drive API"
3. Click on it and press "Enable"

### 3.3 Create Service Account
1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "Service Account"
3. Fill in:
   - Service account name: `wedding-upload`
   - Service account ID: `wedding-upload@your-project-id.iam.gserviceaccount.com`
   - Description: `Service account for wedding upload website`
4. Click "Create and Continue"
5. Skip role assignment (click "Continue")
6. Click "Done"

### 3.4 Generate JSON Key
1. Click on your service account (`wedding-upload`)
2. Go to "Keys" tab
3. Click "Add Key" > "Create New Key"
4. Choose "JSON" format
5. Click "Create"
6. Download the JSON file

### 3.5 Set Up Google Drive Folder
1. Go to [Google Drive](https://drive.google.com)
2. Create a new folder called "Wedding Uploads"
3. Right-click the folder and select "Share"
4. Add your service account email (found in the JSON file) with "Editor" permissions
5. Copy the folder ID from the URL:
   - URL format: `https://drive.google.com/drive/folders/FOLDER_ID_HERE`
   - Copy the long string after `/folders/`

## Step 4: Configure the Application

### 4.1 Place Service Account Key
1. Copy the downloaded JSON file to `credentials/service-account-key.json`
2. Set proper permissions:
   ```bash
   chmod 600 credentials/service-account-key.json
   ```

### 4.2 Update Configuration
1. Open `config.php`
2. Find this line:
   ```php
   define('GOOGLE_DRIVE_FOLDER_ID', ''); // Your Google Drive folder ID where files will be uploaded
   ```
3. Replace the empty string with your folder ID:
   ```php
   define('GOOGLE_DRIVE_FOLDER_ID', 'your-folder-id-here');
   ```

### 4.3 Customize Website (Optional)
Edit these values in `config.php`:
- `SITE_NAME` - Your wedding name
- `SITE_DESCRIPTION` - Custom description
- `UPLOAD_SUCCESS_MESSAGE` - Success message
- `UPLOAD_ERROR_MESSAGE` - Error message

## Step 5: Test the Installation

### 5.1 Run Setup Check
```bash
php setup.php
```

### 5.2 Start Local Server (for testing)
```bash
php -S localhost:8000
```

### 5.3 Access Website
Open your browser and go to: `http://localhost:8000`

## Step 6: Deploy to Production

### Option A: Shared Hosting
1. Upload all files to your web hosting
2. Ensure `uploads/` directory is writable
3. Set `credentials/service-account-key.json` permissions to 600
4. Update `config.php` with your folder ID

### Option B: VPS/Dedicated Server
1. Upload files to `/var/www/html/` or your web directory
2. Set proper permissions:
   ```bash
   chown -R www-data:www-data /var/www/html/
   chmod 755 uploads/
   chmod 600 credentials/service-account-key.json
   ```
3. Configure your web server (Apache/Nginx)

## Troubleshooting

### "Composer not found"
- Install Composer using the instructions above
- Make sure it's in your PATH

### "Google Drive credentials not found"
- Ensure `credentials/service-account-key.json` exists
- Check file permissions (should be 600)

### "Upload failed"
- Check PHP error logs
- Verify Google Drive API is enabled
- Ensure service account has access to the folder

### "File too large"
- Check `MAX_FILE_SIZE` in `config.php`
- Verify server upload limits in `.htaccess`

## Security Notes

- Keep your service account key secure
- Never commit `credentials/` folder to version control
- Use HTTPS in production
- Regularly update dependencies

## Support

If you encounter issues:
1. Run `php setup.php` to check your configuration
2. Check PHP error logs
3. Verify Google Drive API setup
4. Test with smaller files first

---

**Your wedding upload website is ready!** 🎉💒 