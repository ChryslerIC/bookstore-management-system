<?php
/**
 * restore-from-db-dump.php - Restore database to match bookstore_db.db schema
 * 
 * Uses the exact schema from bookstore_db.db as ground truth
 * USAGE: Visit http://localhost:8000/backend/restore-from-db-dump.php in browser
 */

require_once 'db.php';

try {
    echo "<h2>Restoring Database from bookstore_db.db Schema...</h2>";

    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "<p>Step 1: Disabled foreign key checks</p>";

    // Drop all dependent tables
    $conn->exec("DROP TABLE IF EXISTS book_categories_tbl");
    $conn->exec("DROP TABLE IF EXISTS books_tbl");
    $conn->exec("DROP TABLE IF EXISTS categories_tbl");
    echo "<p>Step 2: Dropped tables</p>";

    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");

    /**
     * CREATE categories_tbl (exactly as in bookstore_db.db)
     */
    echo "<p>Step 3: Creating categories_tbl...</p>";
    $conn->exec("
        CREATE TABLE categories_tbl (
            category_id INT AUTO_INCREMENT PRIMARY KEY,
            category_name_fld VARCHAR(100) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ Created categories_tbl with category_name_fld column</p>";

    /**
     * CREATE books_tbl (exactly as in bookstore_db.db)
     */
    echo "<p>Step 4: Creating books_tbl...</p>";
    $conn->exec("
        CREATE TABLE books_tbl (
            book_id INT AUTO_INCREMENT PRIMARY KEY,
            title_fld VARCHAR(255) NOT NULL,
            author_fld VARCHAR(255) NOT NULL,
            description_fld TEXT DEFAULT NULL,
            isbn_fld VARCHAR(50) DEFAULT NULL UNIQUE,
            price_fld DECIMAL(10,2) NOT NULL,
            stock_qty_fld INT NOT NULL DEFAULT 0,
            book_cover_image VARCHAR(255),
            book_created_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            book_updated_fld TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ Created books_tbl with correct _fld columns</p>";

    /**
     * CREATE book_categories_tbl (exactly as in bookstore_db.db)
     */
    echo "<p>Step 5: Creating book_categories_tbl...</p>";
    $conn->exec("
        CREATE TABLE book_categories_tbl (
            book_id INT NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY (book_id, category_id),
            KEY idx_bc_category_id (category_id),
            CONSTRAINT fk_bc_book 
                FOREIGN KEY (book_id) REFERENCES books_tbl(book_id) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_bc_category 
                FOREIGN KEY (category_id) REFERENCES categories_tbl(category_id) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "<p>✓ Created book_categories_tbl</p>";

    /**
     * Insert default categories
     */
    echo "<p>Step 6: Inserting categories...</p>";
    $categories = [
        'Fiction',
        'Non-Fiction',
        'Science Fiction',
        'Mystery',
        'Romance',
        'Thriller',
        'Biography',
        'Self-Help',
        'Science',
        'History',
        'Adventure',
        'Children\'s Books',
        'Young Adult',
        'Poetry',
        'Drama'
    ];

    $insertStmt = $conn->prepare("INSERT INTO categories_tbl (category_name_fld) VALUES (:name)");
    foreach ($categories as $category) {
        $insertStmt->execute([':name' => $category]);
    }
    echo "<p>✓ Inserted " . count($categories) . " categories</p>";

    /**
     * Verify
     */
    echo "<p>Step 7: Verifying...</p>";
    
    // Check categories
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories_tbl");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Categories in database: " . $result['count'] . "</p>";

    // Show categories table schema
    echo "<h3>categories_tbl schema:</h3>";
    $stmt = $conn->prepare("DESCRIBE categories_tbl");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td><td>" . $col['Null'] . "</td><td>" . $col['Key'] . "</td></tr>";
    }
    echo "</table>";

    // Show books table schema
    echo "<h3>books_tbl schema:</h3>";
    $stmt = $conn->prepare("DESCRIBE books_tbl");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td><td>" . $col['Null'] . "</td><td>" . $col['Key'] . "</td></tr>";
    }
    echo "</table>";

    // List first 5 categories
    echo "<h3>Sample categories:</h3>";
    $stmt = $conn->prepare("SELECT category_id, category_name_fld FROM categories_tbl LIMIT 5");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cats as $cat) {
        echo "<p>ID: " . $cat['category_id'] . " → " . $cat['category_name_fld'] . "</p>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Database restored successfully!</h3>";
    echo "<p>Database now matches the bookstore_db.db schema exactly.</p>";
    echo "<p><a href='http://localhost:5173/admin/dashboard'>Go back to Admin Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Restore failed:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

?>
