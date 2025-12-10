<?php
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['file'])) {
    die('No file specified');
}

$filename = basename($_GET['file']); // Security: prevent directory traversal
$filepath = __DIR__ . '/uploads/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    die('File not found');
}

// Force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($filepath);
exit;
?>