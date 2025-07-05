<?php

namespace WeddingUpload;

class UploadTracker
{
    private $dataFile;
    private $maxEntries = 100; // Keep last 100 entries

    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../data/uploads.json';
        $this->ensureDataDirectory();
    }

    private function ensureDataDirectory()
    {
        $dataDir = dirname($this->dataFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }

    public function addUpload($fileData)
    {
        $uploads = $this->getUploads();
        
        $upload = [
            'id' => uniqid(),
            'filename' => $fileData['file_name'],
            'original_name' => $fileData['original_name'] ?? $fileData['file_name'],
            'file_id' => $fileData['file_id'],
            'web_link' => $fileData['web_link'] ?? '',
            'mime_type' => $fileData['mime_type'] ?? '',
            'file_size' => $fileData['file_size'] ?? 0,
            'upload_time' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'is_image' => $this->isImage($fileData['mime_type'] ?? ''),
            'thumbnail_url' => $this->generateThumbnailUrl($fileData['file_id'], $fileData['mime_type'] ?? '')
        ];

        // Add to beginning of array (most recent first)
        array_unshift($uploads, $upload);

        // Keep only the last maxEntries
        $uploads = array_slice($uploads, 0, $this->maxEntries);

        $this->saveUploads($uploads);
        
        return $upload;
    }

    public function getRecentImages($limit = 20, $offset = 0)
    {
        $uploads = $this->getUploads();
        $images = array_filter($uploads, function($upload) {
            return $upload['is_image'] ?? false;
        });
        $images = array_values($images); // reindex
        return array_slice($images, $offset, $limit);
    }

    public function getRecentUploads($limit = 20, $offset = 0)
    {
        $uploads = $this->getUploads();
        return array_slice($uploads, $offset, $limit);
    }

    private function getUploads()
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }

        $content = file_get_contents($this->dataFile);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }

    private function saveUploads($uploads)
    {
        $json = json_encode($uploads, JSON_PRETTY_PRINT);
        file_put_contents($this->dataFile, $json);
    }

    private function isImage($mimeType)
    {
        return strpos($mimeType, 'image/') === 0;
    }

    private function generateThumbnailUrl($fileId, $mimeType)
    {
        if (!$this->isImage($mimeType)) {
            return '';
        }
        
        // Check if Google Drive is configured
        if (!empty(GOOGLE_DRIVE_FOLDER_ID) && file_exists(GOOGLE_APPLICATION_CREDENTIALS)) {
            // For images, we can use Google Drive's thumbnail API
            return "https://drive.google.com/thumbnail?id={$fileId}&sz=w300";
        } else {
            // For local storage, create a thumbnail
            return $this->createLocalThumbnail($fileId);
        }
    }

    private function createLocalThumbnail($fileId)
    {
        // For now, return a placeholder
        // In a full implementation, you would create actual thumbnails
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNTAgNzBDMTc2LjU2OSA3MCAyMDAgOTMuNDMxIDIwMCAxMjBDMjAwIDE0Ni41NjkgMTc2LjU2OSAxNjAgMTUwIDE2MEMxMjMuNDMxIDE2MCAxMDAgMTQ2LjU2OSAxMDAgMTIwQzEwMCA5My40MzEgMTIzLjQzMSA3MCAxNTAgNzBaIiBmaWxsPSIjRjY3M0VBIi8+CjxwYXRoIGQ9Ik0xNTAgMTMwQzE1OC4yODQgMTMwIDE2NSAxMjMuMjg0IDE2NSAxMTVDMTY1IDEwNi43MTYgMTU4LjI4NCAxMDAgMTUwIDEwMEMxNDEuNzE2IDEwMCAxMzUgMTA2LjcxNiAxMzUgMTE1QzEzNSAxMjMuMjg0IDE0MS43MTYgMTMwIDE1MCAxMzBaIiBmaWxsPSIjRjY3M0VBIi8+Cjwvc3ZnPgo=";
    }

    public function getUploadStats()
    {
        $uploads = $this->getUploads();
        $images = array_filter($uploads, function($upload) {
            return $upload['is_image'] ?? false;
        });
        $videos = array_filter($uploads, function($upload) {
            return !($upload['is_image'] ?? false);
        });

        return [
            'total_uploads' => count($uploads),
            'total_images' => count($images),
            'total_videos' => count($videos),
            'latest_upload' => !empty($uploads) ? $uploads[0]['upload_time'] : null
        ];
    }
} 