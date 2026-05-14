<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$fullPath = __DIR__ . $requestPath;

if ($requestPath !== '/' && file_exists($fullPath) && !is_dir($fullPath)) {
    return false;
}

if (strpos($requestPath, '/api/') === 0 || $requestPath === '/api') {
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
