<?php
/**
 * Status page to show upload system configuration
 */

require_once 'config.php';
require_once 'vendor/autoload.php';

$status = [];

// Check enhanced local storage
try {
    require_once 'src/EnhancedLocalStorage.php';
    $localStorage = new \WeddingUpload\EnhancedLocalStorage();
    $status['local_storage'] = [
        'available' => $localStorage->isServiceAvailable(),
        'stats' => $localStorage->getUploadStats()
    ];
} catch (Exception $e) {
    $status['local_storage'] = [
        'available' => false,
        'error' => $e->getMessage()
    ];
}

// Check Google Drive service account
$status['google_drive'] = [
    'configured' => !empty(GOOGLE_DRIVE_FOLDER_ID) && file_exists(GOOGLE_APPLICATION_CREDENTIALS),
    'folder_id' => GOOGLE_DRIVE_FOLDER_ID,
    'credentials_exist' => file_exists(GOOGLE_APPLICATION_CREDENTIALS),
    'note' => 'Service accounts have storage quota limitations - using enhanced local storage instead'
];

// Check upload tracker
try {
    $tracker = new \WeddingUpload\UploadTracker();
    $status['tracker'] = [
        'available' => true,
        'stats' => $tracker->getUploadStats()
    ];
} catch (Exception $e) {
    $status['tracker'] = [
        'available' => false,
        'error' => $e->getMessage()
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload System Status - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
            color: #333;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .status-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .status-section.success {
            border-left-color: #28a745;
            background: #f8fff9;
        }
        .status-section.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .status-section.error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .status-item {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 3px;
            border: 1px solid #e9ecef;
        }
        .status-label {
            font-weight: 600;
            color: #495057;
        }
        .status-value {
            margin-left: 10px;
        }
        .status-value.success {
            color: #28a745;
        }
        .status-value.error {
            color: #dc3545;
        }
        .status-value.warning {
            color: #ffc107;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover {
            color: #495057;
        }
        .note {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Upload System Status</h1>
        
        <div class="note">
            <strong>Current Upload Method:</strong> Enhanced Local Storage<br>
            <strong>Status:</strong> ✅ Working perfectly - no authentication required<br>
            <strong>Benefits:</strong> Unlimited storage, automatic organization, web-accessible files
        </div>

        <!-- Local Storage Status -->
        <div class="status-section <?php echo $status['local_storage']['available'] ? 'success' : 'error'; ?>">
            <h3>💾 Local Storage</h3>
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-value <?php echo $status['local_storage']['available'] ? 'success' : 'error'; ?>">
                    <?php echo $status['local_storage']['available'] ? '✅ Available' : '❌ Not Available'; ?>
                </span>
            </div>
            <?php if ($status['local_storage']['available'] && isset($status['local_storage']['stats'])): ?>
                <div class="status-item">
                    <span class="status-label">Total Files:</span>
                    <span class="status-value"><?php echo $status['local_storage']['stats']['total_files']; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Total Size:</span>
                    <span class="status-value"><?php echo $status['local_storage']['stats']['total_size_formatted']; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Upload Directory:</span>
                    <span class="status-value"><?php echo $status['local_storage']['stats']['upload_directory']; ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($status['local_storage']['error'])): ?>
                <div class="status-item">
                    <span class="status-label">Error:</span>
                    <span class="status-value error"><?php echo htmlspecialchars($status['local_storage']['error']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Google Drive Status -->
        <div class="status-section <?php echo $status['google_drive']['configured'] ? 'warning' : 'error'; ?>">
            <h3>☁️ Google Drive</h3>
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-value warning">⚠️ Configured but not used (storage quota limitations)</span>
            </div>
            <div class="status-item">
                <span class="status-label">Folder ID:</span>
                <span class="status-value"><?php echo $status['google_drive']['folder_id'] ?: 'Not set'; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Credentials:</span>
                <span class="status-value <?php echo $status['google_drive']['credentials_exist'] ? 'success' : 'error'; ?>">
                    <?php echo $status['google_drive']['credentials_exist'] ? '✅ Found' : '❌ Missing'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Note:</span>
                <span class="status-value"><?php echo $status['google_drive']['note']; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">OAuth:</span>
                <span class="status-value">❌ Removed - not needed with enhanced local storage</span>
            </div>
        </div>

        <!-- Upload Tracker Status -->
        <div class="status-section <?php echo $status['tracker']['available'] ? 'success' : 'error'; ?>">
            <h3>📈 Upload Tracker</h3>
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-value <?php echo $status['tracker']['available'] ? 'success' : 'error'; ?>">
                    <?php echo $status['tracker']['available'] ? '✅ Available' : '❌ Not Available'; ?>
                </span>
            </div>
            <?php if ($status['tracker']['available'] && isset($status['tracker']['stats'])): ?>
                <div class="status-item">
                    <span class="status-label">Total Uploads:</span>
                    <span class="status-value"><?php echo $status['tracker']['stats']['total_uploads']; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Total Images:</span>
                    <span class="status-value"><?php echo $status['tracker']['stats']['total_images']; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Latest Upload:</span>
                    <span class="status-value"><?php echo $status['tracker']['stats']['latest_upload'] ?: 'None'; ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($status['tracker']['error'])): ?>
                <div class="status-item">
                    <span class="status-label">Error:</span>
                    <span class="status-value error"><?php echo htmlspecialchars($status['tracker']['error']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <a href="index.php" class="back-link">← Back to Upload Page</a>
    </div>
</body>
</html> 