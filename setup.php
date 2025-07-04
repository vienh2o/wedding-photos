<?php
/**
 * Setup and System Check Script
 * Run this script to verify your system meets the requirements
 */

echo "=== Wedding Upload Website Setup ===\n\n";

// Check PHP version
echo "1. PHP Version Check:\n";
$phpVersion = phpversion();
echo "   Current PHP version: $phpVersion\n";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "   ✓ PHP version is compatible\n";
} else {
    echo "   ✗ PHP version must be 7.4 or higher\n";
    echo "   Please upgrade your PHP installation\n\n";
}

// Check required extensions
echo "\n2. Required Extensions:\n";
$requiredExtensions = ['curl', 'json', 'openssl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ $ext extension is loaded\n";
    } else {
        echo "   ✗ $ext extension is missing\n";
    }
}

// Check file permissions
echo "\n3. Directory Permissions:\n";
$directories = [
    'uploads' => 'writable',
    'credentials' => 'readable'
];

foreach ($directories as $dir => $permission) {
    if (is_dir($dir)) {
        if ($permission === 'writable' && is_writable($dir)) {
            echo "   ✓ $dir directory is writable\n";
        } elseif ($permission === 'readable' && is_readable($dir)) {
            echo "   ✓ $dir directory is readable\n";
        } else {
            echo "   ✗ $dir directory is not $permission\n";
        }
    } else {
        echo "   ✗ $dir directory does not exist\n";
    }
}

// Check for Composer
echo "\n4. Composer Check:\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✓ Composer dependencies are installed\n";
} else {
    echo "   ✗ Composer dependencies are not installed\n";
    echo "   Please run: composer install\n";
}

// Check for Google credentials
echo "\n5. Google Drive API Setup:\n";
if (file_exists('credentials/service-account-key.json')) {
    echo "   ✓ Google service account key found\n";
} else {
    echo "   ✗ Google service account key not found\n";
    echo "   Please place your service-account-key.json in the credentials folder\n";
}

// Check configuration
echo "\n6. Configuration Check:\n";
if (defined('GOOGLE_DRIVE_FOLDER_ID') && !empty(GOOGLE_DRIVE_FOLDER_ID)) {
    echo "   ✓ Google Drive folder ID is configured\n";
} else {
    echo "   ✗ Google Drive folder ID is not configured\n";
    echo "   Please set GOOGLE_DRIVE_FOLDER_ID in config.php\n";
}

echo "\n=== Setup Complete ===\n\n";

if (!file_exists('vendor/autoload.php')) {
    echo "NEXT STEPS:\n";
    echo "1. Install Composer: https://getcomposer.org/download/\n";
    echo "2. Run: composer install\n";
    echo "3. Set up Google Drive API (see README.md)\n";
    echo "4. Configure config.php with your Google Drive folder ID\n";
    echo "5. Place your service account key in credentials/service-account-key.json\n";
    echo "6. Set proper permissions: chmod 755 uploads/ && chmod 600 credentials/service-account-key.json\n";
}

echo "\nFor detailed instructions, see README.md\n"; 