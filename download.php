<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$resource_id = $_GET['id'] ?? null;

if(!$resource_id) {
    header('Location: index.php');
    exit();
}

// Get resource info (use prepared statement and enforce integer id)
$resource_id = (int)$resource_id;
$stmt = $conn->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();
$resource = $result->fetch_assoc();
$stmt->close();

if(!$resource) {
    $_SESSION['alerts'][] = [
        'type' => 'error',
        'message' => 'Resource not found!'
    ];
    header('Location: index.php');
    exit();
}

$file_path = $resource['file_path'];

if(file_exists($file_path)) {
    // Increment download count (optional - add downloads column to resources table)
    // $conn->query("UPDATE resources SET downloads = downloads + 1 WHERE id = '$resource_id'");

    // Clean output buffers to avoid corrupting the file stream
    if (function_exists('ob_get_level')) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    // Detect content type (fallback to PDF)
    $mimeType = function_exists('mime_content_type') ? mime_content_type($file_path) : 'application/pdf';
    if ($mimeType === false) {
        $mimeType = 'application/pdf';
    }

    // Safe filename for download; fall back to actual file basename
    $downloadName = basename($file_path);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Cache-Control: private');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
    exit();
} else {
    $_SESSION['alerts'][] = [
        'type' => 'error',
        'message' => 'File not found on server!'
    ];
    header('Location: index.php');
    exit();
}
?>