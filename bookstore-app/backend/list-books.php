<?php
/**
 * list-books.php - Fetch all books
 * 
 * PURPOSE: Returns all created books with their details
 * - Retrieves books from books_tbl
 * - Returns book_id, title, author, description, isbn, price, stock_quantity, and book_cover_image
 * - Used by admin to view and manage books
 * 
 * ENDPOINT: GET /backend/list-books.php
 * RESPONSE: {success: boolean, books: array, total: number}
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
        require_once 'book-image-storage.php';
        
        // Query to get all books
        $stmt = $conn->prepare("
            SELECT 
                book_id,
                title_fld as title,
                author_fld as author,
                description_fld as description,
                isbn_fld as isbn,
                price_fld as price,
                stock_qty_fld as stock_quantity,
                book_cover_image
            FROM books_tbl 
            ORDER BY book_id DESC
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
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert price to float and stock to int
        foreach ($books as &$book) {
            $book['book_id'] = intval($book['book_id']);
            $book['price'] = floatval($book['price']);
            $book['stock_quantity'] = intval($book['stock_quantity']);

            $imageState = getBookImageState($book['book_id']);
            $book['book_cover_original_image'] = $imageState['book_cover_original_image'];
            $book['image_scale'] = $imageState['image_scale'];
            $book['image_offset_x'] = $imageState['image_offset_x'];
            $book['image_offset_y'] = $imageState['image_offset_y'];
        }

        // Fetch categories for each book
        $catStmt = $conn->prepare("
            SELECT bc.book_id, c.category_id, c.category_name_fld as category_name
            FROM book_categories_tbl bc
            JOIN categories_tbl c ON bc.category_id = c.category_id
            ORDER BY c.category_name_fld ASC
        ");
        $catStmt->execute();
        $allBookCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group categories by book_id
        $categoryMap = [];
        foreach ($allBookCategories as $bc) {
            $categoryMap[$bc['book_id']][] = [
                'category_id' => intval($bc['category_id']),
                'category_name' => $bc['category_name']
            ];
        }

        // Attach categories to each book
        foreach ($books as &$book) {
            $book['categories'] = isset($categoryMap[$book['book_id']]) 
                ? $categoryMap[$book['book_id']] 
                : [];
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'books' => $books,
            'total' => count($books)
        ]);
        
    } catch (Exception $e) {
        error_log("List books error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching books: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
