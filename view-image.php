<?php
/**
 * Image Viewer Script
 * Serves local images with proper headers and security
 */

// Security: Only allow image files
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Get the requested image path
$imagePath = $_GET['path'] ?? '';

// Validate the path
if (empty($imagePath)) {
    http_response_code(400);
    echo 'No image path provided';
    exit;
}

// Extract filename and date from path
$pathParts = explode('/', trim($imagePath, '/'));
if (count($pathParts) < 2) {
    http_response_code(400);
    echo 'Invalid image path';
    exit;
}

$date = $pathParts[0];
$filename = $pathParts[1];

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo 'Invalid date format';
    exit;
}

// Validate filename
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    echo 'Invalid file type';
    exit;
}

// Build the full file path
$uploadDir = __DIR__ . '/uploads/';
$fullPath = $uploadDir . $date . '/' . $filename;

// Security: Prevent directory traversal
$realUploadDir = realpath($uploadDir);
$realFullPath = realpath($fullPath);

if ($realFullPath === false || strpos($realFullPath, $realUploadDir) !== 0) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

// Check if file exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    echo 'Image not found';
    exit;
}

// Get file info
$fileInfo = pathinfo($fullPath);
$extension = strtolower($fileInfo['extension']);

// Set appropriate content type
$contentTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$contentType = $contentTypes[$extension] ?? 'application/octet-stream';

// Set headers
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', filemtime($fullPath)));

// Output the image
readfile($fullPath); 