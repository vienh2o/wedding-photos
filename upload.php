<?php
// Prevent any output before JSON
ob_start();

// Include autoloader for classes
require_once 'vendor/autoload.php';

// Error handling wrapper for uploads
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them
ini_set('log_errors', 0); // Log errors instead of displaying them

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Add at the top, after headers and before main logic
$CHUNK_SIZE = 1024 * 1024; // 1MB
$chunkedUploadDir = __DIR__ . '/chunk_uploads/';
if (!is_dir($chunkedUploadDir)) {
    mkdir($chunkedUploadDir, 0755, true);
}

// Check for chunked upload parameters (from URL params or POST)
$isChunked = isset($_GET['upload_id'], $_GET['chunk_index'], $_GET['total_chunks'], $_GET['file_name']) || 
             isset($_POST['upload_id'], $_POST['chunk_index'], $_POST['total_chunks'], $_POST['file_name']);

if ($isChunked) {
    // Get parameters from either GET or POST
    $uploadId = $_GET['upload_id'] ?? $_POST['upload_id'];
    $chunkIndex = $_GET['chunk_index'] ?? $_POST['chunk_index'];
    $totalChunks = $_GET['total_chunks'] ?? $_POST['total_chunks'];
    $fileName = $_GET['file_name'] ?? $_POST['file_name'];
    
    $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $uploadId);
    $chunkIndex = intval($chunkIndex);
    $totalChunks = intval($totalChunks);
    $fileName = basename($fileName);
    $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['upload_id']);
    $chunkIndex = intval($_POST['chunk_index']);
    $totalChunks = intval($_POST['total_chunks']);
    $fileName = basename($_POST['file_name']);
    $chunkFile = $chunkedUploadDir . $uploadId . '_' . $chunkIndex . '.part';

    // Save the chunk
    $input = fopen('php://input', 'rb');
    $output = fopen($chunkFile, 'wb');
    stream_copy_to_stream($input, $output);
    fclose($input);
    fclose($output);

    // Check if all chunks are present
    $allChunksPresent = true;
    for ($i = 0; $i < $totalChunks; $i++) {
        if (!file_exists($chunkedUploadDir . $uploadId . '_' . $i . '.part')) {
            $allChunksPresent = false;
            break;
        }
    }

    if ($allChunksPresent) {
        // Assemble chunks
        $assembledPath = $chunkedUploadDir . $uploadId . '_assembled_' . $fileName;
        $out = fopen($assembledPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $chunkedUploadDir . $uploadId . '_' . $i . '.part';
            $in = fopen($chunkPath, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            unlink($chunkPath);
        }
        fclose($out);

        // Now process as a normal upload (simulate $_FILES)
        $fakeFile = [
            'name' => $fileName,
            'type' => mime_content_type($assembledPath),
            'tmp_name' => $assembledPath,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($assembledPath)
        ];
        
        // Use enhanced local storage
        try {
            require_once 'src/EnhancedLocalStorage.php';
            
            $storage = new \WeddingUpload\EnhancedLocalStorage();
            
            // Check if service is available
            if (!$storage->isServiceAvailable()) {
                returnJsonError('Local storage service is not available');
            }

            // Process the upload
            $result = $storage->uploadFile($fakeFile);
            
            if ($result['success']) {
                // Track the upload
                $tracker = new \WeddingUpload\UploadTracker();
                $tracker->addUpload([
                    'file_name' => $result['file_name'],
                    'original_name' => $fakeFile['name'],
                    'file_id' => $result['file_id'],
                    'web_link' => $result['web_link'],
                    'mime_type' => $fakeFile['type'],
                    'file_size' => $fakeFile['size']
                ]);

                returnJsonSuccess([
                    'message' => UPLOAD_SUCCESS_MESSAGE,
                    'file_name' => $result['file_name'],
                    'file_id' => $result['file_id'],
                    'web_link' => $result['web_link'],
                    'local_path' => $result['local_path']
                ]);
            } else {
                returnJsonError($result['error']);
            }

        } catch (Exception $e) {
            returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
        }
        
        // Remove assembled file after processing
        if (file_exists($assembledPath)) {
            unlink($assembledPath);
        }
        exit;
    } else {
        // Respond with chunk received
        returnJsonSuccess(['message' => "Chunk $chunkIndex/$totalChunks received."]);
    }
    exit;
}

// Function to return JSON error
function returnJsonError($message, $code = 500) {
    ob_clean();
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'code' => $code
    ]);
    exit;
}

// Function to return JSON success
function returnJsonSuccess($data) {
    // Clear any output buffer
    ob_clean();
    
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// Catch any fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        returnJsonError('Server error: ' . $error['message']);
    }
});

// Set error handler to catch warnings and notices
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    // Log the error instead of returning it
    error_log("PHP Error: $message in $file on line $line");
    returnJsonError('Server error occurred');
});

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        returnJsonError('Method not allowed', 405);
    }

    // Include configuration
    if (!file_exists('config.php')) {
        returnJsonError('Configuration file not found');
    }
    
    // Clear any output before including config
    ob_clean();
    require_once 'config.php';

    // Check if we have any files uploaded
    if (empty($_FILES)) {
        returnJsonError('No files were uploaded');
    }

    // Check if we have multiple files or single file
    $hasMultipleFiles = false;
    $files = [];

    // Check for multiple files (files[] field name becomes 'files' in PHP)
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $hasMultipleFiles = true;
        $fileCount = count($_FILES['files']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if (!empty($_FILES['files']['name'][$i])) {
                $files[] = [
                    'name' => $_FILES['files']['name'][$i],
                    'type' => $_FILES['files']['type'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                    'error' => $_FILES['files']['error'][$i],
                    'size' => $_FILES['files']['size'][$i]
                ];
            }
        }
    } elseif (isset($_FILES['file'])) {
        // Single file upload (backward compatibility)
        $files[] = $_FILES['file'];
    } else {
        returnJsonError('No files were uploaded');
    }

    //if (empty($files)) {
    //    returnJsonError('No valid files were uploaded');
    //}

    // Use enhanced local storage as primary method
    // Google Drive has storage quota limitations with service accounts
    ob_clean();
    if ($hasMultipleFiles) {
        handleMultipleEnhancedLocalUploads($files);
    } else {
        handleEnhancedLocalUploadDirect($files[0]);
    }

} catch (Exception $e) {
    returnJsonError('Upload failed: ' . $e->getMessage());
} catch (Error $e) {
    returnJsonError('Fatal error: ' . $e->getMessage());
}

function handleLocalUploadDirect($file) {
    try {
        // Validate file
        $validation = validateFileDirect($file);
        if (!$validation['valid']) {
            returnJsonError($validation['error']);
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
            returnJsonError('Failed to save uploaded file');
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

        returnJsonSuccess([
            'message' => UPLOAD_SUCCESS_MESSAGE,
            'file_name' => $newFilename,
            'file_id' => uniqid(),
            'local_path' => $uploadPath
        ]);

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function handleGoogleDriveUploadDirect($file) {
    try {
        require_once 'src/UploadHandler.php';
        
        $uploadHandler = new \WeddingUpload\UploadHandler();
        
        // Check if service is available
        if (!$uploadHandler->isServiceAvailable()) {
            returnJsonError('Google Drive service is not available');
        }

        // Process the upload
        $result = $uploadHandler->handleUpload($file);
        
        if ($result['success']) {
            returnJsonSuccess([
                'message' => UPLOAD_SUCCESS_MESSAGE,
                'file_name' => $result['file_name'],
                'file_id' => $result['file_id']
            ]);
        } else {
            returnJsonError($result['error']);
        }

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function handleMultipleLocalUploads($files) {
    try {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($files as $index => $file) {
            try {
                // Validate file
                $validation = validateFileDirect($file);
                if (!$validation['valid']) {
                    $results[] = [
                        'index' => $index,
                        'file_name' => $file['name'],
                        'success' => false,
                        'error' => $validation['error']
                    ];
                    $errorCount++;
                    continue;
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
                    $results[] = [
                        'index' => $index,
                        'file_name' => $file['name'],
                        'success' => false,
                        'error' => 'Failed to save uploaded file'
                    ];
                    $errorCount++;
                    continue;
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

                $results[] = [
                    'index' => $index,
                    'file_name' => $file['name'],
                    'success' => true,
                    'new_filename' => $newFilename,
                    'file_id' => uniqid(),
                    'local_path' => $uploadPath
                ];
                $successCount++;

            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'file_name' => $file['name'],
                    'success' => false,
                    'error' => DEBUG_MODE ? $e->getMessage() : 'Upload failed'
                ];
                $errorCount++;
            }
        }

        returnJsonSuccess([
            'message' => "Uploaded $successCount file(s) successfully" . ($errorCount > 0 ? ", $errorCount failed" : ""),
            'results' => $results,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($files)
        ]);

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function handleMultipleGoogleDriveUploads($files) {
    try {
        require_once 'src/UploadHandler.php';
        
        $uploadHandler = new \WeddingUpload\UploadHandler();
        
        // Check if service is available
        if (!$uploadHandler->isServiceAvailable()) {
            returnJsonError('Google Drive service is not available');
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($files as $index => $file) {
            try {
                // Process the upload
                $result = $uploadHandler->handleUpload($file);
                
                $results[] = array_merge([
                    'index' => $index,
                    'file_name' => $file['name']
                ], $result);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }

            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'file_name' => $file['name'],
                    'success' => false,
                    'error' => DEBUG_MODE ? $e->getMessage() : 'Upload failed'
                ];
                $errorCount++;
            }
        }

        returnJsonSuccess([
            'message' => "Uploaded $successCount file(s) successfully" . ($errorCount > 0 ? ", $errorCount failed" : ""),
            'results' => $results,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($files)
        ]);

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function validateFileDirect($file) {

    
    // Check if file was uploaded
    if (!isset($file['tmp_name'])) {
        return [
            'valid' => false,
            'error' => 'No tmp_name found'
        ];
    }
    
    if (!is_uploaded_file($file['tmp_name'])) {
        return [
            'valid' => false,
            'error' => 'File not uploaded via HTTP POST (tmp_name: ' . $file['tmp_name'] . ')'
        ];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'valid' => false,
            'error' => 'File size exceeds maximum limit of ' . formatBytesDirect(MAX_FILE_SIZE)
        ];
    }

    // Check file type
    $allowedTypes = ALLOWED_IMAGE_TYPES;
    if (!in_array($file['type'], $allowedTypes)) {
        return [
            'valid' => false,
            'error' => 'File type not allowed (' . $file['type'] . '). Please upload images (JPEG, PNG, GIF, WebP)'
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

function handleEnhancedLocalUploadDirect($file) {
    try {
        require_once 'src/EnhancedLocalStorage.php';
        
        $storage = new \WeddingUpload\EnhancedLocalStorage();
        
        // Check if service is available
        if (!$storage->isServiceAvailable()) {
            returnJsonError('Local storage service is not available');
        }

        // Process the upload
        $result = $storage->uploadFile($file);
        
        if ($result['success']) {
            // Track the upload
            $tracker = new \WeddingUpload\UploadTracker();
            $tracker->addUpload([
                'file_name' => $result['file_name'],
                'original_name' => $file['name'],
                'file_id' => $result['file_id'],
                'web_link' => $result['web_link'],
                'mime_type' => $file['type'],
                'file_size' => $file['size']
            ]);

            returnJsonSuccess([
                'message' => UPLOAD_SUCCESS_MESSAGE,
                'file_name' => $result['file_name'],
                'file_id' => $result['file_id'],
                'web_link' => $result['web_link'],
                'local_path' => $result['local_path']
            ]);
        } else {
            returnJsonError($result['error']);
        }

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function handleMultipleEnhancedLocalUploads($files) {
    try {
        require_once 'src/EnhancedLocalStorage.php';
        
        $storage = new \WeddingUpload\EnhancedLocalStorage();
        
        // Check if service is available
        if (!$storage->isServiceAvailable()) {
            returnJsonError('Local storage service is not available');
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($files as $index => $file) {
            try {
                // Process the upload
                $result = $storage->uploadFile($file);
                
                if ($result['success']) {
                    // Track the upload
                    $tracker = new \WeddingUpload\UploadTracker();
                    $tracker->addUpload([
                        'file_name' => $result['file_name'],
                        'original_name' => $file['name'],
                        'file_id' => $result['file_id'],
                        'web_link' => $result['web_link'],
                        'mime_type' => $file['type'],
                        'file_size' => $file['size']
                    ]);

                    $results[] = [
                        'index' => $index,
                        'file_name' => $file['name'],
                        'success' => true,
                        'new_filename' => $result['file_name'],
                        'file_id' => $result['file_id'],
                        'web_link' => $result['web_link'],
                        'local_path' => $result['local_path']
                    ];
                    $successCount++;
                } else {
                    $results[] = [
                        'index' => $index,
                        'file_name' => $file['name'],
                        'success' => false,
                        'error' => $result['error']
                    ];
                    $errorCount++;
                }

            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'file_name' => $file['name'],
                    'success' => false,
                    'error' => DEBUG_MODE ? $e->getMessage() : 'Upload failed'
                ];
                $errorCount++;
            }
        }

        returnJsonSuccess([
            'message' => "Uploaded $successCount file(s) successfully" . ($errorCount > 0 ? ", $errorCount failed" : ""),
            'results' => $results,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_count' => count($files)
        ]);

    } catch (Exception $e) {
        returnJsonError(DEBUG_MODE ? $e->getMessage() : UPLOAD_ERROR_MESSAGE);
    }
}

function formatBytesDirect($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
} 