<?php
/**
 * get-book-image.php - Image Proxy Endpoint
 * 
 * PURPOSE: Serve book cover images with proper CORS headers to avoid browser blocking
 * - Prevents CORS errors when loading images from uploads folder
 * - Sets proper headers for image delivery
 * - Validates filename to prevent directory traversal attacks
 */

/**
 * CORS HEADERS - Must be sent BEFORE any other output
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get filename from query parameter
$filename = isset($_GET['file']) ? basename($_GET['file']) : null;

if (!$filename) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Filename parameter required']);
    exit;
}

// Construct file path - use basename to prevent directory traversal
$filepath = __DIR__ . '/uploads/books/' . basename($filename);

// Check if file exists before resolving the real path. Missing files should be 404, not 403.
if (!file_exists($filepath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Image not found']);
    exit;
}

// Security check - ensure file is within uploads/books directory
$realpath = realpath($filepath);
$uploadsDir = realpath(__DIR__ . '/uploads/books/');

if (!$realpath || strpos($realpath, $uploadsDir) !== 0) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Determine MIME type without requiring the fileinfo extension
$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$mimeMap = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'bmp' => 'image/bmp',
];

$mime = $mimeMap[$extension] ?? 'application/octet-stream';

// Set headers for image delivery
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
header('Content-Disposition: inline; filename="' . basename($filename) . '"');

// Send file
readfile($filepath);
exit;
