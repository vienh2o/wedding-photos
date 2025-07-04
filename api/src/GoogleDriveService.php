<?php

namespace WeddingUpload;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Exception;

class GoogleDriveService
{
    private $service;
    private $folderId;

    public function __construct()
    {
        $this->folderId = GOOGLE_DRIVE_FOLDER_ID;
        $this->initializeService();
    }

    private function initializeService()
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(GOOGLE_APPLICATION_CREDENTIALS);
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
            $client->setApplicationName('Wedding Upload Website');

            $this->service = new Google_Service_Drive($client);
        } catch (Exception $e) {
            throw new Exception('Failed to initialize Google Drive service: ' . $e->getMessage());
        }
    }

    public function uploadFile($filePath, $fileName, $mimeType)
    {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $this->generateUniqueFileName($fileName),
                'parents' => [$this->folderId]
            ]);

            $content = file_get_contents($filePath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink'
            ]);

            return [
                'success' => true,
                'file_id' => $file->getId(),
                'file_name' => $file->getName(),
                'web_link' => $file->getWebViewLink()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to upload file to Google Drive: ' . $e->getMessage()
            ];
        }
    }

    private function generateUniqueFileName($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = date('Y-m-d_H-i-s');
        $randomString = bin2hex(random_bytes(4));
        
        return sprintf('%s_%s_%s.%s', $nameWithoutExtension, $timestamp, $randomString, $extension);
    }

    public function isServiceAvailable()
    {
        return $this->service !== null;
    }
} 