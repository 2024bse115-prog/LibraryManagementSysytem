<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if(isset($_POST['update_profile'])){
    $user_id = $_SESSION['user_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $faculty = $conn->real_escape_string($_POST['faculty']);
    $course = $conn->real_escape_string(trim($_POST['course']));
    $year = $conn->real_escape_string($_POST['year']);

    // Check if email already exists for another user
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
    
    if($check_email->num_rows > 0){
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Email is already used by another account!'
        ];
        header('Location: edit-profile.php');
        exit();
    }

    // Update user profile
    $sql = "UPDATE users SET 
            name = '$name', 
            email = '$email', 
            faculty = '$faculty', 
            course = '$course', 
            year_of_study = '$year' 
            WHERE id = $user_id";

    if($conn->query($sql)){
        // Update session name
        $_SESSION['name'] = $name;
        
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Profile updated successfully!'
        ];
        header('Location: profile.php');
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Update failed: ' . $conn->error
        ];
        header('Location: edit-profile.php');
    }
} else {
    header('Location: profile.php');
}
exit();
?>