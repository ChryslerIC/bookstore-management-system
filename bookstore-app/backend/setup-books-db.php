<?php
/**
 * setup-books-db.php - Create required tables for book management system
 * 
 * PURPOSE: Sets up database tables for books catalog
 * - Creates books_tbl for storing book information
 * - Creates categories_tbl for book categories
 * - Creates book_categories_tbl for linking books to categories
 * 
 * USAGE: Visit http://localhost:8000/backend/setup-books-db.php in browser
 */

require_once 'db.php';

try {
    echo "<h2>Setting up Book Management Database Tables...</h2>";

    /**
     * CREATE categories_tbl
     * Stores book categories/genres
     */
    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories_tbl (
            category_id INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✓ Created categories_tbl</p>";

    /**
     * CREATE books_tbl
     * Stores book information
     */
    $conn->exec("
        CREATE TABLE IF NOT EXISTS books_tbl (
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

    /**
     * CREATE book_categories_tbl
     * Links books to categories (many-to-many relationship)
     */
    $conn->exec("
        CREATE TABLE IF NOT EXISTS book_categories_tbl (
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

    /**
     * Insert default categories if they don't exist
     */
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

    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM categories_tbl");
    $checkStmt->execute();
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] == 0) {
        $insertStmt = $conn->prepare("INSERT INTO categories_tbl (category_name) VALUES (:name)");
        foreach ($defaultCategories as $category) {
            $insertStmt->execute([':name' => $category]);
        }
        echo "<p>✓ Inserted " . count($defaultCategories) . " default categories</p>";
    } else {
        echo "<p>✓ Categories already exist (" . $result['count'] . " found)</p>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Database setup completed successfully!</h3>";
    echo "<p><a href='http://localhost:5173/admin/dashboard'>Go back to Admin Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error setting up database:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>Stack trace: " . nl2br($e->getTraceAsString()) . "</p>";
}

?>
