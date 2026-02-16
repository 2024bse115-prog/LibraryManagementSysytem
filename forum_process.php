<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Create new discussion
if(isset($_POST['create_discussion'])) {
    $course_id = $conn->real_escape_string($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    
    $sql = "INSERT INTO discussions (course_id, user_id, title, content) VALUES ('$course_id', '$user_id', '$title', '$content')";
    
    if($conn->query($sql)) {
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Discussion created successfully!'
        ];
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Failed to create discussion!'
        ];
    }
    
    header('Location: forum.php');
    exit();
}

// Create reply
if(isset($_POST['create_reply'])) {
    $discussion_id = $conn->real_escape_string($_POST['discussion_id']);
    $content = $conn->real_escape_string($_POST['content']);
    
    $sql = "INSERT INTO replies (discussion_id, user_id, content) VALUES ('$discussion_id', '$user_id', '$content')";
    
    if($conn->query($sql)) {
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Reply posted successfully!'
        ];
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Failed to post reply!'
        ];
    }
    
    header('Location: discussion.php?id=' . $discussion_id);
    exit();
}

// Mark as best answer
if(isset($_POST['mark_best_answer'])) {
    $reply_id = $conn->real_escape_string($_POST['reply_id']);
    
    // First, remove any existing best answer for this discussion
    $get_discussion = $conn->query("SELECT discussion_id FROM replies WHERE id = $reply_id");
    $discussion_id = $get_discussion->fetch_assoc()['discussion_id'];
    
    $conn->query("UPDATE replies SET is_best_answer = 0 WHERE discussion_id = $discussion_id");
    
    // Then mark this reply as best answer
    $conn->query("UPDATE replies SET is_best_answer = 1 WHERE id = $reply_id");
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}
?>