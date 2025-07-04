<?php
/**
 * Simple test script for gallery functionality
 * This tests the basic features without requiring Composer
 */

echo "=== Gallery Functionality Test ===\n\n";

// Test 1: Check if data directory exists
echo "1. Data Directory Check:\n";
if (is_dir('data')) {
    echo "   ✓ data directory exists\n";
} else {
    echo "   ✗ data directory missing\n";
}

// Test 2: Check if uploads.json exists
echo "\n2. Upload Data File Check:\n";
if (file_exists('data/uploads.json')) {
    echo "   ✓ uploads.json exists\n";
    $data = json_decode(file_get_contents('data/uploads.json'), true);
    if (is_array($data)) {
        echo "   ✓ uploads.json is valid JSON\n";
        echo "   ✓ Found " . count($data) . " upload entries\n";
        
        // Count images vs videos
        $images = array_filter($data, function($item) {
            return $item['is_image'] ?? false;
        });
        $videos = array_filter($data, function($item) {
            return !($item['is_image'] ?? false);
        });
        
        echo "   ✓ Images: " . count($images) . "\n";
        echo "   ✓ Videos: " . count($videos) . "\n";
    } else {
        echo "   ✗ uploads.json is not valid JSON\n";
    }
} else {
    echo "   ✗ uploads.json missing\n";
}

// Test 3: Check if main index.php loads
echo "\n3. Main Page Check:\n";
if (file_exists('index.php')) {
    echo "   ✓ index.php exists\n";
    
    // Check if it includes gallery code
    $content = file_get_contents('index.php');
    if (strpos($content, 'gallery-section') !== false) {
        echo "   ✓ Gallery section found in index.php\n";
    } else {
        echo "   ✗ Gallery section not found in index.php\n";
    }
    
    if (strpos($content, 'updateGallery') !== false) {
        echo "   ✓ AJAX gallery update function found\n";
    } else {
        echo "   ✗ AJAX gallery update function not found\n";
    }
} else {
    echo "   ✗ index.php missing\n";
}

// Test 4: Check if API endpoint exists
echo "\n4. API Endpoint Check:\n";
if (file_exists('api/recent-images.php')) {
    echo "   ✓ recent-images.php exists\n";
} else {
    echo "   ✗ recent-images.php missing\n";
}

// Test 5: Check if UploadTracker class exists
echo "\n5. UploadTracker Class Check:\n";
if (file_exists('src/UploadTracker.php')) {
    echo "   ✓ UploadTracker.php exists\n";
    
    $content = file_get_contents('src/UploadTracker.php');
    if (strpos($content, 'class UploadTracker') !== false) {
        echo "   ✓ UploadTracker class found\n";
    } else {
        echo "   ✗ UploadTracker class not found\n";
    }
    
    if (strpos($content, 'getRecentImages') !== false) {
        echo "   ✓ getRecentImages method found\n";
    } else {
        echo "   ✗ getRecentImages method not found\n";
    }
} else {
    echo "   ✗ UploadTracker.php missing\n";
}

// Test 6: Check if UploadHandler was updated
echo "\n6. UploadHandler Integration Check:\n";
if (file_exists('src/UploadHandler.php')) {
    echo "   ✓ UploadHandler.php exists\n";
    
    $content = file_get_contents('src/UploadHandler.php');
    if (strpos($content, 'UploadTracker') !== false) {
        echo "   ✓ UploadTracker integration found\n";
    } else {
        echo "   ✗ UploadTracker integration not found\n";
    }
} else {
    echo "   ✗ UploadHandler.php missing\n";
}

// Test 7: Check security files
echo "\n7. Security Configuration Check:\n";
if (file_exists('.htaccess')) {
    echo "   ✓ .htaccess exists\n";
    
    $content = file_get_contents('.htaccess');
    if (strpos($content, 'data') !== false) {
        echo "   ✓ Data directory protection found\n";
    } else {
        echo "   ✗ Data directory protection not found\n";
    }
} else {
    echo "   ✗ .htaccess missing\n";
}

if (file_exists('.gitignore')) {
    echo "   ✓ .gitignore exists\n";
    
    $content = file_get_contents('.gitignore');
    if (strpos($content, '/data/') !== false) {
        echo "   ✓ Data directory excluded from git\n";
    } else {
        echo "   ✗ Data directory not excluded from git\n";
    }
} else {
    echo "   ✗ .gitignore missing\n";
}

echo "\n=== Test Complete ===\n\n";

echo "To test the full functionality:\n";
echo "1. Install Composer: composer install\n";
echo "2. Set up Google Drive API (see INSTALL.md)\n";
echo "3. Start server: php -S localhost:8000\n";
echo "4. Visit: http://localhost:8000\n";
echo "5. Upload some photos to see the gallery in action!\n"; 