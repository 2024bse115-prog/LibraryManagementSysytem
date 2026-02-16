<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$faculty_id = $_GET['faculty_id'] ?? null;

if(!$faculty_id) {
    header('Location: browse.php');
    exit();
}

// Get faculty info
$faculty_result = $conn->query("SELECT * FROM faculties WHERE id = '$faculty_id'");
$faculty = $faculty_result->fetch_assoc();

if(!$faculty) {
    header('Location: browse.php');
    exit();
}

// Get courses in this faculty
$courses_result = $conn->query("SELECT c.*, COUNT(r.id) as resource_count 
                                FROM courses c 
                                LEFT JOIN resources r ON c.id = r.course_id 
                                WHERE c.faculty_id = '$faculty_id' 
                                GROUP BY c.id 
                                ORDER BY c.course_name");
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($faculty['name']) ?> - Pass.Papers</title>
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
        <section class="courses-section">
            <a href="browse.php" class="back-btn"><i class='bx bx-arrow-back'></i> Back to Browse</a>
            
            <h2><?= htmlspecialchars($faculty['name']) ?></h2>
            <p class="section-desc"><?= htmlspecialchars($faculty['description']) ?></p>

            <?php if($courses_result->num_rows > 0): ?>
            <div class="courses-grid">
                <?php while($course = $courses_result->fetch_assoc()): ?>
                <a href="browse.php?faculty_id=<?= $faculty_id ?>&course_id=<?= $course['id'] ?>" class="course-card">
                    <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                    <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                    <div class="course-meta">
                        <span><i class='bx bx-time'></i> Year <?= $course['year'] ?></span>
                        <span><i class='bx bx-calendar'></i> Sem <?= $course['semester'] ?></span>
                    </div>
                    <p class="resource-count"><?= $course['resource_count'] ?> resources available</p>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="no-resources">
                <i class='bx bx-folder-open'></i>
                <h3>No courses available yet</h3>
                <p>Courses will appear here once they are added.</p>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>