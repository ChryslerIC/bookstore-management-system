<?php
require_once 'db.php';

try {
    $stmt = $conn->prepare("SELECT category_id, category_name_fld FROM categories_tbl LIMIT 5");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "First 5 categories:\n";
    foreach ($categories as $cat) {
        echo "  ID: " . $cat['category_id'] . ", Name: " . $cat['category_name_fld'] . "\n";
    }
    
    // Check total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM categories_tbl");
    $countStmt->execute();
    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal categories: " . $result['count'];
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
