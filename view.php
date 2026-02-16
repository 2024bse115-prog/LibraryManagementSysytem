<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$resource_id = $_GET['id'] ?? null;

if(!$resource_id) {
    header('Location: browse.php');
    exit();
}

// Get resource details
$result = $conn->query("SELECT r.*, c.course_code, c.course_name FROM resources r JOIN courses c ON r.course_id = c.id WHERE r.id = $resource_id");
$resource = $result->fetch_assoc();

if(!$resource) {
    header('Location: browse.php');
    exit();
}

// Increment view count
$conn->query("UPDATE resources SET views = views + 1 WHERE id = $resource_id");

// Display PDF
$file_path = $resource['file_path'];
if(file_exists($file_path)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <title><?= htmlspecialchars($resource['title']) ?> - Pass.Papers</title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    body {
        margin: 0;
        padding: 0;
        background: #2d3748;
        font-family: 'Poppins', sans-serif;
    }
    
    .pdf-viewer-header {
        background: #1a202c;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    
    .pdf-viewer-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .pdf-viewer-actions {
        display: flex;
        gap: 15px;
    }
    
    .pdf-action-btn {
        padding: 10px 20px;
        background: #667eea;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .pdf-action-btn:hover {
        background: #764ba2;
        transform: translateY(-2px);
    }
    
    .pdf-action-btn.download {
        background: #10b981;
    }
    
    .pdf-action-btn.download:hover {
        background: #059669;
    }
    
    .pdf-container {
        width: 100%;
        height: calc(100vh - 70px);
        overflow: hidden;
    }
    
    .pdf-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .pdf-info {
        padding: 10px 30px;
        background: #374151;
        color: #fff;
        font-size: 13px;
        display: flex;
        gap: 20px;
    }
    
    .pdf-info span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    </style>
    </head>
    <body>
    
    <div class="pdf-viewer-header">
        <div class="pdf-viewer-title">
            <i class='bx bxs-file-pdf' style="font-size: 24px; color: #ef4444;"></i>
            <?= htmlspecialchars($resource['title']) ?>
        </div>
        <div class="pdf-viewer-actions">
            <a href="download.php?id=<?= $resource_id ?>" class="pdf-action-btn download">
                <i class='bx bx-download'></i> Download
            </a>
            <button onclick="window.close()" class="pdf-action-btn">
                <i class='bx bx-x'></i> Close
            </button>
        </div>
    </div>
    
    <div class="pdf-info">
        <span><i class='bx bx-book'></i> <?= htmlspecialchars($resource['course_code']) ?> - <?= htmlspecialchars($resource['course_name']) ?></span>
        <span><i class='bx bx-user'></i> Uploaded by: <?= htmlspecialchars($resource['uploader_name']) ?></span>
        <span><i class='bx bx-download'></i> <?= $resource['downloads'] ?> downloads</span>
        <span><i class='bx bx-show'></i> <?= $resource['views'] ?> views</span>
    </div>
    
    <div class="pdf-container">
        <iframe src="<?= $file_path ?>#toolbar=1&navpanes=0&scrollbar=1" type="application/pdf"></iframe>
    </div>
    
    </body>
    </html>
    <?php
} else {
    echo "<h1>File not found!</h1>";
}
?>