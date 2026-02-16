<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$faculty_id = $_GET['faculty_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// Get all faculties
$faculties = $conn->query("SELECT * FROM faculties ORDER BY name");

// Get selected faculty info
if($faculty_id) {
    $faculty_result = $conn->query("SELECT * FROM faculties WHERE id = $faculty_id");
    $faculty = $faculty_result->fetch_assoc();
    
    // Get courses in this faculty
    $courses = $conn->query("SELECT * FROM courses WHERE faculty_id = $faculty_id ORDER BY year, semester");
}

// Get resources for selected course
if($course_id) {
    $course_result = $conn->query("SELECT c.*, f.name as faculty_name FROM courses c JOIN faculties f ON c.faculty_id = f.id WHERE c.id = $course_id");
    $course = $course_result->fetch_assoc();
    
    $resources = $conn->query("SELECT * FROM resources WHERE course_id = $course_id ORDER BY year DESC, created_at DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Browse - Pass.Papers</title>
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
    <?php include __DIR__ . '/partials/flash.php'; ?>
    <aside class="sidebar">
        <h3>Faculties</h3>
        <ul class="faculty-list">
            <?php while($fac = $faculties->fetch_assoc()): ?>
            <li>
                <a href="browse.php?faculty_id=<?= $fac['id'] ?>" 
                   class="<?= $faculty_id == $fac['id'] ? 'active' : '' ?>">
                    <i class='bx bxs-folder'></i> <?= htmlspecialchars($fac['name']) ?>
                </a>
            </li>
            <?php endwhile; ?>
        </ul>
    </aside>

    <main class="main-content">
        <?php if(!$faculty_id && !$course_id): ?>
        <div class="welcome-browse">
            <i class='bx bxs-book-open'></i>
            <h2>Select a Faculty to Browse Courses</h2>
            <p>Choose a faculty from the sidebar to view available courses and resources</p>
        </div>
        
        <?php elseif($faculty_id && !$course_id): ?>
        <div class="courses-section">
            <h2><?= htmlspecialchars($faculty['name']) ?></h2>
            <p class="section-desc"><?= htmlspecialchars($faculty['description']) ?></p>
            
            <div class="courses-grid">
                <?php while($course = $courses->fetch_assoc()): ?>
                <a href="browse.php?faculty_id=<?= $faculty_id ?>&course_id=<?= $course['id'] ?>" class="course-card">
                    <div class="course-code"><?= htmlspecialchars($course['course_code']) ?></div>
                    <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                    <div class="course-meta">
                        <span><i class='bx bx-time'></i> Year <?= $course['year'] ?></span>
                        <span><i class='bx bx-calendar'></i> Sem <?= $course['semester'] ?></span>
                    </div>
                    <?php
                    $count = $conn->query("SELECT COUNT(*) as total FROM resources WHERE course_id = {$course['id']}")->fetch_assoc();
                    ?>
                    <div class="resource-count"><?= $count['total'] ?> resources</div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <?php elseif($course_id): ?>
        <div class="resources-section">
            <div class="course-header">
                <a href="browse.php?faculty_id=<?= $course['faculty_id'] ?>" class="back-btn">
                    <i class='bx bx-arrow-back'></i> Back to Courses
                </a>
                <h2><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h2>
                <p class="course-meta-info">
                    <span><?= htmlspecialchars($course['faculty_name']) ?></span> • 
                    <span>Year <?= $course['year'] ?></span> • 
                    <span>Semester <?= $course['semester'] ?></span>
                </p>
            </div>

            <div class="resources-list">
                <?php if($resources->num_rows > 0): ?>
                    <?php while($resource = $resources->fetch_assoc()): ?>
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
                    <h3>No resources yet</h3>
                    <p>Be the first to contribute to this course!</p>
                    <a href="upload.php?course_id=<?= $course_id ?>" class="btn">Upload Resource</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>