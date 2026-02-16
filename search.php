<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$query = $_GET['q'] ?? '';
$faculty_filter = $_GET['faculty'] ?? '';
$year_filter = $_GET['year'] ?? '';
$semester_filter = $_GET['semester'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// Get all faculties for filter dropdown
$faculties = $conn->query("SELECT * FROM faculties ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search - Pass.Papers</title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .resource-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<header>
<a href="index.php" class="logo">Pass.Papers</a>
<nav>
<a href="index.php">Home</a>
<a href="browse.php">Browse</a>
<a href="upload.php">Upload</a>
<a href="forum.php">Forum</a>
<?php 
// Show Admin link only for admins
$user_check = $conn->query("SELECT role FROM users WHERE id = {$_SESSION['user_id']}");
$current_user = $user_check->fetch_assoc();
if($current_user && $current_user['role'] == 'admin'):
?>
<a href="admin.php">Admin</a>
<?php endif; ?>
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

<div class="search-page">
    <div class="search-results">
        <div id="resultsContainer">
            <!-- Results will be loaded here by JavaScript -->
            <div class="loading">Loading resources...</div>
        </div>
    </div>
</div>

<script>
// Build params from current URL/server-side vars and fetch only resources
function loadResults() {
    const params = new URLSearchParams();
    const q = <?= json_encode($query) ?>;
    const faculty = <?= json_encode($faculty_filter) ?>;
    const year = <?= json_encode($year_filter) ?>;
    const semester = <?= json_encode($semester_filter) ?>;
    const sort = <?= json_encode($sort) ?>;

    if (q) params.append('q', q);
    if (faculty) params.append('faculty', faculty);
    if (year) params.append('year', year);
    if (semester) params.append('semester', semester);
    if (sort) params.append('sort', sort);

    // Show loading
    document.getElementById('resultsContainer').innerHTML = '<div class="loading">Loading resources...</div>';

    fetch('api.php?endpoint=resources&' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayResults(data.data);
            } else {
                throw new Error(data.error || 'Failed to load results');
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('resultsContainer').innerHTML = `
                <div class="error">
                    <i class='bx bx-error'></i>
                    <p>Failed to load resources. Please try again.</p>
                </div>`;
        });
}

// Function to display results
function displayResults(results) {
    const container = document.getElementById('resultsContainer');
    
    if (!results || results.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <i class='bx bx-search-alt'></i>
                <p>No resources found. Try different search terms.</p>
            </div>`;
        return;
    }
    
    let html = '';
    results.forEach(item => {
        html += `
        <div class="resource-item">
            <div class="resource-icon">
                <i class='bx bxs-file-pdf'></i>
            </div>
            <div class="resource-info">
                <h4>${escapeHtml(item.title)}</h4>
                <p class="course-tag">${escapeHtml(item.course_code)} - ${escapeHtml(item.course_name)}</p>
                ${item.faculty_name ? `<p class="course-tag" style="background: #10b981;">${escapeHtml(item.faculty_name)}</p>` : ''}
                ${item.description ? `<p>${escapeHtml(item.description)}</p>` : ''}
                <div class="resource-meta">
                    <span><i class='bx bx-download'></i> ${item.downloads || 0} downloads</span>
                    <span><i class='bx bx-show'></i> ${item.views || 0} views</span>
                </div>
            </div>
            <div class="resource-actions">
                <a href="download.php?id=${item.id}" class="btn">Download</a>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', function() {
    loadResults();
});
</script>

</body>
</html>