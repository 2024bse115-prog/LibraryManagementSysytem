<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null; // 'discussion' or 'reply'
$action = $_POST['action'] ?? null; // 'up' or 'down'

if(!$id || !$type || !$action) {
    echo json_encode(['success' => false]);
    exit();
}

$table = ($type === 'discussion') ? 'discussions' : 'replies';
$change = ($action === 'up') ? '+1' : '-1';

$sql = "UPDATE $table SET votes = votes $change WHERE id = $id";
$conn->query($sql);

// Get updated vote count
$result = $conn->query("SELECT votes FROM $table WHERE id = $id");
$votes = $result->fetch_assoc()['votes'];

header('Content-Type: application/json');
echo json_encode(['success' => true, 'votes' => $votes]);
?>