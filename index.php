<?php
session_start();
require_once 'config.php';

$name = $_SESSION['name'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';

// Load data only if DB connection is available
if ($conn) {
    // Get faculties for display
    $faculties_result = $conn->query("SELECT * FROM faculties ORDER BY name");
    $faculties = $faculties_result ? $faculties_result->fetch_all(MYSQLI_ASSOC) : [];

    // Get recent uploads
    $recent_resources = $conn->query("SELECT r.*, c.course_code, c.course_name 
                                       FROM resources r 
                                       JOIN courses c ON r.course_id = c.id 
                                       ORDER BY r.created_at DESC LIMIT 10");
} else {
    // Ensure variables exist to prevent fatal errors in the template
    $faculties = [];
    $recent_resources = null;
    // Add one-time alert if not already present
    $_SESSION['alerts'][] = [
        'type' => 'error',
        'message' => 'Database connection failed. Please check database settings and try again.'
    ];
    $alerts = $_SESSION['alerts'];
}

// Clear only alerts and active_form after capturing them
unset($_SESSION['alerts']);
unset($_SESSION['active_form']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Pass.Papers - University Past Papers Library</title>
<link rel="stylesheet" href="styles.css">
<link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  
<header>
<a href="index.php" class="logo">Pass.Papers</a>

<nav>
<a href="index.php">Home</a>
<?php if($name): ?>
<a href="browse.php">Browse</a>
<a href="upload.php">Upload</a>
<a href="forum.php">Forum</a>
<?php 
// Show Admin link only for admins
if ($user_id && $conn) {
    $user_check = $conn->query("SELECT role FROM users WHERE id = {$user_id}");
    $current_user = $user_check ? $user_check->fetch_assoc() : null;
} else {
    $current_user = null;
}
if($current_user && $current_user['role'] == 'admin'):
?>
<a href="admin.php">Admin</a>
<?php endif; ?>
<?php endif; ?>
<a href="about.php">About</a>
</nav>

<div class="user-auth">
<?php if(!empty($name)): ?>
<div class="profile-box">
<div class="avatar-circle"><?= strtoupper($name[0])?></div>
<div class="dropdown">
<a href="profile.php">My Account</a>
<a href="my-uploads.php">My Uploads</a>
<a href="logout.php">Logout</a>
</div>
</div>
<?php else: ?>
<button type="button" class="login-btn-model">Login</button>
<?php endif; ?>
</div>
</header>

<!-- Hero Section -->
<section class="hero-section">
<div class="hero-content">
<h1>Welcome to Pass.Papers<?= $name ? ', ' . $name : '' ?>!</h1>
<p>Your centralized platform for accessing university past papers, course materials, and collaborative learning</p>

<?php if(!$name): ?>
<button class="cta-btn login-btn-model">Get Started</button>
<?php else: ?>
<div class="search-container">
<form action="search.php" method="GET">
<input type="text" name="q" placeholder="Search by course code or name..." class="hero-search">
<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
</form>
</div>
<?php endif; ?>
</div>
</section>

<?php if($name): ?>
<!-- Faculties Section -->
<section class="faculties-section">
<div class="container">
<h2>Browse by Faculty</h2>
<div class="faculty-grid">
<?php foreach($faculties as $faculty): ?>
<a href="courses.php?faculty_id=<?= $faculty['id'] ?>" class="faculty-card">
<i class='bx bxs-graduation'></i>
<h3><?= htmlspecialchars($faculty['name']) ?></h3>
<p><?= htmlspecialchars($faculty['description']) ?></p>
</a>
<?php endforeach; ?>
</div>
</div>
</section>

<!-- Recent Uploads Section -->
<section class="recent-section">
<div class="container">
<h2>Recently Added Papers</h2>
<div class="resources-list">
<?php if ($recent_resources): ?>
<?php while($resource = $recent_resources->fetch_assoc()): ?>
<div class="resource-item">
<div class="resource-icon">
<i class='bx bxs-file-pdf'></i>
</div>
<div class="resource-info">
<h4><?= htmlspecialchars($resource['title']) ?></h4>
<p class="course-tag"><?= htmlspecialchars($resource['course_code']) ?> - <?= htmlspecialchars($resource['course_name']) ?></p>
<span class="upload-meta">Uploaded by <?= htmlspecialchars($resource['uploader_name']) ?> • <?= date('M d, Y', strtotime($resource['created_at'])) ?></span>
</div>
<a href="download.php?id=<?= $resource['id'] ?>" class="download-btn"><i class='bx bx-download'></i> Download</a>
</div>
<?php endwhile; ?>
<?php else: ?>
<div class="no-resources">
<i class='bx bx-error'></i>
<h3>Unable to load recent resources</h3>
<p>Please check your database connection settings.</p>
</div>
<?php endif; ?>
</div>
</div>
</section>
<?php endif; ?>

<?php if(!empty($alerts)): ?>
<div class="alert-box show">
<?php foreach ($alerts as $alert): ?>
<div class="alert <?= $alert['type']; ?>">
<i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-error-circle'; ?>'></i>
<span><?= $alert['message']; ?></span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Auth Modal -->
<div class="auth-model <?= $active_form === 'register' ? 'show slide' : ($active_form === 'login' ? 'show' : ''); ?>">
<button type="button" class="close-btn-model">X</button>

<div class="form-box login">
<h2>Login</h2>
<form action="auth_process.php" method="POST">
<div class="input-box">
<input type="email" name="email" placeholder="University Email" required>
<i class='bx bxs-envelope'></i>
</div>
<div class="input-box">
<input type="password" name="password" placeholder="Password" required>
<i class='bx bxs-lock-alt'></i>
</div>
<button type="submit" name="login_btn" class="btn">Login</button>
<p>Don't have an account? <a href="#" class="register-link">Register</a></p>
</form>
</div>

<div class="form-box register">
  <h2>Register</h2>
  <form action="auth_process.php" method="POST">
  
  <div class="input-box">
  <input type="text" name="name" placeholder="Full Name" required>
  <i class='bx bxs-user'></i>
  </div>

  <div class="input-box">
  <input type="email" name="email" placeholder="University Email" required>
  <i class='bx bxs-envelope'></i>
  </div>

  <div class="input-box">
  <select name="faculty" required>
  <option value="" disabled selected>Select Faculty</option>
  <?php 
  $faculties_reset = $conn->query("SELECT * FROM faculties ORDER BY name");
  while($faculty = $faculties_reset->fetch_assoc()): 
  ?>
  <option value="<?= $faculty['id'] ?>"><?= htmlspecialchars($faculty['name']) ?></option>
  <?php endwhile; ?>
  </select>
  <i class='bx bxs-building'></i>
  </div>

  <div class="input-box">
  <input type="text" name="course" placeholder="Course/Program (e.g., Computer Science)" required>
  <i class='bx bxs-book'></i>
  </div>

  <div class="input-box">
  <select name="year" required>
  <option value="" disabled selected>Year of Study</option>
  <option value="1">Year 1</option>
  <option value="2">Year 2</option>
  <option value="3">Year 3</option>
  <option value="4">Year 4</option>
  <option value="5">Year 5</option>
  </select>
  <i class='bx bx-calendar'></i>
  </div>

  <div class="input-box">
  <input type="password" name="password" placeholder="Password (min 6 characters)" required minlength="6">
  <i class='bx bxs-lock-alt'></i>
  </div>

  <button type="submit" name="register_btn" class="btn">Register</button>
  <p>Already have an account? <a href="#" class="login-link">Login</a></p>

  </form>
</div>
</div>

<footer>
<div class="container">
<p>&copy; 2025 Pass.Papers - University Past Papers Library. All rights reserved.</p>
<p>Saving time, improving performance, encouraging collaboration.</p>
</div>
</footer>

<script src="script.js"></script>
</body>
</html>