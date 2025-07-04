<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

use WeddingUpload\UploadHandler;

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if Google Drive folder ID is configured
if (empty(GOOGLE_DRIVE_FOLDER_ID)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Google Drive folder ID not configured. Please set GOOGLE_DRIVE_FOLDER_ID in config.php'
    ]);
    exit;
}

// Check if credentials file exists
if (!file_exists(GOOGLE_APPLICATION_CREDENTIALS)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Google Drive credentials not found. Please place your service account key file in the credentials folder.'
    ]);
    exit;
}

try {
    $uploadHandler = new UploadHandler();
    
    // Check if service is available
    if (!$uploadHandler->isServiceAvailable()) {
        echo json_encode([
            'success' => false, 
            'error' => 'Google Drive service is not available'
        ]);
        exit;
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
        echo json_encode([
            'success' => false, 
            'error' => 'No file was uploaded'
        ]);
        exit;
    }

    // Process the upload
    $result = $uploadHandler->handleUpload($_FILES['file']);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => UPLOAD_SUCCESS_MESSAGE,
            'file_name' => $result['file_name'],
            'file_id' => $result['file_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE
    ]);
} 