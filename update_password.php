<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if(isset($_POST['change_password'])){
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password length
    if(strlen($new_password) < 6){
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'New password must be at least 6 characters long!'
        ];
        header('Location: change-password.php');
        exit();
    }

    // Check if new passwords match
    if($new_password !== $confirm_password){
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'New passwords do not match!'
        ];
        header('Location: change-password.php');
        exit();
    }

    // Get current password from database
    $user_result = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $user = $user_result->fetch_assoc();

    // Verify current password
    if(!password_verify($current_password, $user['password'])){
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Current password is incorrect!'
        ];
        header('Location: change-password.php');
        exit();
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";

    if($conn->query($sql)){
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Password changed successfully!'
        ];
        header('Location: profile.php');
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Password update failed: ' . $conn->error
        ];
        header('Location: change-password.php');
    }
} else {
    header('Location: profile.php');
}
exit();
?>