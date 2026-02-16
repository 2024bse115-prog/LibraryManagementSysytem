<?php
require_once 'config.php';

header('Content-Type: application/json');

if(isset($_GET['faculty_id'])) {
    $faculty_id = $conn->real_escape_string($_GET['faculty_id']);
    
    $result = $conn->query("SELECT id, course_code, course_name FROM courses WHERE faculty_id = '$faculty_id' ORDER BY course_name");
    
    $courses = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    
    echo json_encode($courses);
} else {
    echo json_encode([]);
}
?>