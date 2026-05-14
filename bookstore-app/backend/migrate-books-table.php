<?php
/**
 * migrate-books-table.php - Fix books_tbl schema
 * 
 * Drops the old books_tbl and recreates it with the correct column names
 * USAGE: Visit http://localhost:8000/backend/migrate-books-table.php in browser
 */

require_once 'db.php';

try {
    echo "<h2>Migrating books_tbl Schema...</h2>";

    // Temporarily disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "<p>Disabled foreign key checks temporarily</p>";

    // Step 1: Drop existing book_categories_tbl (depends on books_tbl)
    echo "<p>Step 1: Removing book_categories_tbl...</p>";
    $conn->exec("DROP TABLE IF EXISTS book_categories_tbl");
    echo "<p>✓ Dropped book_categories_tbl</p>";

    // Step 2: Drop existing books_tbl
    echo "<p>Step 2: Removing old books_tbl...</p>";
    $conn->exec("DROP TABLE IF EXISTS books_tbl");
    echo "<p>✓ Dropped books_tbl</p>";

    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "<p>Re-enabled foreign key checks</p>";

    // Step 3: Create new books_tbl with correct schema
    echo "<p>Step 3: Creating new books_tbl with correct schema...</p>";
    $conn->exec("
        CREATE TABLE books_tbl (
            book_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            isbn VARCHAR(20) NOT NULL UNIQUE,
            price DECIMAL(10, 2) NOT NULL,
            stock_quantity INT NOT NULL DEFAULT 0,
            book_cover_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✓ Created new books_tbl with correct schema</p>";

    // Step 4: Recreate book_categories_tbl
    echo "<p>Step 4: Recreating book_categories_tbl...</p>";
    $conn->exec("
        CREATE TABLE book_categories_tbl (
            book_category_id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books_tbl(book_id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories_tbl(category_id) ON DELETE CASCADE,
            UNIQUE KEY unique_book_category (book_id, category_id)
        )
    ");
    echo "<p>✓ Recreated book_categories_tbl</p>";

    // Step 5: Verify new schema
    echo "<p>Step 5: Verifying new schema...</p>";
    $stmt = $conn->prepare("DESCRIBE books_tbl");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Migration completed successfully!</h3>";
    echo "<p>The books_tbl now has the correct schema with these columns:</p>";
    echo "<ul>";
    echo "<li>book_id (PRIMARY KEY)</li>";
    echo "<li>title</li>";
    echo "<li>description</li>";
    echo "<li>isbn (UNIQUE)</li>";
    echo "<li>price</li>";
    echo "<li>stock_quantity</li>";
    echo "<li>book_cover_image</li>";
    echo "<li>created_at</li>";
    echo "<li>updated_at</li>";
    echo "</ul>";
    echo "<p><a href='http://localhost:5173/admin/dashboard'>Go back to Admin Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Migration failed:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

?>
