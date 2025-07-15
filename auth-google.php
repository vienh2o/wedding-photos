<?php
/**
 * Google OAuth Authentication Page
 * Use this to authenticate with your personal Google account instead of service account
 */

require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/GoogleDriveOAuthService.php';

$message = '';
$authUrl = '';

try {
    $oauthService = new \WeddingUpload\GoogleDriveOAuthService();
    
    // Check if we have a valid token
    if ($oauthService->isServiceAvailable()) {
        $message = '<div class="success">✅ Google Drive OAuth is already configured and working!</div>';
    } else {
        // Generate auth URL
        $authUrl = $oauthService->getAuthUrl();
        $message = '<div class="info">🔐 Please authenticate with your Google account to enable Google Drive uploads.</div>';
    }
    
} catch (Exception $e) {
    // If there's an error, still try to generate auth URL
    try {
        $oauthService = new \WeddingUpload\GoogleDriveOAuthService();
        $authUrl = $oauthService->getAuthUrl();
        $message = '<div class="info">🔐 Please authenticate with your Google account to enable Google Drive uploads.</div>';
    } catch (Exception $e2) {
        $message = '<div class="error">❌ Error: ' . htmlspecialchars($e2->getMessage()) . '</div>';
    }
}

// Handle OAuth callback
if (isset($_GET['code'])) {
    try {
        $oauthService = new \WeddingUpload\GoogleDriveOAuthService();
        $token = $oauthService->handleAuthCallback($_GET['code']);
        $message = '<div class="success">✅ Google Drive OAuth authentication successful! You can now upload files to Google Drive.</div>';
    } catch (Exception $e) {
        $message = '<div class="error">❌ Authentication failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive Authentication - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
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
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .auth-button {
            display: inline-block;
            background: #4285f4;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
            margin: 10px 0;
        }
        .auth-button:hover {
            background: #3367d6;
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
        .steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Google Drive Authentication</h1>
        
        <?php echo $message; ?>
        
        <?php if ($authUrl): ?>
            <div class="steps">
                <h3>How to authenticate:</h3>
                <ol>
                    <li>Click the "Authenticate with Google" button below</li>
                    <li>Sign in with your personal Google account</li>
                    <li>Grant permission to access your Google Drive</li>
                    <li>You'll be redirected back here when complete</li>
                </ol>
            </div>
            
            <div style="text-align: center;">
                <a href="<?php echo htmlspecialchars($authUrl); ?>" class="auth-button">
                    🔑 Authenticate with Google
                </a>
            </div>
        <?php endif; ?>
        
        <div class="steps">
            <h3>Why use OAuth instead of Service Account?</h3>
            <ul>
                <li>✅ No storage quota limitations</li>
                <li>✅ Uses your personal Google Drive storage</li>
                <li>✅ Files appear in your regular Google Drive</li>
                <li>✅ No need for Shared Drives</li>
            </ul>
        </div>
        
        <a href="index.php" class="back-link">← Back to Upload Page</a>
    </div>
</body>
</html> 