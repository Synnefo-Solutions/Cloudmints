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

$fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Set appropriate content type for viewing
$contentTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'txt' => 'text/plain',
    'sql' => 'text/plain',
    'html' => 'text/html',
    'css' => 'text/css',
    'js' => 'text/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
];

$contentType = $contentTypes[$fileext] ?? 'application/octet-stream';

// Display inline for supported file types
if (isset($contentTypes[$fileext])) {
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
} else {
    // For unsupported types, show a message
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>File Viewer</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
                background: #f1f5f9;
            }
            .message {
                text-align: center;
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .message h2 {
                color: #1e293b;
                margin-bottom: 16px;
            }
            .message p {
                color: #64748b;
                margin-bottom: 24px;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
            }
            .btn:hover {
                background: #5568d3;
            }
        </style>
    </head>
    <body>
        <div class="message">
            <h2>Preview Not Available</h2>
            <p>This file type (.' . $fileext . ') cannot be previewed in the browser.</p>
            <a href="download.php?file=' . urlencode($filename) . '" class="btn">Download File</a>
        </div>
    </body>
    </html>';
}
exit;
?>