<?php
// Demo version - shows the UI without Google Drive integration
define('SITE_NAME', 'Wedding Memories (Demo)');
define('SITE_DESCRIPTION', 'Share your special moments from our wedding day - Demo Mode');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            color: #2d3748;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            color: #718096;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .demo-notice {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .upload-area {
            border: 3px dashed #cbd5e0;
            border-radius: 15px;
            padding: 40px 20px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #667eea;
            background-color: #f7fafc;
        }

        .upload-icon {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }

        .upload-area:hover .upload-icon,
        .upload-area.dragover .upload-icon {
            color: #667eea;
        }

        .upload-text {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .upload-subtext {
            color: #a0aec0;
            font-size: 0.9rem;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .upload-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .progress-container {
            display: none;
            margin-top: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            color: #4a5568;
            font-size: 0.9rem;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .message.error {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }

        .message.info {
            background-color: #bee3f8;
            color: #2a4365;
            border: 1px solid #90cdf4;
        }

        .supported-formats {
            background-color: #edf2f7;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }

        .supported-formats h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .format-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .format-tag {
            background-color: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .setup-link {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .setup-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(72, 187, 120, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .upload-area {
                padding: 30px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> <?php echo SITE_NAME; ?></h1>
            <p><?php echo SITE_DESCRIPTION; ?></p>
        </div>

        <div class="demo-notice">
            <i class="fas fa-info-circle"></i> This is a demo version. Files will not be uploaded to Google Drive.
        </div>

        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-text">Drop your photos and videos here</div>
            <div class="upload-subtext">or click to browse files</div>
            <input type="file" class="file-input" id="fileInput" accept="image/*,video/*" multiple>
        </div>

        <button class="upload-btn" id="uploadBtn" disabled>
            <i class="fas fa-upload"></i> Upload Files (Demo)
        </button>

        <div class="progress-container" id="progressContainer">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">Uploading...</div>
        </div>

        <div id="messageContainer"></div>

        <div class="supported-formats">
            <h3><i class="fas fa-info-circle"></i> Supported Formats</h3>
            <div class="format-list">
                <span class="format-tag">JPEG</span>
                <span class="format-tag">PNG</span>
                <span class="format-tag">GIF</span>
                <span class="format-tag">WebP</span>
                <span class="format-tag">MP4</span>
                <span class="format-tag">AVI</span>
                <span class="format-tag">MOV</span>
                <span class="format-tag">WebM</span>
            </div>
            <p style="margin-top: 15px; color: #718096; font-size: 0.9rem;">
                Maximum file size: 100MB
            </p>
        </div>

        <a href="INSTALL.md" class="setup-link">
            <i class="fas fa-cog"></i> Setup Full Version
        </a>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const messageContainer = document.getElementById('messageContainer');

        let selectedFiles = [];

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        });

        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            handleFiles(files);
        });

        function handleFiles(files) {
            selectedFiles = files.filter(file => {
                const isImage = file.type.startsWith('image/');
                const isVideo = file.type.startsWith('video/');
                return isImage || isVideo;
            });

            if (selectedFiles.length > 0) {
                uploadBtn.disabled = false;
                showMessage(`Selected ${selectedFiles.length} file(s) for demo`, 'success');
            } else {
                uploadBtn.disabled = true;
                showMessage('Please select valid image or video files', 'error');
            }
        }

        uploadBtn.addEventListener('click', async () => {
            if (selectedFiles.length === 0) return;

            uploadBtn.disabled = true;
            progressContainer.style.display = 'block';
            messageContainer.innerHTML = '';

            // Simulate upload process
            for (let i = 0; i < selectedFiles.length; i++) {
                const file = selectedFiles[i];
                await simulateUpload(file, i + 1, selectedFiles.length);
            }

            uploadBtn.disabled = false;
            progressContainer.style.display = 'none';
            selectedFiles = [];
            fileInput.value = '';
        });

        async function simulateUpload(file, current, total) {
            try {
                progressText.textContent = `Simulating upload: ${file.name} (${current}/${total})`;
                
                // Simulate upload delay
                await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));

                // Simulate success (90% success rate for demo)
                const success = Math.random() > 0.1;
                
                if (success) {
                    showMessage(`✓ ${file.name} - Demo upload successful!`, 'success');
                } else {
                    showMessage(`✗ ${file.name} - Demo upload failed (simulated)`, 'error');
                }

                // Update progress
                const progress = (current / total) * 100;
                progressFill.style.width = progress + '%';

            } catch (error) {
                showMessage(`✗ ${file.name}: Demo error - ${error.message}`, 'error');
            }
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            messageContainer.appendChild(messageDiv);

            // Auto-remove message after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 5000);
        }

        // Show initial message
        showMessage('This is a demo version. Files will not be uploaded to Google Drive.', 'info');
    </script>
</body>
</html> 