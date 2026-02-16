<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is admin
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$user_result = $conn->query("SELECT role FROM users WHERE id = {$_SESSION['user_id']}");
$user = $user_result->fetch_assoc();

if($user['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Not authorized - Admin only']);
    exit();
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if($action == 'delete_resource' && $id > 0) {
    // Get file path before deleting
    $resource = $conn->query("SELECT file_path FROM resources WHERE id = $id");
    
    if($resource->num_rows > 0) {
        $res = $resource->fetch_assoc();
        
        // Delete file if exists
        if($res['file_path'] && file_exists($res['file_path'])) {
            unlink($res['file_path']);
        }
        
        // Delete from database
        if($conn->query("DELETE FROM resources WHERE id = $id")) {
            echo json_encode(['success' => true, 'message' => 'Resource deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
    }
}

if($action == 'delete_user' && $id > 0) {
    // Don't allow deleting yourself
    if($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete yourself']);
        exit();
    }
    
    // Delete user's uploads first
    $resources = $conn->query("SELECT file_path FROM resources WHERE uploader_id = $id");
    while($res = $resources->fetch_assoc()) {
        if($res['file_path'] && file_exists($res['file_path'])) {
            unlink($res['file_path']);
        }
    }
    
    // Delete user
    if($conn->query("DELETE FROM users WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'User deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>