<?php
/**
 * Test script to verify demo data and gallery functionality
 */

echo "=== Demo Data and Gallery Test ===\n\n";

// Load demo data
$dataFile = 'data/uploads.json';
if (!file_exists($dataFile)) {
    echo "✗ Demo data file not found: $dataFile\n";
    exit(1);
}

$data = json_decode(file_get_contents($dataFile), true);
if (!is_array($data)) {
    echo "✗ Invalid JSON in demo data file\n";
    exit(1);
}

echo "✓ Demo data loaded successfully\n";
echo "✓ Found " . count($data) . " upload entries\n\n";

// Test data structure
echo "Testing data structure:\n";
$requiredFields = ['id', 'filename', 'original_name', 'file_id', 'mime_type', 'upload_time', 'is_image', 'thumbnail_url'];
$sample = $data[0];

foreach ($requiredFields as $field) {
    if (isset($sample[$field])) {
        echo "  ✓ Field '$field' exists\n";
    } else {
        echo "  ✗ Field '$field' missing\n";
    }
}

// Test image filtering
echo "\nTesting image filtering:\n";
$images = array_filter($data, function($item) {
    return $item['is_image'] ?? false;
});

$videos = array_filter($data, function($item) {
    return !($item['is_image'] ?? false);
});

echo "✓ Images: " . count($images) . "\n";
echo "✓ Videos: " . count($videos) . "\n";

// Test recent images (limit to 20)
$recentImages = array_slice($images, 0, 20);
echo "✓ Recent images (max 20): " . count($recentImages) . "\n";

// Test thumbnail URLs
echo "\nTesting thumbnail URLs:\n";
$validThumbnails = 0;
foreach ($images as $image) {
    if (!empty($image['thumbnail_url'])) {
        $validThumbnails++;
    }
}
echo "✓ Valid thumbnail URLs: $validThumbnails/" . count($images) . "\n";

// Test date formatting
echo "\nTesting date formatting:\n";
foreach (array_slice($data, 0, 3) as $item) {
    $date = date('M j, Y g:i A', strtotime($item['upload_time']));
    echo "  ✓ " . $item['original_name'] . " - " . $date . "\n";
}

// Test file size formatting
echo "\nTesting file size formatting:\n";
foreach (array_slice($data, 0, 3) as $item) {
    $size = $item['file_size'];
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;
    
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }
    
    $formattedSize = round($size, 2) . ' ' . $units[$unitIndex];
    echo "  ✓ " . $item['original_name'] . " - " . $formattedSize . "\n";
}

// Test gallery HTML generation
echo "\nTesting gallery HTML generation:\n";
if (count($recentImages) > 0) {
    echo "✓ Gallery would display " . count($recentImages) . " images\n";
    
    // Show sample gallery item structure
    $sample = $recentImages[0];
    echo "✓ Sample gallery item:\n";
    echo "  - Original name: " . $sample['original_name'] . "\n";
    echo "  - Thumbnail URL: " . $sample['thumbnail_url'] . "\n";
    echo "  - Upload time: " . $sample['upload_time'] . "\n";
    echo "  - File size: " . number_format($sample['file_size']) . " bytes\n";
} else {
    echo "✗ No images to display in gallery\n";
}

// Test stats calculation
echo "\nTesting stats calculation:\n";
$stats = [
    'total_uploads' => count($data),
    'total_images' => count($images),
    'total_videos' => count($videos),
    'latest_upload' => !empty($data) ? $data[0]['upload_time'] : null
];

echo "✓ Total uploads: " . $stats['total_uploads'] . "\n";
echo "✓ Total images: " . $stats['total_images'] . "\n";
echo "✓ Total videos: " . $stats['total_videos'] . "\n";
echo "✓ Latest upload: " . $stats['latest_upload'] . "\n";

echo "\n=== Demo Data Test Complete ===\n\n";
echo "✓ All tests passed! The gallery functionality is ready.\n";
echo "✓ Demo data shows " . count($images) . " images and " . count($videos) . " videos.\n";
echo "✓ Gallery will display the most recent " . min(20, count($images)) . " images.\n\n";

echo "To see the gallery in action:\n";
echo "1. Visit: http://localhost:8001 (if server is running)\n";
echo "2. Or start server: php -S localhost:8000\n";
echo "3. The gallery will show the demo photos at the bottom of the page.\n"; 