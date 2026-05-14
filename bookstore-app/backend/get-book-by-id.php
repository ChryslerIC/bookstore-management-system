<?php
/**
 * get-book-by-id.php - Fetch a single book by ID
 * 
 * PURPOSE: Returns a single book with its details and categories
 * - Retrieves book from books_tbl by book_id
 * - Returns book_id, title, author, description, isbn, price, stock_quantity, book_cover_image
 * - Fetches and includes all categories assigned to the book
 * - Mirrors the data structure of list-books.php for consistency
 * 
 * ENDPOINT: GET /backend/get-book-by-id.php?book_id=X
 * REQUEST PARAMS: book_id (required, integer)
 * RESPONSE: {success: boolean, book: object, message: string}
 * 
 * SUCCESS RESPONSE:
 * {
 *   "success": true,
 *   "book": {
 *     "book_id": 1,
 *     "title": "...",
 *     "author": "...",
 *     "description": "...",
 *     "isbn": "...",
 *     "price": 29.99,
 *     "stock_quantity": 10,
 *     "book_cover_image": "...",
 *     "categories": [
 *       {"category_id": 1, "category_name": "Fiction"}
 *     ]
 *   }
 * }
 * 
 * ERROR RESPONSE:
 * {
 *   "success": false,
 *   "message": "Book not found" or other error message
 * }
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

        // Get book_id from query parameters
        $book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : null;

        if (!$book_id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'book_id parameter is required'
            ]);
            exit();
        }

        // Query to get the single book
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
            WHERE book_id = :book_id
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

        $stmt->execute([':book_id' => $book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if book exists
        if (!$book) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Book not found'
            ]);
            exit();
        }

        // Convert data types for consistency
        $book['book_id'] = intval($book['book_id']);
        $book['price'] = floatval($book['price']);
        $book['stock_quantity'] = intval($book['stock_quantity']);

        $imageState = getBookImageState($book['book_id']);
        $book['book_cover_original_image'] = $imageState['book_cover_original_image'];
        $book['image_scale'] = $imageState['image_scale'];
        $book['image_offset_x'] = $imageState['image_offset_x'];
        $book['image_offset_y'] = $imageState['image_offset_y'];

        // Fetch categories for this book
        $catStmt = $conn->prepare("
            SELECT c.category_id, c.category_name_fld as category_name
            FROM book_categories_tbl bc
            JOIN categories_tbl c ON bc.category_id = c.category_id
            WHERE bc.book_id = :book_id
            ORDER BY c.category_name_fld ASC
        ");

        if ($catStmt) {
            $catStmt->execute([':book_id' => $book_id]);
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert category_id to int
            foreach ($categories as &$cat) {
                $cat['category_id'] = intval($cat['category_id']);
            }

            $book['categories'] = $categories;
        } else {
            $book['categories'] = [];
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'book' => $book
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
