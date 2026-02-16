<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$course_id = $_GET['course_id'] ?? null;

if(!$course_id) {
    header('Location: browse.php');
    exit();
}

// Get course info
$course_result = $conn->query("SELECT c.*, f.name as faculty_name FROM courses c JOIN faculties f ON c.faculty_id = f.id WHERE c.id = '$course_id'");
$course = $course_result->fetch_assoc();

if(!$course) {
    header('Location: browse.php');
    exit();
}

// Get resources for this course
$resources_result = $conn->query("SELECT * FROM resources WHERE course_id = '$course_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($course['course_name']) ?> - Pass.Papers</title>
<link rel="stylesheet" href="styles.css">
<link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header>
<a href="index.php" class="logo">Pass.Papers</a>
<nav>
<a href="index.php">Home</a>
<a href="browse.php" class="active">Browse</a>
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

<div class="browse-container">
    <div class="main-content">
        <div class="course-header">
            <a href="courses.php?faculty_id=<?= $course['faculty_id'] ?>" class="back-btn"><i class='bx bx-arrow-back'></i> Back to <?= htmlspecialchars($course['faculty_name']) ?></a>
            <h2><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h2>
            <p class="course-meta-info"><?= htmlspecialchars($course['faculty_name']) ?> • Year <?= $course['year_level'] ?> • <?= $course['credits'] ?> Credits</p>
        </div>

        <?php if($resources_result->num_rows > 0): ?>
        <div class="resources-list">
            <?php while($resource = $resources_result->fetch_assoc()): ?>
            <div class="resource-item">
                <div class="resource-icon">
                    <i class='bx bxs-file-pdf'></i>
                </div>
                <div class="resource-info">
                    <h4><?= htmlspecialchars($resource['title']) ?></h4>
                    <?php if($resource['description']): ?>
                    <p class="resource-desc"><?= htmlspecialchars($resource['description']) ?></p>
                    <?php endif; ?>
                    <div class="resource-meta">
                        <span><i class='bx bx-user'></i> <?= htmlspecialchars($resource['uploader_name']) ?></span>
                        <span><i class='bx bx-calendar'></i> Year <?= $resource['year'] ?></span>
                        <span><i class='bx bx-book-open'></i> Semester <?= $resource['semester'] ?></span>
                        <span><i class='bx bx-time'></i> <?= date('M d, Y', strtotime($resource['created_at'])) ?></span>
                    </div>
                </div>
                <div class="resource-actions">
                    <a href="download.php?id=<?= $resource['id'] ?>" class="download-btn"><i class='bx bx-download'></i> Download</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-resources">
            <i class='bx bx-folder-open'></i>
            <h3>No resources available yet</h3>
            <p>Be the first to upload a resource for this course!</p>
            <a href="upload.php?course_id=<?= $course_id ?>" class="cta-btn">Upload Resource</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>