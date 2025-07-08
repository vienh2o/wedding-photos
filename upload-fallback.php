<?php
// Prevent any output before JSON
if (ob_get_level()) {
    ob_clean();
}

require_once 'config.php';

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

// Check if Google Drive is configured
$googleDriveConfigured = !empty(GOOGLE_DRIVE_FOLDER_ID) && file_exists(GOOGLE_APPLICATION_CREDENTIALS);

if (!$googleDriveConfigured) {
    // Use fallback local storage
    handleLocalUpload();
} else {
    // Use Google Drive upload
    handleGoogleDriveUpload();
}

function handleLocalUpload() {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
            echo json_encode([
                'success' => false, 
                'error' => 'No file was uploaded'
            ]);
            return;
        }

        $file = $_FILES['file'];
        
        // Validate file
        $validation = validateFile($file);
        if (!$validation['valid']) {
            echo json_encode([
                'success' => false,
                'error' => $validation['error']
            ]);
            return;
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($file['name'], PATHINFO_FILENAME);
        $timestamp = date('Y-m-d_H-i-s');
        $randomString = bin2hex(random_bytes(4));
        $newFilename = sprintf('%s_%s_%s.%s', $nameWithoutExtension, $timestamp, $randomString, $extension);
        
        $uploadPath = $uploadDir . $newFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save uploaded file'
            ]);
            return;
        }

        // Track the upload
        $tracker = new \WeddingUpload\UploadTracker();
        $tracker->addUpload([
            'file_name' => $newFilename,
            'original_name' => $file['name'],
            'file_id' => uniqid(),
            'web_link' => '',
            'mime_type' => $file['type'],
            'file_size' => $file['size']
        ]);

        echo json_encode([
            'success' => true,
            'message' => UPLOAD_SUCCESS_MESSAGE,
            'file_name' => $newFilename,
            'file_id' => uniqid(),
            'local_path' => $uploadPath
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE
        ]);
    }
}

function handleGoogleDriveUpload() {
    try {
        require_once 'vendor/autoload.php';
        require_once 'src/UploadHandler.php';
        
        $uploadHandler = new \WeddingUpload\UploadHandler();
        
        // Check if service is available
        if (!$uploadHandler->isServiceAvailable()) {
            echo json_encode([
                'success' => false, 
                'error' => 'Google Drive service is not available'
            ]);
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
            echo json_encode([
                'success' => false, 
                'error' => 'No file was uploaded'
            ]);
            return;
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
}

function validateFile($file) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return [
            'valid' => false,
            'error' => 'No file was uploaded'
        ];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'valid' => false,
            'error' => 'File size exceeds maximum limit of ' . formatBytes(MAX_FILE_SIZE)
        ];
    }

    // Check file type
    $allowedTypes = ALLOWED_IMAGE_TYPES;
    if (!in_array($file['type'], $allowedTypes)) {
        return [
            'valid' => false,
            'error' => 'File type not allowed. Please upload images (JPEG, PNG, GIF, WebP)'
        ];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $errorMessage = isset($errorMessages[$file['error']]) 
            ? $errorMessages[$file['error']] 
            : 'Unknown upload error';
            
        return [
            'valid' => false,
            'error' => $errorMessage
        ];
    }

    return ['valid' => true];
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
} 