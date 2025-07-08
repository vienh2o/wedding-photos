<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
require_once 'config.php';

// Load UploadTracker class
require_once 'src/UploadTracker.php';
$tracker = new \WeddingUpload\UploadTracker();
$recentImages = $tracker->getRecentImages(20);
$stats = $tracker->getUploadStats();

echo SITE_NAME; 
?></title>
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

        .file-info {
            background-color: #f7fafc;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
        }

        .file-info h4 {
            color: #2d3748;
            margin-bottom: 10px;
        }

        .file-info p {
            color: #4a5568;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .selected-files {
            background-color: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            display: none;
        }

        .selected-files h4 {
            color: #22543d;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .file-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .file-item:last-child {
            border-bottom: none;
        }

        .file-name {
            color: #4a5568;
            font-size: 0.9rem;
            flex: 1;
            margin-right: 10px;
        }

        .file-size {
            color: #718096;
            font-size: 0.8rem;
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

        .gallery-section {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #e2e8f0;
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .gallery-header h2 {
            color: #2d3748;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .gallery-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 5px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            aspect-ratio: 1;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
            padding: 15px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-item-overlay {
            transform: translateY(0);
        }

        .gallery-item-title {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gallery-item-time {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .no-images {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .no-images i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-images h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #4a5568;
        }

        .no-images p {
            font-size: 1rem;
            line-height: 1.6;
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

        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-text">Drop your photos here</div>
            <div class="upload-subtext">or click to browse files (multiple files supported)</div>
            <input type="file" class="file-input" id="fileInput" accept="image/*" multiple>
        </div>

        <button class="upload-btn" id="uploadBtn" disabled>
            <i class="fas fa-upload"></i> Upload Files
        </button>

        <div class="progress-container" id="progressContainer">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">Uploading...</div>
        </div>

        <div id="messageContainer"></div>

        <div class="selected-files" id="selectedFilesContainer">
            <h4><i class="fas fa-files-o"></i> Selected Files</h4>
            <div class="file-list" id="fileList"></div>
        </div>

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

        <!-- Recent Photos Gallery -->
        <div class="gallery-section">
            <div class="gallery-header">
                <h2><i class="fas fa-images"></i> Recent Photos</h2>
                <div class="gallery-stats">
                    <div class="stat-item">
                        <div class="stat-number" id="statImages"><?php echo $stats['total_images']; ?></div>
                        <div class="stat-label">Photos</div>
                    </div>
                </div>
            </div>
            <div class="gallery-grid" id="galleryGrid"></div>
            <div class="no-images" id="noImages" style="display:none;">
                <i class="fas fa-camera"></i>
                <h3>No photos uploaded yet</h3>
                <p>Be the first to share your special moments! Upload your wedding photos above.</p>
            </div>
            <div id="galleryLoading" style="text-align:center; margin:30px 0; display:none; color:#667eea; font-size:1.2rem;">
                <i class="fas fa-spinner fa-spin"></i> Loading more photos...
            </div>
            <div id="galleryEnd" style="text-align:center; margin:30px 0; display:none; color:#718096; font-size:1rem;">
                <i class="fas fa-check-circle"></i> No more photos to load.
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const messageContainer = document.getElementById('messageContainer');
        const selectedFilesContainer = document.getElementById('selectedFilesContainer');
        const fileList = document.getElementById('fileList');

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

        uploadArea.addEventListener('click', (e) => {
            // Prevent if clicking on the file input itself
            if (e.target === fileInput) return;
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
                showSelectedFiles();
                showMessage(`Selected ${selectedFiles.length} file(s)`, 'success');
            } else {
                uploadBtn.disabled = true;
                selectedFilesContainer.style.display = 'none';
                showMessage('Please select valid image or video files', 'error');
            }
        }

        function showSelectedFiles() {
            if (selectedFiles.length === 0) {
                selectedFilesContainer.style.display = 'none';
                return;
            }

            selectedFilesContainer.style.display = 'block';
            fileList.innerHTML = '';

            selectedFiles.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                const fileName = document.createElement('div');
                fileName.className = 'file-name';
                fileName.textContent = file.name;
                
                const fileSize = document.createElement('div');
                fileSize.className = 'file-size';
                fileSize.textContent = formatFileSize(file.size);
                
                fileItem.appendChild(fileName);
                fileItem.appendChild(fileSize);
                fileList.appendChild(fileItem);
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        uploadBtn.addEventListener('click', async () => {
            if (selectedFiles.length === 0) return;

            uploadBtn.disabled = true;
            progressContainer.style.display = 'block';
            messageContainer.innerHTML = '';

            try {
                await uploadMultipleFiles(selectedFiles);
            } catch (error) {
                showMessage(`Upload failed: ${error.message}`, 'error');
            }

            uploadBtn.disabled = false;
            progressContainer.style.display = 'none';
            selectedFiles = [];
            fileInput.value = '';
        });

        async function uploadMultipleFiles(files) {
            const formData = new FormData();
            files.forEach(file => {
                formData.append('files[]', file);
            });

            // Debug: log FormData
            for (let [key, value] of formData.entries()) {
                console.log('FormData entry:', key, value);
            }

            try {
                progressText.textContent = `Uploading ${files.length} file(s)...`;
                progressFill.style.width = '0%';
                
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();
                console.log('Response:', result);

                if (result.success) {
                    // Show overall success message
                    showMessage(`✓ ${result.success_count} file(s) uploaded successfully!` + 
                               (result.error_count > 0 ? ` (${result.error_count} failed)` : ''), 'success');
                    
                    // Show individual file results
                    result.results.forEach(fileResult => {
                        if (fileResult.success) {
                            showMessage(`✓ ${fileResult.file_name} uploaded successfully!`, 'success');
                        } else {
                            showMessage(`✗ ${fileResult.file_name}: ${fileResult.error}`, 'error');
                        }
                    });

                    // Update progress to 100%
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Upload completed!';

                    // Hide selected files display
                    selectedFilesContainer.style.display = 'none';

                    // Update gallery with new images
                    setTimeout(() => {
                        updateGallery();
                    }, 1000);
                } else {
                    showMessage(`✗ Upload failed: ${result.error}`, 'error');
                }

            } catch (error) {
                showMessage(`✗ Upload failed: ${error.message}`, 'error');
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

        // Infinite scroll gallery logic
        const galleryGrid = document.getElementById('galleryGrid');
        const noImages = document.getElementById('noImages');
        const galleryLoading = document.getElementById('galleryLoading');
        const galleryEnd = document.getElementById('galleryEnd');
        const statImages = document.getElementById('statImages');
        const statVideos = document.getElementById('statVideos');
        const statTotal = document.getElementById('statTotal');

        let galleryOffset = 0;
        const galleryLimit = 20;
        let galleryLoadingFlag = false;
        let galleryEndFlag = false;

        async function loadGalleryBatch(initial = false) {
            if (galleryLoadingFlag || galleryEndFlag) return;
            galleryLoadingFlag = true;
            galleryLoading.style.display = 'block';
            try {
                const response = await fetch(`api/recent-images.php?limit=${galleryLimit}&offset=${galleryOffset}`);
                const data = await response.json();
                if (data.success) {
                    if (initial) {
                        galleryGrid.innerHTML = '';
                    }
                    if (data.images.length === 0 && galleryOffset === 0) {
                        noImages.style.display = 'block';
                        galleryGrid.style.display = 'none';
                        galleryEnd.style.display = 'none';
                    } else {
                        noImages.style.display = 'none';
                        galleryGrid.style.display = 'grid';
                        data.images.forEach(image => {
                            const galleryItem = document.createElement('div');
                            galleryItem.className = 'gallery-item';
                            galleryItem.innerHTML = `
                                <img src="${image.thumbnail_url}" 
                                     alt="${image.original_name}"
                                     loading="lazy"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMDAgNzBDMTE2LjU2OSA3MCAxMzAgODMuNDMxIDMwIDEwMEMxMzAgMTE2LjU2OSAxMTYuNTY5IDEzMCAxMDAgMTMwQzgzLjQzMSAxMzAgNzAgMTE2LjU2OSA3MCAxMEM3MCA4My40MzEgODMuNDMxIDcwIDEwMCA3MFoiIGZpbGw9IiNEMjQyRjYiLz4KPHBhdGggZD0iTTEwMCAxMTBDMTA1LjUyMyAxMTAgMTEwIDEwNS41MjMgMTEwIDEwMEMxMTAgOTQuNDc3IDEwNS41MjMgOTAgMTAwIDkwQzk0LjQ3NyA5MCA5MCA5NC40NzcgOTAgMTAwQzkwIDEwNS41MjMgOTQuNDc3IDExMCAxMDAgMTEwWiIgZmlsbD0iI0QyNDJGNiIvPgo8L3N2Zz4K'">
                                <div class="gallery-item-overlay">
                                    <div class="gallery-item-title">${image.original_name}</div>
                                    <div class="gallery-item-time">${new Date(image.upload_time).toLocaleString()}</div>
                                </div>
                            `;
                            galleryGrid.appendChild(galleryItem);
                        });
                        galleryOffset += data.images.length;
                        if (data.images.length < galleryLimit) {
                            galleryEndFlag = true;
                            galleryEnd.style.display = 'block';
                        } else {
                            galleryEnd.style.display = 'none';
                        }
                        // Update stats
                        if (data.stats) {
                            statImages.textContent = data.stats.total_images;
                            statVideos.textContent = data.stats.total_videos;
                            statTotal.textContent = data.stats.total_uploads;
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to load gallery:', error);
            } finally {
                galleryLoading.style.display = 'none';
                galleryLoadingFlag = false;
            }
        }

        // Initial load
        loadGalleryBatch(true);

        // Infinite scroll event
        window.addEventListener('scroll', () => {
            if (galleryEndFlag || galleryLoadingFlag) return;
            const scrollY = window.scrollY || window.pageYOffset;
            const viewportHeight = window.innerHeight;
            const fullHeight = document.body.offsetHeight;
            if (scrollY + viewportHeight > fullHeight - 300) {
                loadGalleryBatch();
            }
        });

        // Update gallery after upload
        async function updateGallery() {
            galleryOffset = 0;
            galleryEndFlag = false;
            await loadGalleryBatch(true);
        }
    </script>
</body>
</html> 