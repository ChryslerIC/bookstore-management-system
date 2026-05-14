<?php
require_once 'db.php';

try {
    // Count books
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM books_tbl");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Books in database: " . $result['count'];
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
