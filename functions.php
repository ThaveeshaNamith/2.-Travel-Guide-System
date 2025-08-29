<?php
require_once 'config.php';

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to handle file upload
function uploadFile($file, $destinationId, $type = 'destination') {
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed with error code ' . $file['error']];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Only JPG files are allowed'];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => 'File size exceeds 5MB'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . $destinationId . '_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['path' => $destination];
    } else {
        return ['error' => 'Failed to move uploaded file'];
    }
}
?>