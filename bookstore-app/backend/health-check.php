<?php
/**
 * health-check.php - Health check and diagnostics endpoint
 * 
 * PURPOSE: Tests database connection and table structure
 * - Verifies database is accessible
 * - Checks required tables exist
 * - Returns diagnostics information
 * 
 * ENDPOINT: GET /backend/health-check.php
 * RESPONSE: {status: string, details: object}
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$diagnostics = [
    'status' => 'unknown',
    'database' => ['status' => 'unknown'],
    'tables' => ['status' => 'unknown'],
    'categories' => ['status' => 'unknown']
];

try {
    require_once 'db.php';
    
    $diagnostics['database']['status'] = 'connected';
    
    // Check categories table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories_tbl");
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $diagnostics['tables']['status'] = 'accessible';
        $diagnostics['categories']['status'] = 'ok';
        $diagnostics['categories']['count'] = $result['count'] ?? 0;
        $diagnostics['categories']['message'] = $result['count'] > 0 
            ? "Found {$result['count']} categories" 
            : "No categories found - run init-categories.php";
    } else {
        $diagnostics['tables']['status'] = 'error';
        $diagnostics['tables']['message'] = 'Failed to query categories_tbl';
    }
    
    $stmt = $conn->query("SHOW TABLES");
    if ($stmt) {
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $diagnostics['tables']['list'] = $tables;
    }
    
    $diagnostics['status'] = 'healthy';
    http_response_code(200);
    
} catch (PDOException $e) {
    $diagnostics['status'] = 'error';
    $diagnostics['database']['status'] = 'connection_failed';
    $diagnostics['database']['error'] = $e->getMessage();
    http_response_code(500);
    
} catch (Exception $e) {
    $diagnostics['status'] = 'error';
    $diagnostics['error'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($diagnostics);
?>
