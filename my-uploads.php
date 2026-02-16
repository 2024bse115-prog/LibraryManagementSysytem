<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Get user's uploads
$uploads = $conn->query("SELECT r.*, c.course_code, c.course_name FROM resources r JOIN courses c ON r.course_id = c.id WHERE r.uploader_id = $user_id ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>My Uploads - Pass.Papers</title>
<link rel="stylesheet" href="styles.css">
<link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header>
<a href="index.php" class="logo">Pass.Papers</a>
<nav>
<a href="index.php">Home</a>
<a href="browse.php">Browse</a>
<a href="upload.php">Upload</a>
<a href="forum.php">Forum</a>
<a href="about.php">About</a>
</nav>
<div class="user-auth">
<div class="profile-box">
<div class="avatar-circle"><?= strtoupper($name[0])?></div>
<div class="dropdown">
<a href="profile.php">My Account</a>
<a href="my-uploads.php">My Uploads</a>
<a href="logout.php">Logout</a>
</div>
</div>
</div>
</header>

<div class="my-uploads-container">
    <h1><i class='bx bx-folder'></i> My Uploads</h1>
    <p class="page-subtitle">Resources you've contributed to Pass.Papers</p>

    <div class="resources-list">
        <?php if($uploads->num_rows > 0): ?>
            <?php while($resource = $uploads->fetch_assoc()): ?>
            <div class="resource-item">
                <div class="resource-icon">
                    <i class='bx bxs-file-pdf'></i>
                </div>
                <div class="resource-info">
                    <h4><?= htmlspecialchars($resource['title']) ?></h4>
                    <p class="course-tag"><?= htmlspecialchars($resource['course_code']) ?> - <?= htmlspecialchars($resource['course_name']) ?></p>
                    <div class="resource-meta">
                        <span><i class='bx bx-calendar'></i> <?= date('M d, Y', strtotime($resource['created_at'])) ?></span>
                        <span><i class='bx bx-download'></i> <?= $resource['downloads'] ?> downloads</span>
                        <span><i class='bx bx-show'></i> <?= $resource['views'] ?> views</span>
                    </div>
                </div>
                <div class="resource-actions">
                    <a href="view.php?id=<?= $resource['id'] ?>" class="view-btn" target="_blank">
                        <i class='bx bx-show'></i> View
                    </a>
                    <a href="download.php?id=<?= $resource['id'] ?>" class="download-btn">
                        <i class='bx bx-download'></i> Download
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="no-resources">
            <i class='bx bx-folder-open'></i>
            <h3>No uploads yet</h3>
            <p>Start contributing to help your fellow students!</p>
            <a href="upload.php" class="btn">Upload Resource</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>