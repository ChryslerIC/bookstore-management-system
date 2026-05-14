<?php
/**
 * debug-categories.php - Debug endpoint for category fetching
 * This helps diagnose issues with the get-book-categories.php endpoint
 */

header("Content-Type: application/json; charset=utf-8");

try {
    require_once 'db.php';
    
    $debug = [];
    
    // Test 1: Check if book_categories_tbl exists
    $debug['test1_table_exists'] = 'checking...';
    try {
        $stmt = $conn->prepare("SELECT 1 FROM book_categories_tbl LIMIT 1");
        $stmt->execute();
        $debug['test1_table_exists'] = 'YES - book_categories_tbl exists';
    } catch (Exception $e) {
        $debug['test1_table_exists'] = 'NO - ' . $e->getMessage();
    }
    
    // Test 2: Check if categories_tbl exists
    $debug['test2_categories_table'] = 'checking...';
    try {
        $stmt = $conn->prepare("SELECT 1 FROM categories_tbl LIMIT 1");
        $stmt->execute();
        $debug['test2_categories_table'] = 'YES - categories_tbl exists';
    } catch (Exception $e) {
        $debug['test2_categories_table'] = 'NO - ' . $e->getMessage();
    }
    
    // Test 3: Count records in book_categories_tbl
    $debug['test3_book_categories_count'] = 'checking...';
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM book_categories_tbl");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['test3_book_categories_count'] = $result['cnt'] . ' records';
    } catch (Exception $e) {
        $debug['test3_book_categories_count'] = 'ERROR - ' . $e->getMessage();
    }
    
    // Test 4: Count records in categories_tbl
    $debug['test4_categories_count'] = 'checking...';
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM categories_tbl");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['test4_categories_count'] = $result['cnt'] . ' records';
    } catch (Exception $e) {
        $debug['test4_categories_count'] = 'ERROR - ' . $e->getMessage();
    }
    
    // Test 5: Try to fetch categories for book_id=3
    $debug['test5_fetch_book_3'] = 'checking...';
    try {
        $stmt = $conn->prepare("
            SELECT bc.category_id, c.category_name
            FROM book_categories_tbl bc
            INNER JOIN categories_tbl c ON bc.category_id = c.category_id
            WHERE bc.book_id = :book_id
            ORDER BY c.category_name
        ");
        $stmt->execute([':book_id' => 3]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $debug['test5_fetch_book_3'] = 'SUCCESS - ' . count($categories) . ' categories found';
        $debug['test5_categories'] = $categories;
    } catch (Exception $e) {
        $debug['test5_fetch_book_3'] = 'ERROR - ' . $e->getMessage();
    }
    
    // Test 6: List all tables
    $debug['test6_tables'] = 'checking...';
    try {
        $stmt = $conn->prepare("SELECT name FROM sqlite_master WHERE type='table'");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $debug['test6_tables'] = $tables;
    } catch (Exception $e) {
        $debug['test6_tables'] = 'ERROR - ' . $e->getMessage();
    }
    
    http_response_code(200);
    echo json_encode($debug, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>
