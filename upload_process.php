<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_POST['course_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $conn->real_escape_string($_POST['course_id']);
$title = $conn->real_escape_string($_POST['title']);
$description = $conn->real_escape_string($_POST['description']);
$year = $conn->real_escape_string($_POST['year']);
$semester = $conn->real_escape_string($_POST['semester']);
$anonymous = isset($_POST['anonymous']) ? 1 : 0;

// Get uploader name
$uploader_name = $anonymous ? 'Anonymous' : $_SESSION['name'];

// Handle file upload
if(isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
    $file = $_FILES['resource_file'];
    
    // Validate file type
    $allowed = ['pdf'];
    $filename = $file['name'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed)) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Only PDF files are allowed!'
        ];
        header('Location: upload.php');
        exit();
    }
    
    // Validate file size (10MB max)
    if($file['size'] > 10485760) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'File size must be less than 10MB!'
        ];
        header('Location: upload.php');
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/';
    if(!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if(move_uploaded_file($file['tmp_name'], $file_path)) {
        // Insert into database - FIXED: using file_path instead of file_name
        $sql = "INSERT INTO resources (course_id, title, description, file_path, file_type, uploader_id, uploader_name, year, semester) 
                VALUES ('$course_id', '$title', '$description', '$file_path', '$file_ext', '$user_id', '$uploader_name', '$year', '$semester')";
        
        if($conn->query($sql)) {
            $_SESSION['alerts'][] = [
                'type' => 'success',
                'message' => 'Resource uploaded successfully!'
            ];
            header('Location: browse.php?course_id=' . $course_id);
        } else {
            $_SESSION['alerts'][] = [
                'type' => 'error',
                'message' => 'Database error: ' . $conn->error
            ];
            // Delete the uploaded file since database insert failed
            unlink($file_path);
            header('Location: upload.php');
        }
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Failed to upload file!'
        ];
        header('Location: upload.php');
    }
} else {
    $_SESSION['alerts'][] = [
        'type' => 'error',
        'message' => 'Please select a file to upload!'
    ];
    header('Location: upload.php');
}

exit();
?>