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
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Get faculties
$faculties_result = $conn->query("SELECT * FROM faculties ORDER BY name");
$faculties = $faculties_result->fetch_all(MYSQLI_ASSOC);

// Handle alerts
$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profile - Pass.Papers</title>
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
        <h2><i class='bx bx-edit'></i> Edit Profile</h2>
        
        <form action="update_profile.php" method="POST">
            <div class="input-box">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <i class='bx bxs-envelope'></i>
            </div>

            <div class="input-box">
                <label>Faculty</label>
                <select name="faculty" required>
                    <option value="" disabled>Select Faculty</option>
                    <?php foreach($faculties as $faculty): ?>
                    <option value="<?= $faculty['id'] ?>" <?= $user['faculty'] == $faculty['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($faculty['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <i class='bx bxs-building'></i>
            </div>

            <div class="input-box">
                <label>Course/Program</label>
                <input type="text" name="course" value="<?= htmlspecialchars($user['course']) ?>" required>
                <i class='bx bxs-book'></i>
            </div>

            <div class="input-box">
                <label>Year of Study</label>
                <select name="year" required>
                    <option value="" disabled>Year of Study</option>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= $user['year_of_study'] == $i ? 'selected' : '' ?>>
                        Year <?= $i ?>
                    </option>
                    <?php endfor; ?>
                </select>
                <i class='bx bx-calendar'></i>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class='bx bx-save'></i> Save Changes
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