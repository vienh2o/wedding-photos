<?php

namespace WeddingUpload;

use Exception;

class EnhancedLocalStorage
{
    private $uploadDir;
    private $backupDir;
    private $maxFileSize;
    private $allowedTypes;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../uploads/';
        $this->backupDir = __DIR__ . '/../backups/';
        $this->maxFileSize = MAX_FILE_SIZE;
        $this->allowedTypes = ALLOWED_IMAGE_TYPES;
        
        $this->ensureDirectories();
    }

    private function ensureDirectories()
    {
        // Create upload directory with date-based subdirectories
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Create backup directory
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        
        // Create today's subdirectory
        $todayDir = $this->uploadDir . date('Y-m-d') . '/';
        if (!is_dir($todayDir)) {
            mkdir($todayDir, 0755, true);
        }
    }

    public function uploadFile($file)
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return $validation;
            }

            // Generate unique filename
            $newFilename = $this->generateUniqueFileName($file['name']);
            
            // Create date-based subdirectory
            $dateDir = $this->uploadDir . date('Y-m-d') . '/';
            $uploadPath = $dateDir . $newFilename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return [
                    'success' => false,
                    'error' => 'Failed to save uploaded file'
                ];
            }

            // Create backup copy
            $backupPath = $this->backupDir . $newFilename;
            copy($uploadPath, $backupPath);

            // Generate web-accessible URL
            $webUrl = $this->generateWebUrl($newFilename);

            return [
                'success' => true,
                'file_name' => $newFilename,
                'file_id' => uniqid('local_'),
                'web_link' => $webUrl,
                'local_path' => $uploadPath,
                'backup_path' => $backupPath,
                'upload_date' => date('Y-m-d H:i:s'),
                'file_size' => $file['size']
            ];

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
                'valid' => false,
                'error' => 'No file was uploaded'
            ];
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'valid' => false,
                'error' => 'File size exceeds maximum limit of ' . $this->formatBytes($this->maxFileSize)
            ];
        }

        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            return [
                'success' => false,
                'valid' => false,
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
                'valid' => false,
                'error' => $errorMessage
            ];
        }

        return ['valid' => true];
    }

    private function generateUniqueFileName($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = date('Y-m-d_H-i-s');
        $randomString = bin2hex(random_bytes(4));
        
        return sprintf('%s_%s_%s.%s', $nameWithoutExtension, $timestamp, $randomString, $extension);
    }

    private function generateWebUrl($filename)
    {
        // Generate a web-accessible URL for the file using the image viewer
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $datePath = date('Y-m-d');
        
        // Use the image viewer script for secure image serving
        return sprintf('%s://%s/view-image.php?path=%s/%s', $protocol, $host, $datePath, $filename);
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
        return is_dir($this->uploadDir) && is_writable($this->uploadDir);
    }

    public function getUploadStats()
    {
        $totalFiles = 0;
        $totalSize = 0;
        
        // Count files in upload directory
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalFiles++;
                $totalSize += $file->getSize();
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'upload_directory' => $this->uploadDir
        ];
    }
} 