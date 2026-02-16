<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Handle alerts
$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Change Password - Pass.Papers</title>
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

<div class="profile-container">
    <div class="form-container">
        <h2><i class='bx bx-lock'></i> Change Password</h2>
        
        <form action="update_password.php" method="POST">
            <div class="input-box">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter your current password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <div class="input-box">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password (min 6 characters)" required minlength="6">
                <i class='bx bxs-lock'></i>
            </div>

            <div class="input-box">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required minlength="6">
                <i class='bx bxs-lock-open'></i>
            </div>

            <div class="form-actions">
                <button type="submit" name="change_password" class="btn btn-primary">
                    <i class='bx bx-check'></i> Update Password
                </button>
                <a href="profile.php" class="btn btn-secondary">
                    <i class='bx bx-x'></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>