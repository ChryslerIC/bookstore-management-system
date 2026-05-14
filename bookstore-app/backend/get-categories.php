<?php
/**
 * get-categories.php - Fetch all book categories
 * 
 * PURPOSE: Returns all existing categories for book management
 * - Retrieves categories from categories_tbl
 * - Returns category_id and category_name
 * - Used by admin when creating/editing books
 * 
 * ENDPOINT: GET /backend/get-categories.php
 * RESPONSE: {success: boolean, categories: array}
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        require_once 'db.php';
        
        // Query to get all categories
        $stmt = $conn->prepare("
            SELECT category_id, category_name_fld as category_name
            FROM categories_tbl 
            ORDER BY category_name_fld ASC
        ");
        
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: Failed to prepare statement',
                'error' => $conn->errorInfo()
            ]);
            exit();
        }
        
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
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
            'message' => 'Error fetching categories: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
