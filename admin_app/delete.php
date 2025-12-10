<?php
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file specified']);
    exit;
}

$filename = basename($_POST['file']); // Security: prevent directory traversal
$filepath = __DIR__ . '/uploads/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

// Delete the file
if (unlink($filepath)) {
    echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete file']);
}
exit;
?>