# Wedding Photo & Video Upload Website

A beautiful, modern PHP website that allows anonymous photo and video uploads directly to Google Drive for your wedding celebration.

## Features

- 🎨 **Beautiful Modern UI** - Responsive design with drag-and-drop functionality
- 📱 **Mobile Friendly** - Works perfectly on all devices
- 🔒 **Secure Uploads** - File validation and secure processing
- ☁️ **Google Drive Integration** - Direct upload to your Google Drive folder
- 📸 **Multiple File Types** - Supports images (JPEG, PNG, GIF, WebP) and videos (MP4, AVI, MOV, WMV, FLV, WebM)
- 📁 **Multiple File Upload** - Upload multiple files at once with drag-and-drop support
- 📊 **Progress Tracking** - Real-time upload progress
- 🖼️ **Photo Gallery** - View the last 20 uploaded photos
- 📈 **Upload Statistics** - See total photos, videos, and uploads
- 🚀 **Fast & Efficient** - Optimized for performance

## Prerequisites

- PHP 7.4 or higher
- Composer
- Web server (Apache/Nginx)
- Google Cloud Platform account

## Installation

### 1. Clone or Download the Project

```bash
git clone <repository-url>
cd wedding-upload-website
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Set Up Google Drive API

1. **Create a Google Cloud Project:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select an existing one

2. **Enable Google Drive API:**
   - Navigate to "APIs & Services" > "Library"
   - Search for "Google Drive API" and enable it

3. **Create Service Account:**
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "Service Account"
   - Fill in the service account details
   - Click "Create and Continue"

4. **Generate JSON Key:**
   - Click on your service account
   - Go to "Keys" tab
   - Click "Add Key" > "Create New Key"
   - Choose JSON format
   - Download the key file

5. **Set Up Google Drive Folder:**
   - Create a folder in your Google Drive where files will be uploaded
   - Right-click the folder and select "Share"
   - Add your service account email (found in the JSON key file) with "Editor" permissions
   - Copy the folder ID from the URL (the long string after `/folders/`)

### 4. Configure the Application

1. **Create credentials folder:**
   ```bash
   mkdir credentials
   ```

2. **Place your service account key:**
   - Copy the downloaded JSON key file to `credentials/service-account-key.json`

3. **Update configuration:**
   - Open `config.php`
   - Set your Google Drive folder ID:
     ```php
     define('GOOGLE_DRIVE_FOLDER_ID', 'your-folder-id-here');
     ```
   - Customize other settings as needed

### 5. Set Permissions

```bash
chmod 755 uploads/
chmod 600 credentials/service-account-key.json
```

### 6. Configure Web Server

#### Apache Configuration
The `.htaccess` file is already included with optimal settings.

#### Nginx Configuration (if using Nginx)
Add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Increase upload limits
client_max_body_size 100M;
```

## Usage

1. **Access the Website:**
   - Open your web browser
   - Navigate to your website URL

2. **Upload Files:**
   - Drag and drop files onto the upload area, or click to browse
   - Select multiple files (images and videos) - up to 20 files at once
   - View selected files list with file sizes
   - Click "Upload Files" to upload all files simultaneously
   - Watch the progress as files upload to Google Drive

3. **View Uploaded Files:**
   - All uploaded files will appear in your configured Google Drive folder
   - Files are automatically renamed with timestamps for organization

## Configuration Options

Edit `config.php` to customize:

- **File Size Limits:** `MAX_FILE_SIZE` (default: 100MB)
- **Allowed File Types:** `ALLOWED_IMAGE_TYPES` and `ALLOWED_VIDEO_TYPES`
- **Website Branding:** `SITE_NAME` and `SITE_DESCRIPTION`
- **Messages:** Success and error messages
- **Security:** CSRF protection and session timeout
- **Debug Mode:** Error reporting settings

## Security Features

- ✅ File type validation
- ✅ File size limits
- ✅ Secure file handling
- ✅ CSRF protection
- ✅ Input sanitization
- ✅ Secure headers
- ✅ Protected sensitive directories

## File Structure

```
wedding-upload-website/
├── composer.json              # PHP dependencies
├── config.php                 # Configuration settings
├── index.php                  # Main upload interface
├── upload.php                 # Upload processing script
├── .htaccess                  # Apache configuration
├── README.md                  # This file
├── src/
│   ├── GoogleDriveService.php # Google Drive integration
│   ├── UploadHandler.php      # File upload processing
│   └── UploadTracker.php      # Upload tracking and gallery
├── api/
│   └── recent-images.php      # API for recent images
├── credentials/               # Google API credentials (create this)
│   └── service-account-key.json
├── uploads/                   # Temporary upload directory (auto-created)
├── data/                      # Upload tracking data (auto-created)
└── vendor/                    # Composer dependencies (auto-created)
```

## Troubleshooting

### Common Issues

1. **"Google Drive credentials not found"**
   - Ensure `credentials/service-account-key.json` exists
   - Check file permissions (should be 600)

2. **"Google Drive folder ID not configured"**
   - Set `GOOGLE_DRIVE_FOLDER_ID` in `config.php`
   - Make sure the service account has access to the folder

3. **"Upload failed"**
   - Check PHP error logs
   - Verify file size limits in `.htaccess`
   - Ensure uploads directory is writable

4. **"File type not allowed"**
   - Check `ALLOWED_IMAGE_TYPES` and `ALLOWED_VIDEO_TYPES` in `config.php`
   - Verify file MIME type detection

### Debug Mode

Enable debug mode in `config.php` to see detailed error messages:

```php
define('DEBUG_MODE', true);
```

## Customization

### Styling
The website uses inline CSS for easy customization. Edit the `<style>` section in `index.php` to modify:
- Colors and gradients
- Fonts and typography
- Layout and spacing
- Responsive breakpoints

### Branding
Update these constants in `config.php`:
- `SITE_NAME` - Your wedding name
- `SITE_DESCRIPTION` - Custom description
- `UPLOAD_SUCCESS_MESSAGE` - Success message
- `UPLOAD_ERROR_MESSAGE` - Error message

## Support

For issues and questions:
1. Check the troubleshooting section above
2. Review PHP error logs
3. Verify Google Drive API setup
4. Test with smaller files first

## License

This project is open source and available under the MIT License.

---

**Happy Wedding!** 🎉💒 