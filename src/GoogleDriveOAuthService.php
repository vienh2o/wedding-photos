<?php

namespace WeddingUpload;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Exception;

class GoogleDriveOAuthService
{
    private $service;
    private $folderId;
    private $tokenFile;

    public function __construct()
    {
        $this->folderId = GOOGLE_DRIVE_FOLDER_ID;
        $this->tokenFile = __DIR__ . '/../credentials/token.json';
        $this->initializeService();
    }

    private function initializeService()
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(GOOGLE_APPLICATION_CREDENTIALS);
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
            $client->setApplicationName('Wedding Upload Website');
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            // Load previously authorized token from a file, if it exists
            if (file_exists($this->tokenFile)) {
                $accessToken = json_decode(file_get_contents($this->tokenFile), true);
                $client->setAccessToken($accessToken);
            }

            // If there is no previous token or it's expired
            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, otherwise fetch a new one
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    throw new Exception('OAuth token expired and no refresh token available. Please re-authenticate.');
                }
                
                // Save the token to a file
                if (!is_dir(dirname($this->tokenFile))) {
                    mkdir(dirname($this->tokenFile), 0755, true);
                }
                file_put_contents($this->tokenFile, json_encode($client->getAccessToken()));
            }

            $this->service = new Google_Service_Drive($client);
        } catch (Exception $e) {
            throw new Exception('Failed to initialize Google Drive OAuth service: ' . $e->getMessage());
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

    public function getAuthUrl()
    {
        $client = new Google_Client();
        $client->setAuthConfig(GOOGLE_APPLICATION_CREDENTIALS);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->setApplicationName('Wedding Upload Website');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        return $client->createAuthUrl();
    }

    public function handleAuthCallback($code)
    {
        $client = new Google_Client();
        $client->setAuthConfig(GOOGLE_APPLICATION_CREDENTIALS);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->setApplicationName('Wedding Upload Website');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (!is_dir(dirname($this->tokenFile))) {
            mkdir(dirname($this->tokenFile), 0755, true);
        }
        file_put_contents($this->tokenFile, json_encode($token));
        
        return $token;
    }
} 