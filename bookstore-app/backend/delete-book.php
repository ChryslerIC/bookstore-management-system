<?php
/**
 * delete-book.php - Delete a book from the catalog
 * 
 * PURPOSE: Handles book deletion
 * - Validates book exists
 * - Deletes book from books_tbl
 * - Removes book-category associations from book_categories_tbl
 * - Optionally deletes book cover image file
 * - Returns success response
 * 
 * ENDPOINT: POST /backend/delete-book.php
 * REQUEST BODY: {book_id}
 * RESPONSE: {success: boolean, message: string}
 */

require_once 'db.php';
require_once 'book-image-storage.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // Log received data for debugging
        error_log("Received delete-book request with data: " . json_encode($data));

        /**
         * STEP 1: Validate required fields
         */
        if (!isset($data['book_id']) || $data['book_id'] === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required field: book_id']);
            exit();
        }

        $book_id = intval($data['book_id']);
        error_log("Deleting book with ID: $book_id");

        /**
         * STEP 2: Get book details (for file deletion)
         */
        $getStmt = dbPrepare($conn, "
            SELECT book_cover_image, title_fld 
            FROM books_tbl 
            WHERE book_id = :book_id
        ");
        
        $getStmt->execute([':book_id' => $book_id]);
        $book = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$book) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Book not found']);
            exit();
        }

        $book_title = $book['title_fld'];
        $book_cover_image = $book['book_cover_image'];
        $imageState = getBookImageState($book_id);
        $book_cover_original_image = $imageState['book_cover_original_image'] ?? null;

        /**
         * STEP 3: Delete book-category associations
         */
        error_log("Removing category associations for book $book_id...");
        
        $deleteCatStmt = dbPrepare($conn, "
            DELETE FROM book_categories_tbl 
            WHERE book_id = :book_id
        ");
        
        $deleteCatStmt->execute([':book_id' => $book_id]);
        error_log("Category associations removed");

        /**
         * STEP 4: Delete book from books_tbl
         */
        error_log("Deleting book from database...");
        
        $deleteStmt = dbPrepare($conn, "
            DELETE FROM books_tbl 
            WHERE book_id = :book_id
        ");
        
        $deleteStmt->execute([':book_id' => $book_id]);
        error_log("Book deleted from database");

        /**
         * STEP 5: Delete book cover image file
         */
        deleteBookImageFile($book_cover_image);
        deleteBookImageFile($book_cover_original_image);
        deleteBookImageState($book_id);

        /**
         * STEP 6: Return success response
         */
        error_log("Book deletion successful!");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Book deleted successfully',
            'deleted_book' => [
                'book_id' => $book_id,
                'title' => $book_title
            ]
        ]);

    } catch (Exception $e) {
        error_log("Delete book error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting book: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
