<?php
// Determine the correct path to config.php
$configPath = file_exists('../config.php') ? '../config.php' : 'config.php';
require_once $configPath;

$trackerPath = file_exists('../src/UploadTracker.php') ? '../src/UploadTracker.php' : 'src/UploadTracker.php';
require_once $trackerPath;

// Check if Composer autoloader exists
$autoloadPath = file_exists('../vendor/autoload.php') ? '../vendor/autoload.php' : 'vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    $tracker = new \WeddingUpload\UploadTracker();
} else {
    $tracker = new \WeddingUpload\UploadTracker();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cross-Origin-Resource-Policy: cross-origin');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $tracker = new \WeddingUpload\UploadTracker();
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $limit = min($limit, 50); // Maximum 50 images
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $recentImages = $tracker->getRecentImages($limit, $offset);
    $stats = $tracker->getUploadStats();
    
    echo json_encode([
        'success' => true,
        'images' => $recentImages,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Failed to load recent images'
    ]);
} 