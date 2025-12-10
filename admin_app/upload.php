<?php
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Debug: Check if file was uploaded
    if (!isset($_FILES['file'])) {
        $response['message'] = 'No file field in POST request';
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    if ($_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        $response['message'] = 'No file selected. Please choose a file to upload.';
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    $file = $_FILES['file'];

    // Get settings from .env
    $maxFileSize = env('MAX_FILE_SIZE', 10485760); // 10MB default
    $allowedExtensions = explode(',', env('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip,sql,php'));

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileSize = $file['size'];

    // Detailed error checking
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            // No error, continue processing
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $response['message'] = 'File too large. Maximum size: ' . round($maxFileSize/1024/1024) . 'MB';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response['message'] = 'File upload was interrupted. Please try again.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response['message'] = 'Server error: Missing temporary upload directory';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response['message'] = 'Server error: Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response['message'] = 'Server error: A PHP extension stopped the upload';
            break;
        default:
            $response['message'] = 'Unknown upload error (code: ' . $file['error'] . ')';
            break;
    }

    // If there was an upload error, stop here
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    // Validate file extension
    if (!in_array($fileExt, $allowedExtensions)) {
        $response['message'] = 'File type .' . $fileExt . ' not allowed. Allowed: ' . implode(', ', $allowedExtensions);
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    // Validate file size
    if ($fileSize > $maxFileSize) {
        $response['message'] = 'File too large (' . formatFileSize($fileSize) . '). Maximum: ' . round($maxFileSize/1024/1024) . 'MB';
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    // Prepare upload directory
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $response['message'] = 'Server error: Could not create uploads directory';
            $_SESSION['upload_message'] = $response['message'];
            $_SESSION['upload_success'] = false;
            header('Location: dashboard.php');
            exit;
        }
        chmod($uploadDir, 0777);
    }

    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        $response['message'] = 'Server error: Uploads directory is not writable. Run: chmod 777 uploads/';
        $_SESSION['upload_message'] = $response['message'];
        $_SESSION['upload_success'] = false;
        header('Location: dashboard.php');
        exit;
    }

    // Generate safe filename
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $uploadPath = $uploadDir . '/' . $safeName;

    // Check if file already exists
    if (file_exists($uploadPath)) {
        $safeName = time() . '_' . $safeName;
        $uploadPath = $uploadDir . '/' . $safeName;
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Success! Add to session
        if (!isset($_SESSION['uploaded_files'])) {
            $_SESSION['uploaded_files'] = [];
        }

        // Add new file to the beginning of array
        array_unshift($_SESSION['uploaded_files'], [
            'id' => time(),
            'name' => $safeName,
            'type' => strtoupper($fileExt),
            'size' => formatFileSize($fileSize),
            'uploaded' => 'Just now',
            'status' => 'Active',
            'path' => $uploadPath
        ]);

        $response['success'] = true;
        $response['message'] = '✓ File uploaded successfully: ' . htmlspecialchars($safeName);
    } else {
        $response['message'] = 'Server error: Failed to save uploaded file. Check PHP error logs and directory permissions.';
    }

} else {
    $response['message'] = 'Invalid request method';
}

// Helper function
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Store message in session
$_SESSION['upload_message'] = $response['message'];
$_SESSION['upload_success'] = $response['success'];

// Redirect back to dashboard
header('Location: dashboard.php');
exit;
?>