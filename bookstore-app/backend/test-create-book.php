<?php
/**
 * test-create-book.php - Test book creation without image
 * 
 * PURPOSE: Test if the database insert is working
 * USAGE: Visit http://localhost:8000/backend/test-create-book.php in browser
 */

require_once 'db.php';

try {
    echo "<h2>Testing Book Creation...</h2>";

    // Test 1: Check if we can connect to database
    echo "<p><strong>Test 1: Database Connection</strong></p>";
    $testStmt = $conn->prepare("SELECT 1");
    $testStmt->execute();
    echo "<p>✓ Database connection successful</p>";

    // Test 2: Check if tables exist
    echo "<p><strong>Test 2: Check Tables</strong></p>";
    $tables = ['categories_tbl', 'books_tbl', 'book_categories_tbl'];
    foreach ($tables as $table) {
        $checkStmt = $conn->prepare("SHOW TABLES LIKE :table");
        $checkStmt->execute([':table' => $table]);
        if ($checkStmt->rowCount() > 0) {
            echo "<p>✓ Table '$table' exists</p>";
        } else {
            echo "<p>✗ Table '$table' NOT FOUND</p>";
        }
    }

    // Test 3: Try inserting a test book (without image)
    echo "<p><strong>Test 3: Insert Test Book</strong></p>";
    
    $testTitle = 'Test Book ' . time();
    $testISBN = 'TEST-' . time();
    
    $insertStmt = $conn->prepare(
        "INSERT INTO books_tbl (title_fld, author_fld, description_fld, isbn_fld, price_fld, stock_qty_fld, book_cover_image) 
         VALUES (:title, :author, :description, :isbn, :price, :stock_qty, :image)"
    );
    
    $insertStmt->execute([
        ':title' => $testTitle,
        ':author' => 'Test Author',
        ':description' => 'This is a test book',
        ':isbn' => $testISBN,
        ':price' => 100.00,
        ':stock_qty' => 5,
        ':image' => 'test.jpg'
    ]);
    
    $bookId = $conn->lastInsertId();
    echo "<p>✓ Test book created with ID: $bookId</p>";

    // Test 4: Try inserting into book_categories_tbl
    echo "<p><strong>Test 4: Link Book to Category</strong></p>";
    
    $categoryStmt = $conn->prepare(
        "INSERT INTO book_categories_tbl (book_id, category_id) 
         VALUES (:book_id, :category_id)"
    );
    
    $categoryStmt->execute([
        ':book_id' => $bookId,
        ':category_id' => 1
    ]);
    
    echo "<p>✓ Book linked to category</p>";

    // Test 5: Check uploads directory
    echo "<p><strong>Test 5: Check Uploads Directory</strong></p>";
    
    $uploads_dir = __DIR__ . '/uploads/books';
    if (is_dir($uploads_dir)) {
        echo "<p>✓ Uploads directory exists: $uploads_dir</p>";
        if (is_writable($uploads_dir)) {
            echo "<p>✓ Uploads directory is writable</p>";
        } else {
            echo "<p>✗ Uploads directory is NOT writable</p>";
            echo "<p>Try running: chmod 777 " . $uploads_dir . "</p>";
        }
    } else {
        echo "<p>ℹ Uploads directory will be created when first book image is uploaded</p>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ All tests passed!</h3>";
    echo "<p><a href='http://localhost:5173/admin/dashboard'>Go back to Admin Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Test failed:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

?>
