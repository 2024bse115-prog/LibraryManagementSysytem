<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Get all faculties
$faculties = $conn->query("SELECT * FROM faculties ORDER BY name");

// Get courses based on selected faculty (for pre-selection if course_id in URL)
$selected_course = $_GET['course_id'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Resource - Pass.Papers</title>
<link rel="stylesheet" href="styles.css">
<link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header>
<a href="index.php" class="logo">Pass.Papers</a>
<nav>
<a href="index.php">Home</a>
<a href="browse.php">Browse</a>
<a href="upload.php" class="active">Upload</a>
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

<div class="upload-container">
    <?php include __DIR__ . '/partials/flash.php'; ?>
    <div class="upload-card">
        <h2><i class='bx bx-cloud-upload'></i> Upload Resource</h2>
        <p class="upload-subtitle">Share past papers and course materials with fellow students</p>

        <form action="upload_process.php" method="POST" enctype="multipart/form-data" class="upload-form">
            
            <div class="form-group">
                <label>Faculty *</label>
                <select name="faculty_id" id="faculty-select" required>
                    <option value="">Select Faculty</option>
                    <?php while($faculty = $faculties->fetch_assoc()): ?>
                    <option value="<?= $faculty['id'] ?>"><?= htmlspecialchars($faculty['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Course *</label>
                <select name="course_id" id="course-select" required>
                    <option value="">Select Faculty First</option>
                </select>
            </div>

            <div class="form-group">
                <label>Resource Title *</label>
                <input type="text" name="title" placeholder="e.g., 2024 Final Exam Paper" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Brief description of the resource (optional)"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Year *</label>
                    <select name="year" required>
                        <option value="">Select Year</option>
                        <?php for($y = date('Y'); $y >= 2015; $y--): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Semester *</label>
                    <select name="semester" required>
                        <option value="">Select Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Summer</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Upload File (PDF only) *</label>
                <div class="file-upload-area" id="fileUploadArea">
                    <input type="file" name="resource_file" id="resourceFile" accept=".pdf" required>
                    <div class="file-upload-content">
                        <i class='bx bx-upload'></i>
                        <p>Click to browse or drag and drop your PDF file here</p>
                        <span class="file-size-info">Maximum file size: 10MB</span>
                    </div>
                    <div class="file-preview" id="filePreview" style="display: none;">
                        <i class='bx bxs-file-pdf'></i>
                        <span class="file-name"></span>
                        <button type="button" class="remove-file" id="removeFile">&times;</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="anonymous" value="1">
                    Upload anonymously (your name won't be displayed)
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class='bx bx-cloud-upload'></i> Upload Resource
            </button>
        </form>
    </div>
</div>

<script src="script.js"></script>
<script>
// Load courses based on selected faculty
document.getElementById('faculty-select').addEventListener('change', function() {
    const facultyId = this.value;
    const courseSelect = document.getElementById('course-select');
    
    if(!facultyId) {
        courseSelect.innerHTML = '<option value="">Select Faculty First</option>';
        return;
    }
    
    courseSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch('get_courses.php?faculty_id=' + facultyId)
        .then(function(response) {
            return response.json();
        })
        .then(function(courses) {
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            courses.forEach(function(course) {
                courseSelect.innerHTML += '<option value="' + course.id + '">' + course.course_code + ' - ' + course.course_name + '</option>';
            });
        })
        .catch(function(error) {
            console.error('Error loading courses:', error);
            courseSelect.innerHTML = '<option value="">Error loading courses</option>';
        });
});

// File upload preview
const fileInput = document.getElementById('resourceFile');
const fileUploadArea = document.getElementById('fileUploadArea');
const filePreview = document.getElementById('filePreview');
const removeFileBtn = document.getElementById('removeFile');

if(fileInput) {
    fileInput.addEventListener('change', function(e) {
        if(this.files.length > 0) {
            const file = this.files[0];
            fileUploadArea.querySelector('.file-upload-content').style.display = 'none';
            filePreview.style.display = 'flex';
            filePreview.querySelector('.file-name').textContent = file.name;
        }
    });
}

if(removeFileBtn) {
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        fileUploadArea.querySelector('.file-upload-content').style.display = 'block';
        filePreview.style.display = 'none';
    });
}
</script>
</body>
</html>