<?php
/**
 * Configuration file for Wedding Upload Website
 * 
 * IMPORTANT: You need to set up Google Drive API credentials:
 * 1. Go to https://console.developers.google.com/
 * 2. Create a new project or select existing one
 * 3. Enable Google Drive API
 * 4. Create Service Account credentials
 * 5. Download the JSON key file
 * 6. Place it in the 'credentials' folder as 'service-account-key.json'
 */

// Google Drive API Configuration
define('GOOGLE_APPLICATION_CREDENTIALS', __DIR__ . '/credentials/service-account-key.json');
define('GOOGLE_DRIVE_FOLDER_ID', '19nGhLZpltbQ7OltoidhhagpfB4Oy51Ax'); // Your Google Drive folder ID where files will be uploaded

// Upload Configuration
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
// define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm']); // No longer used, only photos allowed

// Website Configuration
define('SITE_NAME', 'Vien & Thao Guests Photos');
define('SITE_DESCRIPTION', 'Share your photos from our wedding day! Up to 20 images per upload.');
define('UPLOAD_SUCCESS_MESSAGE', 'Thank you! Your file has been uploaded successfully.');
define('UPLOAD_ERROR_MESSAGE', 'Sorry, there was an error uploading your file. Please try again.');

// Security Configuration
define('ENABLE_CSRF_PROTECTION', true);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Never display errors for API responses
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
} 