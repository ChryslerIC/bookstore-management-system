<?php
/**
 * get-book-categories.php - Get categories for a specific book
 * 
 * PURPOSE: Fetches all categories assigned to a book
 * - Retrieves category IDs and names for a book
 * - Used in edit modal to show current categories
 * 
 * ENDPOINT: GET /backend/get-book-categories.php?book_id=X
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
        
        error_log("Database connection established for get-book-categories.php");
        
        // Get book_id from query parameters
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : null;
        
        if (!$book_id) {
            error_log("book_id parameter missing or invalid");
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'book_id parameter is required'
            ]);
            exit();
        }
        
        error_log("Fetching categories for book_id: $book_id");
        
        // Query to get categories for this book
        $sql = "
            SELECT bc.category_id, c.category_name_fld as category_name
            FROM book_categories_tbl bc
            INNER JOIN categories_tbl c ON bc.category_id = c.category_id
            WHERE bc.book_id = :book_id
            ORDER BY c.category_name_fld
        ";
        
        error_log("Preparing SQL query: $sql");
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $error_info = $conn->errorInfo();
            error_log("Failed to prepare statement. Error: " . json_encode($error_info));
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: Failed to prepare statement',
                'error' => $error_info,
                'sql' => $sql
            ]);
            exit();
        }
        
        error_log("Statement prepared successfully. Executing with book_id=$book_id");
        
        $execute_result = $stmt->execute([':book_id' => $book_id]);
        
        if (!$execute_result) {
            $error_info = $stmt->errorInfo();
            error_log("Failed to execute statement. Error: " . json_encode($error_info));
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: Failed to execute statement',
                'error' => $error_info
            ]);
            exit();
        }
        
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Successfully fetched " . count($categories) . " categories for book_id: $book_id");
        
        // Convert category_id to int
        foreach ($categories as &$cat) {
            $cat['category_id'] = intval($cat['category_id']);
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        
    } catch (Exception $e) {
        error_log("Get book categories exception: " . $e->getMessage());
        error_log("Exception trace: " . $e->getTraceAsString());
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
