<?php
/**
 * init-categories.php - Initialize categories in the database
 * 
 * PURPOSE: Checks if categories exist and creates sample categories if needed
 * - Useful for development and debugging
 * - Creates standard book categories if none exist
 * 
 * ENDPOINT: GET or POST /backend/init-categories.php
 * RESPONSE: {success: boolean, message: string, categories: array}
 * 
 * CAUTION: Can be run multiple times safely (checks for duplicates)
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once 'db.php';

    // Sample categories to create
    $sample_categories = [
        'Fiction',
        'Non-Fiction',
        'Science Fiction',
        'Mystery',
        'Romance',
        'Thriller',
        'Fantasy',
        'Biography',
        'History',
        'Self-Help',
        'Technology',
        'Business',
        'Children',
        'Young Adult'
    ];

    // First, get existing categories
    $stmt = $conn->prepare("SELECT category_name_fld FROM categories_tbl");
    $stmt->execute();
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $existing_lower = array_map('strtolower', $existing);

    $created_count = 0;
    $skipped_count = 0;
    $created_names = [];
    $skipped_names = [];

    // Insert missing categories
    foreach ($sample_categories as $category) {
        if (!in_array(strtolower($category), $existing_lower)) {
            try {
                $insertStmt = $conn->prepare("INSERT INTO categories_tbl (category_name_fld) VALUES (:name)");
                $insertStmt->execute([':name' => $category]);
                $created_count++;
                $created_names[] = $category;
            } catch (Exception $e) {
                $skipped_count++;
                $skipped_names[] = $category;
            }
        } else {
            $skipped_count++;
            $skipped_names[] = $category;
        }
    }

    // Fetch all current categories
    $stmt = $conn->prepare("SELECT category_id, category_name_fld as category_name FROM categories_tbl ORDER BY category_name_fld ASC");
    $stmt->execute();
    $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Initialization complete. Created: $created_count, Skipped: $skipped_count",
        'created' => $created_names,
        'skipped' => $skipped_names,
        'total_categories' => count($all_categories),
        'categories' => $all_categories
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
