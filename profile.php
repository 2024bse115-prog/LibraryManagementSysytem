<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Get user details
$user_result = $conn->query("SELECT u.*, f.name as faculty_name FROM users u LEFT JOIN faculties f ON u.faculty = f.id WHERE u.id = $user_id");
$user = $user_result->fetch_assoc();

// Get user stats
$uploads_count = $conn->query("SELECT COUNT(*) as total FROM resources WHERE uploader_id = $user_id")->fetch_assoc()['total'];
$discussions_count = $conn->query("SELECT COUNT(*) as total FROM discussions WHERE user_id = $user_id")->fetch_assoc()['total'];
$replies_count = $conn->query("SELECT COUNT(*) as total FROM replies WHERE user_id = $user_id")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile - Pass.Papers</title>
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

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar-large">
            <?= strtoupper($name[0]) ?>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p class="profile-email"><i class='bx bx-envelope'></i> <?= htmlspecialchars($user['email']) ?></p>
            <p class="profile-details">
                <span><i class='bx bx-building'></i> <?= htmlspecialchars($user['faculty_name'] ?? 'N/A') ?></span>
                <span><i class='bx bx-book'></i> <?= htmlspecialchars($user['course'] ?? 'N/A') ?></span>
                <span><i class='bx bx-calendar'></i> Year <?= $user['year_of_study'] ?? 'N/A' ?></span>
            </p>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-card">
            <i class='bx bx-upload'></i>
            <h3><?= $uploads_count ?></h3>
            <p>Uploads</p>
        </div>
        <div class="stat-card">
            <i class='bx bx-message-square-dots'></i>
            <h3><?= $discussions_count ?></h3>
            <p>Discussions</p>
        </div>
        <div class="stat-card">
            <i class='bx bx-message'></i>
            <h3><?= $replies_count ?></h3>
            <p>Replies</p>
        </div>
    </div>

    <div class="profile-actions">
        <h2>Account Settings</h2>
        <a href="edit-profile.php" class="btn-action"><i class='bx bx-edit'></i> Edit Profile</a>
        <a href="change-password.php" class="btn-action"><i class='bx bx-lock'></i> Change Password</a>
        <a href="my-uploads.php" class="btn-action"><i class='bx bx-folder'></i> View My Uploads</a>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>