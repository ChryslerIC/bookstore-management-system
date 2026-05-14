<?php
/**
 * full-migration.php - Complete database schema fix
 * 
 * Fixes both books_tbl and categories_tbl with correct schemas and resets AUTO_INCREMENT
 * USAGE: Visit http://localhost:8000/backend/full-migration.php in browser
 */

require_once 'db.php';

try {
    echo "<h2>Complete Database Schema Migration...</h2>";

    // Disable foreign key checks for safe drops
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "<p>Step 1: Disabled foreign key checks temporarily</p>";

    // Drop all dependent tables
    $conn->exec("DROP TABLE IF EXISTS book_categories_tbl");
    $conn->exec("DROP TABLE IF EXISTS books_tbl");
    $conn->exec("DROP TABLE IF EXISTS categories_tbl");
    echo "<p>Step 2: Dropped all existing tables</p>";

    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");

    // Create categories_tbl with correct schema
    echo "<p>Step 3: Creating categories_tbl...</p>";
    $conn->exec("
        CREATE TABLE categories_tbl (
            category_id INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✓ Created categories_tbl</p>";

    // Create books_tbl with correct schema
    echo "<p>Step 4: Creating books_tbl...</p>";
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
    echo "<p>✓ Created books_tbl</p>";

    // Create book_categories_tbl
    echo "<p>Step 5: Creating book_categories_tbl...</p>";
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
    echo "<p>✓ Created book_categories_tbl</p>";

    // Insert default categories
    echo "<p>Step 6: Inserting default categories...</p>";
    $defaultCategories = [
        'Fiction',
        'Non-Fiction',
        'Science Fiction',
        'Mystery',
        'Romance',
        'Thriller',
        'Biography',
        'Self-Help',
        'Science',
        'History'
    ];

    $insertStmt = $conn->prepare("INSERT INTO categories_tbl (category_name) VALUES (:name)");
    foreach ($defaultCategories as $category) {
        $insertStmt->execute([':name' => $category]);
    }
    echo "<p>✓ Inserted " . count($defaultCategories) . " default categories</p>";

    // Verify schema
    echo "<p>Step 7: Verifying schema...</p>";
    echo "<h3>categories_tbl structure:</h3>";
    $stmt = $conn->prepare("DESCRIBE categories_tbl");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    echo "<h3>books_tbl structure:</h3>";
    $stmt = $conn->prepare("DESCRIBE books_tbl");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    // List categories
    echo "<h3>Categories (first 5):</h3>";
    $stmt = $conn->prepare("SELECT category_id, category_name FROM categories_tbl LIMIT 5");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as $cat) {
        echo "<p>ID: " . $cat['category_id'] . " → " . $cat['category_name'] . "</p>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Complete migration successful!</h3>";
    echo "<p><strong>Database is now ready for book creation with proper schema and IDs.</strong></p>";
    echo "<p><a href='http://localhost:5173/admin/dashboard'>Go back to Admin Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Migration failed:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

?>
