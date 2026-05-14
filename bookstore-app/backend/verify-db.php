<?php
/**
 * verify-db.php - Verify database connection and tables
 */

header("Content-Type: application/json; charset=utf-8");

$output = [];

// Test 1: Can we include db.php?
$output['test1_db_include'] = 'attempting...';
try {
    require_once 'db.php';
    $output['test1_db_include'] = 'SUCCESS - db.php included';
} catch (Exception $e) {
    $output['test1_db_include'] = 'FAILED - ' . $e->getMessage();
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// Test 2: Is $conn available?
$output['test2_conn_exists'] = isset($conn) ? 'YES' : 'NO';

// Test 3: Can we get tables list?
$output['test3_tables'] = 'attempting...';
try {
    $result = $conn->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    $output['test3_tables'] = $tables;
} catch (Exception $e) {
    $output['test3_tables'] = 'ERROR - ' . $e->getMessage();
}

// Test 4: Get book_categories_tbl count
$output['test4_book_categories_count'] = 'attempting...';
try {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM book_categories_tbl");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $output['test4_book_categories_count'] = $row ? $row['cnt'] : 0;
} catch (Exception $e) {
    $output['test4_book_categories_count'] = 'ERROR - ' . $e->getMessage();
}

// Test 5: Get categories_tbl count
$output['test5_categories_count'] = 'attempting...';
try {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM categories_tbl");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $output['test5_categories_count'] = $row ? $row['cnt'] : 0;
} catch (Exception $e) {
    $output['test5_categories_count'] = 'ERROR - ' . $e->getMessage();
}

// Test 6: Get books_tbl count
$output['test6_books_count'] = 'attempting...';
try {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM books_tbl");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $output['test6_books_count'] = $row ? $row['cnt'] : 0;
} catch (Exception $e) {
    $output['test6_books_count'] = 'ERROR - ' . $e->getMessage();
}

// Test 7: Get book #3 details
$output['test7_book_3'] = 'attempting...';
try {
    $result = $conn->query("SELECT book_id, title_fld, isbn_fld FROM books_tbl WHERE book_id = 3");
    $book = $result->fetch(PDO::FETCH_ASSOC);
    $output['test7_book_3'] = $book ? $book : 'NOT FOUND';
} catch (Exception $e) {
    $output['test7_book_3'] = 'ERROR - ' . $e->getMessage();
}

// Test 8: Get categories for book #3
$output['test8_categories_for_book_3'] = 'attempting...';
try {
    $result = $conn->query("
        SELECT bc.category_id, c.category_name_fld as category_name
        FROM book_categories_tbl bc
        INNER JOIN categories_tbl c ON bc.category_id = c.category_id
        WHERE bc.book_id = 3
    ");
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
    $output['test8_categories_for_book_3'] = $categories;
} catch (Exception $e) {
    $output['test8_categories_for_book_3'] = 'ERROR - ' . $e->getMessage();
}

echo json_encode($output, JSON_PRETTY_PRINT);
?>
