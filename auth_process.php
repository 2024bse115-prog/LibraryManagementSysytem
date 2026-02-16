<?php 
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once 'config.php';

// Simple file logger (rotates at ~2MB)
function write_log($message) {
    $logFile = __DIR__ . DIRECTORY_SEPARATOR . 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown-ip';
    $line = "[$timestamp] [$ip] $message" . PHP_EOL;

    // Rotate if file is large
    if (file_exists($logFile) && filesize($logFile) > 2 * 1024 * 1024) { // 2MB
        $archive = __DIR__ . DIRECTORY_SEPARATOR . 'debug-' . date('Ymd-His') . '.log';
        @rename($logFile, $archive);
    }

    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

if(isset($_POST['register_btn'])){
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $faculty = $conn->real_escape_string($_POST['faculty']);
    $course = $conn->real_escape_string(trim($_POST['course']));
    $year = $conn->real_escape_string($_POST['year']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_email = $conn->query("SELECT email FROM users WHERE email = '$email'");
    
    if($check_email === false) {
        die("Query error: " . $conn->error);
    }
    
    if($check_email->num_rows > 0){
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Email is already registered!'
        ];
        $_SESSION['active_form'] = 'register';
    } else {
        $sql = "INSERT INTO users (name, email, password, faculty, course, year_of_study) 
                VALUES ('$name', '$email', '$password', '$faculty', '$course', '$year')";
        
        if($conn->query($sql)){
            $_SESSION['alerts'][] = [
                'type' => 'success',
                'message' => 'Registration successful! Please login.'
            ];
            $_SESSION['active_form'] = 'login';
        } else {
            $_SESSION['alerts'][] = [
                'type' => 'error',
                'message' => 'Registration failed: ' . $conn->error
            ];
            $_SESSION['active_form'] = 'register';
        }
    }

    // Log registration attempt (no passwords)xpx\
    write_log("REGISTER attempt: email=$email, result=" . end($_SESSION['alerts'])['type']);

    header('Location: index.php');
    exit();
}

if(isset($_POST['login_btn'])){
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    
    if($result === false) {
        die("Query error: " . $conn->error);
    }
    
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        
        if(password_verify($password, $user['password'])){
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['alerts'][] = [
                'type' => 'success',
                'message' => 'Login successful! Welcome back.'
            ];
        } else {
            $_SESSION['alerts'][] = [
                'type' => 'error',
                'message' => 'Incorrect password!'
            ];
            $_SESSION['active_form'] = 'login';
        }
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Email not found!'
        ];
        $_SESSION['active_form'] = 'login';
    }
    
    // Log login attempt (no passwords)
    $last = end($_SESSION['alerts']);
    write_log("LOGIN attempt: email=$email, result={$last['type']}");
    
    header('Location: index.php');
    exit();
}

// If no button was clicked
write_log("AUTH no-button route hit. POST keys: " . implode(',', array_keys($_POST)));
header('Location: index.php');
exit();
?>