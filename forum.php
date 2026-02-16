<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Get all discussions
$discussions = $conn->query("SELECT d.*, c.course_code, c.course_name, u.name as author_name, 
                             (SELECT COUNT(*) FROM replies WHERE discussion_id = d.id) as reply_count
                             FROM discussions d 
                             JOIN courses c ON d.course_id = c.id 
                             JOIN users u ON d.user_id = u.id 
                             ORDER BY d.created_at DESC");

// Get all courses for dropdown
$courses = $conn->query("SELECT c.*, f.name as faculty_name FROM courses c JOIN faculties f ON c.faculty_id = f.id ORDER BY f.name, c.course_name");
?>

<!DOCTYPE html>
<html>
<head>
<title>Community Forum - Pass.Papers</title>
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
<a href="forum.php" class="active">Forum</a>
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

<div class="forum-container">
    <div class="forum-header">
        <h1><i class='bx bx-message-square-dots'></i> Community Forum</h1>
        <p>Ask questions, share knowledge, and collaborate with fellow students</p>
        <button class="btn-new-discussion" id="newDiscussionBtn">
            <i class='bx bx-plus'></i> New Discussion
        </button>
    </div>

    <div class="discussions-list">
        <?php while($discussion = $discussions->fetch_assoc()): ?>
        <div class="discussion-item">
            <div class="discussion-votes">
                <button class="vote-btn upvote" data-id="<?= $discussion['id'] ?>" data-type="discussion">
                    <i class='bx bxs-up-arrow'></i>
                </button>
                <span class="vote-count"><?= $discussion['votes'] ?></span>
                <button class="vote-btn downvote" data-id="<?= $discussion['id'] ?>" data-type="discussion">
                    <i class='bx bxs-down-arrow'></i>
                </button>
            </div>
            <div class="discussion-content">
                <a href="discussion.php?id=<?= $discussion['id'] ?>" class="discussion-title">
                    <?= htmlspecialchars($discussion['title']) ?>
                </a>
                <p class="discussion-preview"><?= htmlspecialchars(substr($discussion['content'], 0, 150)) ?>...</p>
                <div class="discussion-meta">
                    <span class="course-tag"><?= htmlspecialchars($discussion['course_code']) ?></span>
                    <span><i class='bx bx-user'></i> <?= htmlspecialchars($discussion['author_name']) ?></span>
                    <span><i class='bx bx-time'></i> <?= date('M d, Y', strtotime($discussion['created_at'])) ?></span>
                    <span><i class='bx bx-message'></i> <?= $discussion['reply_count'] ?> replies</span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- New Discussion Modal -->
<div class="modal" id="discussionModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Start a New Discussion</h2>
        <form action="forum_process.php" method="POST">
            <div class="form-group">
                <label>Course *</label>
                <select name="course_id" required>
                    <option value="">Select Course</option>
                    <?php while($course = $courses->fetch_assoc()): ?>
                    <option value="<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['faculty_name']) ?> - <?= htmlspecialchars($course['course_code']) ?> (<?= htmlspecialchars($course['course_name']) ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="What's your question?" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="content" rows="6" placeholder="Provide details about your question..." required></textarea>
            </div>
            <button type="submit" name="create_discussion" class="btn-submit">Post Discussion</button>
        </form>
    </div>
</div>

<script src="script.js"></script>
<script>
// Modal handling
var modal = document.getElementById('discussionModal');
var btn = document.getElementById('newDiscussionBtn');
var span = document.querySelector('.close-modal');

btn.onclick = function() {
    modal.style.display = 'flex';
}

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if(event.target == modal) {
        modal.style.display = 'none';
    }
}

// Voting system
document.querySelectorAll('.vote-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var type = this.getAttribute('data-type');
        var action = this.classList.contains('upvote') ? 'up' : 'down';
        var voteCountElement = this.parentElement.querySelector('.vote-count');
        
        fetch('vote.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&type=' + type + '&action=' + action
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if(data.success) {
                voteCountElement.textContent = data.votes;
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
    });
});
</script>
</body>
</html>