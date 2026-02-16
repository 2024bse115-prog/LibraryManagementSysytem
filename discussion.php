<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];
$discussion_id = $_GET['id'] ?? null;

if(!$discussion_id) {
    header('Location: forum.php');
    exit();
}

// Get discussion details
$discussion_result = $conn->query("SELECT d.*, c.course_code, c.course_name, u.name as author_name 
                                   FROM discussions d 
                                   JOIN courses c ON d.course_id = c.id 
                                   JOIN users u ON d.user_id = u.id 
                                   WHERE d.id = $discussion_id");

if($discussion_result->num_rows == 0) {
    header('Location: forum.php');
    exit();
}

$discussion = $discussion_result->fetch_assoc();

// Get replies
$replies = $conn->query("SELECT r.*, u.name as author_name 
                         FROM replies r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.discussion_id = $discussion_id 
                         ORDER BY r.is_best_answer DESC, r.votes DESC, r.created_at ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($discussion['title']) ?> - Pass.Papers</title>
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

<div class="discussion-container">
    <a href="forum.php" class="back-btn"><i class='bx bx-arrow-back'></i> Back to Forum</a>
    
    <div class="discussion-detail">
        <div class="discussion-votes">
            <button class="vote-btn upvote" data-id="<?= $discussion['id'] ?>" data-type="discussion">
                <i class='bx bxs-up-arrow'></i>
            </button>
            <span class="vote-count"><?= $discussion['votes'] ?></span>
            <button class="vote-btn downvote" data-id="<?= $discussion['id'] ?>" data-type="discussion">
                <i class='bx bxs-down-arrow'></i>
            </button>
        </div>
        <div class="discussion-body">
            <h1><?= htmlspecialchars($discussion['title']) ?></h1>
            <div class="discussion-meta">
                <span class="course-tag"><?= htmlspecialchars($discussion['course_code']) ?> - <?= htmlspecialchars($discussion['course_name']) ?></span>
                <span><i class='bx bx-user'></i> <?= htmlspecialchars($discussion['author_name']) ?></span>
                <span><i class='bx bx-time'></i> <?= date('M d, Y H:i', strtotime($discussion['created_at'])) ?></span>
            </div>
            <div class="discussion-content">
                <?= nl2br(htmlspecialchars($discussion['content'])) ?>
            </div>
        </div>
    </div>

    <div class="replies-section">
        <h2><?= $replies->num_rows ?> Replies</h2>
        
        <?php while($reply = $replies->fetch_assoc()): ?>
        <div class="reply-item <?= $reply['is_best_answer'] ? 'best-answer' : '' ?>">
            <div class="reply-votes">
                <button class="vote-btn upvote" data-id="<?= $reply['id'] ?>" data-type="reply">
                    <i class='bx bxs-up-arrow'></i>
                </button>
                <span class="vote-count"><?= $reply['votes'] ?></span>
                <button class="vote-btn downvote" data-id="<?= $reply['id'] ?>" data-type="reply">
                    <i class='bx bxs-down-arrow'></i>
                </button>
            </div>
            <div class="reply-body">
                <?php if($reply['is_best_answer']): ?>
                <div class="best-answer-badge">
                    <i class='bx bxs-check-circle'></i> Best Answer
                </div>
                <?php endif; ?>
                <div class="reply-author">
                    <div class="avatar-small"><?= strtoupper($reply['author_name'][0]) ?></div>
                    <div>
                        <strong><?= htmlspecialchars($reply['author_name']) ?></strong>
                        <span class="reply-time"><?= date('M d, Y H:i', strtotime($reply['created_at'])) ?></span>
                    </div>
                </div>
                <div class="reply-content">
                    <?= nl2br(htmlspecialchars($reply['content'])) ?>
                </div>
                <?php if($discussion['user_id'] == $user_id && !$reply['is_best_answer']): ?>
                <button class="mark-best-btn" data-reply-id="<?= $reply['id'] ?>">
                    <i class='bx bx-check-circle'></i> Mark as Best Answer
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="reply-form-section">
        <h3>Your Answer</h3>
        <form action="forum_process.php" method="POST">
            <input type="hidden" name="discussion_id" value="<?= $discussion_id ?>">
            <textarea name="content" rows="6" placeholder="Write your answer here..." required></textarea>
            <button type="submit" name="create_reply" class="btn-submit">
                <i class='bx bx-send'></i> Post Answer
            </button>
        </form>
    </div>
</div>

<script src="script.js"></script>
<script>
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

// Mark as best answer
document.querySelectorAll('.mark-best-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var replyId = this.getAttribute('data-reply-id');
        
        if(confirm('Mark this as the best answer?')) {
            fetch('forum_process.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'mark_best_answer=1&reply_id=' + replyId
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark as best answer');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    });
});
</script>
</body>
</html>