<?php
session_start();
$name = $_SESSION['name'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
<title>About - Pass.Papers</title>
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
<?php endif; ?>
<a href="about.php" class="active">About</a>
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

<div class="about-container">
    <div class="about-hero">
        <h1>About Pass.Papers</h1>
        <p>University Past Papers Library System</p>
    </div>

    <div class="about-content">
        <section class="about-section">
            <h2><i class='bx bx-target-lock'></i> Mission & Vision</h2>
            <p>Pass.Papers is designed to centralize and simplify access to past papers, course materials, and academic resources for university students. We believe in the power of collaborative learning and equal access to educational resources.</p>
        </section>

        <section class="about-section">
            <h2><i class='bx bx-star'></i> Why Pass.Papers?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class='bx bx-time-five'></i>
                    <h3>Saves Time</h3>
                    <p>No more hunting for papers in WhatsApp groups or asking multiple classmates</p>
                </div>
                <div class="feature-item">
                    <i class='bx bx-trending-up'></i>
                    <h3>Improves Performance</h3>
                    <p>Students learn from shared solutions and past examination patterns</p>
                </div>
                <div class="feature-item">
                    <i class='bx bx-group'></i>
                    <h3>Encourages Collaboration</h3>
                    <p>Creates a culture of academic sharing within the institution</p>
                </div>
                <div class="feature-item">
                    <i class='bx bx-shield-alt-2'></i>
                    <h3>Equal Access</h3>
                    <p>Access based on what you need, not who you know</p>
                </div>
                <div class="feature-item">
                    <i class='bx bx-heart'></i>
                    <h3>Reduces Stress</h3>
                    <p>Removes stress from lecturers and students asking for resources</p>
                </div>
                <div class="feature-item">
                    <i class='bx bx-trending-up'></i>
                    <h3>Scalable</h3>
                    <p>Can grow to include tutorials, study guides, and more</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2><i class='bx bx-lock-alt'></i> Privacy & Security</h2>
            <p>Pass.Papers is a secure, login-protected platform. Only verified university students with valid IDs can access the system. All content is behind a secure login wall to ensure academic integrity and copyright compliance.</p>
        </section>

        <section class="about-section">
            <h2><i class='bx bx-envelope'></i> Contact & Support</h2>
            <p>For technical support, feedback, or questions about the platform, please reach out to us:</p>
            <div class="contact-info">
                <p><i class='bx bx-envelope'></i> Email: support@passpapers.edu</p>
                <p><i class='bx bx-phone'></i> Phone: +256 762 463 125</p>
            </div>
        </section>
    </div>
</div>

<footer>
<div class="container">
<p>&copy; 2025 Pass.Papers - University Past Papers Library. All rights reserved.</p>
<p>An academic equalizer, performance booster, and secure collaborative platform.</p>
</div>
</footer>

<script src="script.js"></script>
</body>
</html>