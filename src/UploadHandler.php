<?php

namespace WeddingUpload;

use Exception;

class UploadHandler
{
    private $googleDriveService;
    private $uploadDir;

    public function __construct()
    {
        $this->googleDriveService = new GoogleDriveService();
        $this->uploadDir = __DIR__ . '/../uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function handleUpload($file)
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }

            // Generate temporary file path
            $tempPath = $this->uploadDir . uniqid() . '_' . basename($file['name']);
            
            // Move uploaded file to temporary location
            if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
                return [
                    'success' => false,
                    'error' => 'Failed to save uploaded file'
                ];
            }

            // Upload to Google Drive
            $result = $this->googleDriveService->uploadFile(
                $tempPath,
                $file['name'],
                $file['type']
            );

            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Track the upload if successful
            if ($result['success']) {
                $tracker = new \WeddingUpload\UploadTracker();
                $tracker->addUpload([
                    'file_name' => $result['file_name'],
                    'original_name' => $file['name'],
                    'file_id' => $result['file_id'],
                    'web_link' => $result['web_link'],
                    'mime_type' => $file['type'],
                    'file_size' => $file['size']
                ]);
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    private function validateFile($file)
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'No file was uploaded'
            ];
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return [
                'success' => false,
                'error' => 'File size exceeds maximum limit of ' . $this->formatBytes(MAX_FILE_SIZE)
            ];
        }

        // Check file type
        $allowedTypes = ALLOWED_IMAGE_TYPES;
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'File type not allowed. Please upload images (JPEG, PNG, GIF, WebP) only.'
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
                'success' => false,
                'error' => $errorMessage
            ];
        }

        return ['success' => true];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function isServiceAvailable()
    {
        return $this->googleDriveService->isServiceAvailable();
    }
} 