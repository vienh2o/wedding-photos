<?php
/**
 * Test script to verify upload functionality
 */

echo "=== Upload Functionality Test ===\n\n";

// Test 1: Check if fallback upload handler exists
echo "1. Upload Handler Check:\n";
if (file_exists('upload-fallback.php')) {
    echo "   ✓ upload-fallback.php exists\n";
} else {
    echo "   ✗ upload-fallback.php missing\n";
}

// Test 2: Check if UploadTracker can be loaded
echo "\n2. UploadTracker Loading Test:\n";
try {
    require_once 'config.php';
    require_once 'src/UploadTracker.php';
    $tracker = new \WeddingUpload\UploadTracker();
    echo "   ✓ UploadTracker loaded successfully\n";
} catch (Exception $e) {
    echo "   ✗ Failed to load UploadTracker: " . $e->getMessage() . "\n";
}

// Test 3: Check uploads directory
echo "\n3. Uploads Directory Check:\n";
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir)) {
    echo "   ✓ uploads directory exists\n";
    if (is_writable($uploadDir)) {
        echo "   ✓ uploads directory is writable\n";
    } else {
        echo "   ✗ uploads directory is not writable\n";
    }
} else {
    echo "   ✗ uploads directory missing\n";
}

// Test 4: Check Google Drive configuration
echo "\n4. Google Drive Configuration Check:\n";
$googleDriveConfigured = !empty(GOOGLE_DRIVE_FOLDER_ID) && file_exists(GOOGLE_APPLICATION_CREDENTIALS);
if ($googleDriveConfigured) {
    echo "   ✓ Google Drive API is configured\n";
    echo "   ✓ Will use Google Drive for uploads\n";
} else {
    echo "   ⚠ Google Drive API not configured\n";
    echo "   ✓ Will use local storage fallback\n";
}

// Test 5: Check file size limits
echo "\n5. File Size Limits Check:\n";
echo "   ✓ Maximum file size: " . formatBytes(MAX_FILE_SIZE) . "\n";
echo "   ✓ Allowed image types: " . implode(', ', ALLOWED_IMAGE_TYPES) . "\n";
echo "   ✓ Allowed video types: " . implode(', ', ALLOWED_VIDEO_TYPES) . "\n";

// Test 6: Check API endpoint
echo "\n6. API Endpoint Check:\n";
if (file_exists('api/recent-images.php')) {
    echo "   ✓ recent-images.php exists\n";
    
    // Test if it can be loaded
    try {
        ob_start();
        include 'api/recent-images.php';
        $output = ob_get_clean();
        echo "   ✓ API endpoint loads without errors\n";
    } catch (Exception $e) {
        echo "   ✗ API endpoint has errors: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ recent-images.php missing\n";
}

// Test 7: Check current upload data
echo "\n7. Current Upload Data Check:\n";
try {
    $tracker = new \WeddingUpload\UploadTracker();
    $stats = $tracker->getUploadStats();
    echo "   ✓ Total uploads: " . $stats['total_uploads'] . "\n";
    echo "   ✓ Total images: " . $stats['total_images'] . "\n";
    echo "   ✓ Total videos: " . $stats['total_videos'] . "\n";
} catch (Exception $e) {
    echo "   ✗ Failed to get upload stats: " . $e->getMessage() . "\n";
}

echo "\n=== Upload Test Complete ===\n\n";

echo "To test actual uploads:\n";
echo "1. Start server: php -S localhost:8000\n";
echo "2. Visit: http://localhost:8000\n";
echo "3. Try uploading a photo or video\n";
echo "4. Check the gallery to see if it appears\n\n";

echo "Expected behavior:\n";
if ($googleDriveConfigured) {
    echo "- Files will be uploaded to Google Drive\n";
    echo "- Gallery will show Google Drive thumbnails\n";
} else {
    echo "- Files will be stored locally in uploads/ directory\n";
    echo "- Gallery will show placeholder thumbnails\n";
    echo "- All uploads will be tracked in data/uploads.json\n";
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
} 