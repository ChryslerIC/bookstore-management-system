<?php
require_once __DIR__ . '/bootstrap.php';

$method = apiRequestMethod();
$segments = apiPathSegments();

if ($method === 'OPTIONS') {
    apiResponse(200, ['success' => true]);
}

if (empty($segments)) {
    apiResponse(200, [
        'success' => true,
        'message' => 'Online bookstore API',
        'available_roots' => ['auth', 'users', 'books', 'orders', 'admin', 'reports'],
    ]);
}

switch ($segments[0]) {
    case 'auth':
        handleAuthRoutes($conn, $method, array_slice($segments, 1));
        break;
    case 'users':
        handleUserRoutes($conn, $method, array_slice($segments, 1));
        break;
    case 'books':
        handleBookRoutes($conn, $method, array_slice($segments, 1));
        break;
    case 'orders':
        handleOrderRoutes($conn, $method, array_slice($segments, 1));
        break;
    case 'admin':
        handleAdminRoutes($conn, $method, array_slice($segments, 1));
        break;
    case 'reports':
        handleReportRoutes($conn, $method, array_slice($segments, 1));
        break;
    default:
        apiResponse(404, ['success' => false, 'message' => 'Endpoint not found']);
}

function handleAuthRoutes(PDO $conn, string $method, array $segments): void
{
    $resource = $segments[0] ?? '';

    if ($resource === 'register' && $method === 'POST') {
        $data = apiReadJsonBody();
        apiRequireFields($data, ['fname', 'lname', 'email', 'phone', 'password']);
        $normalizedPhone = apiNormalizePhilippinePhone((string) $data['phone']);

        $checkStmt = dbPrepare($conn, 'SELECT account_id FROM user_account_tbl WHERE email = :email');
        $checkStmt->execute([':email' => $data['email']]);
        if ($checkStmt->fetch()) {
            apiResponse(400, ['success' => false, 'message' => 'Email already registered']);
        }

        $nameStmt = dbPrepare($conn, 'INSERT INTO user_name_tbl (fname_fld, lname_fld) VALUES (:fname, :lname)');
        $nameStmt->execute([':fname' => trim($data['fname']), ':lname' => trim($data['lname'])]);
        $nameId = intval($conn->lastInsertId());

        $encryptedPhone = EncryptionUtil::encryptForStorage($normalizedPhone);
        $insertStmt = dbPrepare($conn, "
            INSERT INTO user_account_tbl (name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role)
            VALUES (:name_id, :email, :password_hash, :phone_encrypted, :phone_iv, :phone_tag, 'user')
        ");
        $insertStmt->execute([
            ':name_id' => $nameId,
            ':email' => trim($data['email']),
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':phone_encrypted' => $encryptedPhone['encrypted'],
            ':phone_iv' => $encryptedPhone['iv'],
            ':phone_tag' => $encryptedPhone['tag'],
        ]);

        $user = apiFetchUserById($conn, intval($conn->lastInsertId()));
        apiResponse(201, ['success' => true, 'message' => 'User registered successfully', 'user' => apiFormatUser($user, true)]);
    }

    if ($resource === 'login' && $method === 'POST') {
        $data = apiReadJsonBody();
        apiRequireFields($data, ['email', 'password']);

        $stmt = dbPrepare($conn, "
            SELECT ua.account_id, ua.name_id, ua.email, ua.password_hash, ua.role,
                   ua.phone_encrypted, ua.phone_iv, ua.phone_tag,
                   un.fname_fld, un.lname_fld
            FROM user_account_tbl ua
            JOIN user_name_tbl un ON un.name_id = ua.name_id
            WHERE ua.email = :email
        ");
        $stmt->execute([':email' => trim($data['email'])]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            apiResponse(401, ['success' => false, 'message' => 'Invalid email or password']);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        dbPrepare($conn, 'DELETE FROM user_tokens_tbl WHERE account_id = :account_id')
            ->execute([':account_id' => $user['account_id']]);
        dbPrepare($conn, '
            INSERT INTO user_tokens_tbl (account_id, token, expires_at)
            VALUES (:account_id, :token, :expires_at)
        ')->execute([
            ':account_id' => $user['account_id'],
            ':token' => $token,
            ':expires_at' => $expiresAt,
        ]);

        apiResponse(200, [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => apiFormatUser($user, true),
        ]);
    }

    if ($resource === 'logout' && $method === 'POST') {
        $token = apiGetBearerToken();
        if ($token === '') {
            $body = apiReadJsonBody();
            $token = trim($body['token'] ?? '');
        }

        if ($token === '') {
            apiResponse(401, ['success' => false, 'message' => 'No token provided']);
        }

        dbPrepare($conn, 'DELETE FROM user_tokens_tbl WHERE token = :token')->execute([':token' => $token]);
        apiResponse(200, ['success' => true, 'message' => 'Logged out successfully']);
    }

    apiResponse(404, ['success' => false, 'message' => 'Auth endpoint not found']);
}

function handleUserRoutes(PDO $conn, string $method, array $segments): void
{
    $resource = $segments[0] ?? '';
    $currentUser = apiRequireUser();

    if ($resource === 'profile' && $method === 'GET') {
        $user = apiFetchUserById($conn, intval($currentUser['account_id']));
        apiResponse(200, ['success' => true, 'profile' => apiFormatUser($user, true)]);
    }

    if ($resource === 'profile' && $method === 'PUT') {
        $data = apiReadJsonBody();
        apiRequireFields($data, ['fname', 'lname', 'email', 'phone']);
        $normalizedPhone = apiNormalizePhilippinePhone((string) $data['phone']);

        $user = apiFetchUserById($conn, intval($currentUser['account_id']));
        if (!$user) {
            apiResponse(404, ['success' => false, 'message' => 'User not found']);
        }

        $duplicateStmt = dbPrepare($conn, '
            SELECT account_id FROM user_account_tbl
            WHERE email = :email AND account_id != :account_id
        ');
        $duplicateStmt->execute([
            ':email' => trim($data['email']),
            ':account_id' => $currentUser['account_id'],
        ]);
        if ($duplicateStmt->fetch()) {
            apiResponse(400, ['success' => false, 'message' => 'Email already registered to another account']);
        }

        $encryptedPhone = EncryptionUtil::encryptForStorage($normalizedPhone);
        dbPrepare($conn, '
            UPDATE user_name_tbl SET fname_fld = :fname, lname_fld = :lname WHERE name_id = :name_id
        ')->execute([
            ':fname' => trim($data['fname']),
            ':lname' => trim($data['lname']),
            ':name_id' => $user['name_id'],
        ]);
        dbPrepare($conn, '
            UPDATE user_account_tbl
            SET email = :email, phone_encrypted = :phone_encrypted, phone_iv = :phone_iv, phone_tag = :phone_tag
            WHERE account_id = :account_id
        ')->execute([
            ':email' => trim($data['email']),
            ':phone_encrypted' => $encryptedPhone['encrypted'],
            ':phone_iv' => $encryptedPhone['iv'],
            ':phone_tag' => $encryptedPhone['tag'],
            ':account_id' => $currentUser['account_id'],
        ]);

        $updatedUser = apiFetchUserById($conn, intval($currentUser['account_id']));
        apiResponse(200, ['success' => true, 'message' => 'Profile updated successfully', 'profile' => apiFormatUser($updatedUser, true)]);
    }

    if ($resource === 'change-password' && $method === 'PUT') {
        $data = apiReadJsonBody();
        apiRequireFields($data, ['current_password', 'new_password']);

        $user = apiFetchUserById($conn, intval($currentUser['account_id']));
        if (!$user || !password_verify($data['current_password'], $user['password_hash'])) {
            apiResponse(400, ['success' => false, 'message' => 'Current password is incorrect']);
        }

        dbPrepare($conn, '
            UPDATE user_account_tbl SET password_hash = :password_hash WHERE account_id = :account_id
        ')->execute([
            ':password_hash' => password_hash($data['new_password'], PASSWORD_BCRYPT),
            ':account_id' => $currentUser['account_id'],
        ]);

        apiResponse(200, ['success' => true, 'message' => 'Password updated successfully']);
    }

    apiResponse(404, ['success' => false, 'message' => 'User endpoint not found']);
}

function handleBookRoutes(PDO $conn, string $method, array $segments): void
{
    if (empty($segments)) {
        if ($method === 'GET') {
            $stmt = dbPrepare($conn, "
                SELECT book_id
                FROM books_tbl
                ORDER BY book_id DESC
            ");
            $stmt->execute();
            $books = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $book = apiFetchBookById($conn, intval($row['book_id']));
                if ($book) {
                    $books[] = $book;
                }
            }

            apiResponse(200, ['success' => true, 'books' => $books, 'total' => count($books)]);
        }

        if ($method === 'POST') {
            apiRequireAdmin();
            $data = apiReadJsonBody();
            apiRequireFields($data, ['title', 'author', 'description', 'isbn', 'price', 'stock_quantity']);

            $duplicateStmt = dbPrepare($conn, 'SELECT book_id FROM books_tbl WHERE isbn_fld = :isbn');
            $duplicateStmt->execute([':isbn' => trim($data['isbn'])]);
            if ($duplicateStmt->fetch()) {
                apiResponse(400, ['success' => false, 'message' => 'ISBN already exists']);
            }

            dbPrepare($conn, '
                INSERT INTO books_tbl (title_fld, author_fld, description_fld, isbn_fld, price_fld, stock_qty_fld, book_cover_image)
                VALUES (:title, :author, :description, :isbn, :price, :stock_quantity, :book_cover_image)
            ')->execute([
                ':title' => trim($data['title']),
                ':author' => trim($data['author']),
                ':description' => trim($data['description']),
                ':isbn' => trim($data['isbn']),
                ':price' => floatval($data['price']),
                ':stock_quantity' => intval($data['stock_quantity']),
                ':book_cover_image' => trim($data['book_cover_image'] ?? '') ?: null,
            ]);

            $bookId = intval($conn->lastInsertId());
            foreach (($data['category_ids'] ?? []) as $categoryId) {
                dbPrepare($conn, '
                    INSERT INTO book_categories_tbl (book_id, category_id) VALUES (:book_id, :category_id)
                ')->execute([
                    ':book_id' => $bookId,
                    ':category_id' => intval($categoryId),
                ]);
            }

            $book = apiFetchBookById($conn, $bookId);
            apiResponse(201, ['success' => true, 'message' => 'Book created successfully', 'book' => $book]);
        }
    }

    $bookId = intval($segments[0] ?? 0);
    if ($bookId <= 0) {
        apiResponse(404, ['success' => false, 'message' => 'Book endpoint not found']);
    }

    if ($method === 'GET') {
        $book = apiFetchBookById($conn, $bookId);
        if (!$book) {
            apiResponse(404, ['success' => false, 'message' => 'Book not found']);
        }
        apiResponse(200, ['success' => true, 'book' => $book]);
    }

    if ($method === 'PUT') {
        apiRequireAdmin();
        $existingBook = apiFetchBookById($conn, $bookId);
        if (!$existingBook) {
            apiResponse(404, ['success' => false, 'message' => 'Book not found']);
        }

        $data = apiReadJsonBody();
        apiRequireFields($data, ['title', 'author', 'description', 'isbn', 'price', 'stock_quantity']);

        dbPrepare($conn, '
            UPDATE books_tbl
            SET title_fld = :title, author_fld = :author, description_fld = :description,
                isbn_fld = :isbn, price_fld = :price, stock_qty_fld = :stock_quantity,
                book_cover_image = :book_cover_image
            WHERE book_id = :book_id
        ')->execute([
            ':title' => trim($data['title']),
            ':author' => trim($data['author']),
            ':description' => trim($data['description']),
            ':isbn' => trim($data['isbn']),
            ':price' => floatval($data['price']),
            ':stock_quantity' => intval($data['stock_quantity']),
            ':book_cover_image' => trim($data['book_cover_image'] ?? ($existingBook['book_cover_image'] ?? '')) ?: null,
            ':book_id' => $bookId,
        ]);

        dbPrepare($conn, 'DELETE FROM book_categories_tbl WHERE book_id = :book_id')
            ->execute([':book_id' => $bookId]);
        foreach (($data['category_ids'] ?? []) as $categoryId) {
            dbPrepare($conn, '
                INSERT INTO book_categories_tbl (book_id, category_id) VALUES (:book_id, :category_id)
            ')->execute([
                ':book_id' => $bookId,
                ':category_id' => intval($categoryId),
            ]);
        }

        $book = apiFetchBookById($conn, $bookId);
        apiResponse(200, ['success' => true, 'message' => 'Book updated successfully', 'book' => $book]);
    }

    if ($method === 'DELETE') {
        apiRequireAdmin();
        $book = apiFetchBookById($conn, $bookId);
        if (!$book) {
            apiResponse(404, ['success' => false, 'message' => 'Book not found']);
        }

        dbPrepare($conn, 'DELETE FROM book_categories_tbl WHERE book_id = :book_id')
            ->execute([':book_id' => $bookId]);
        dbPrepare($conn, 'DELETE FROM books_tbl WHERE book_id = :book_id')
            ->execute([':book_id' => $bookId]);

        apiResponse(200, ['success' => true, 'message' => 'Book deleted successfully', 'deleted_book' => $book]);
    }

    apiResponse(405, ['success' => false, 'message' => 'Method not allowed']);
}

function handleOrderRoutes(PDO $conn, string $method, array $segments): void
{
    $currentUser = apiRequireUser();

    if (empty($segments) && $method === 'POST') {
        $data = apiReadJsonBody();
        apiRequireFields($data, ['items']);

        if (!is_array($data['items']) || count($data['items']) === 0) {
            apiResponse(400, ['success' => false, 'message' => 'Order items are required']);
        }

        $lineItems = [];
        $totalAmount = 0.0;
        foreach ($data['items'] as $item) {
            $bookId = intval($item['book_id'] ?? 0);
            $quantity = intval($item['quantity'] ?? 0);
            if ($bookId <= 0 || $quantity <= 0) {
                apiResponse(400, ['success' => false, 'message' => 'Each order item must include a valid book_id and quantity']);
            }

            $book = apiFetchBookById($conn, $bookId);
            if (!$book) {
                apiResponse(404, ['success' => false, 'message' => "Book {$bookId} not found"]);
            }
            if ($book['stock_quantity'] < $quantity) {
                apiResponse(400, ['success' => false, 'message' => "Insufficient stock for book {$book['title']}"]);
            }

            $price = floatval($book['price']);
            $lineItems[] = ['book' => $book, 'quantity' => $quantity, 'price' => $price];
            $totalAmount += $price * $quantity;
        }

        $paymentEncrypted = null;
        $paymentIv = null;
        $paymentTag = null;
        if (!empty($data['payment_method'])) {
            $encrypted = EncryptionUtil::encryptForStorage((string) $data['payment_method']);
            $paymentEncrypted = $encrypted['encrypted'];
            $paymentIv = $encrypted['iv'];
            $paymentTag = $encrypted['tag'];
        }

        dbPrepare($conn, '
            INSERT INTO orders_tbl (account_id, total_amount_fld, payment_encrypted, payment_iv, payment_tag, order_status_fld)
            VALUES (:account_id, :total_amount, :payment_encrypted, :payment_iv, :payment_tag, :status)
        ')->execute([
            ':account_id' => $currentUser['account_id'],
            ':total_amount' => $totalAmount,
            ':payment_encrypted' => $paymentEncrypted,
            ':payment_iv' => $paymentIv,
            ':payment_tag' => $paymentTag,
            ':status' => $data['status'] ?? 'pending',
        ]);

        $orderId = intval($conn->lastInsertId());
        foreach ($lineItems as $lineItem) {
            dbPrepare($conn, '
                INSERT INTO order_items_tbl (order_id, book_id, quantity_fld, price_at_purchase_fld)
                VALUES (:order_id, :book_id, :quantity, :price)
            ')->execute([
                ':order_id' => $orderId,
                ':book_id' => $lineItem['book']['book_id'],
                ':quantity' => $lineItem['quantity'],
                ':price' => $lineItem['price'],
            ]);

            dbPrepare($conn, '
                UPDATE books_tbl
                SET stock_qty_fld = stock_qty_fld - :quantity
                WHERE book_id = :book_id
            ')->execute([
                ':quantity' => $lineItem['quantity'],
                ':book_id' => $lineItem['book']['book_id'],
            ]);
        }

        apiResponse(201, ['success' => true, 'message' => 'Order created successfully', 'order' => apiFetchOrderById($conn, $orderId)]);
    }

    if (($segments[0] ?? '') === 'user' && $method === 'GET') {
        $userId = intval($segments[1] ?? 0);
        if ($userId <= 0) {
            apiResponse(400, ['success' => false, 'message' => 'Invalid user id']);
        }
        if (intval($currentUser['account_id']) !== $userId && $currentUser['role'] !== 'admin') {
            apiResponse(403, ['success' => false, 'message' => 'Access denied']);
        }

        $stmt = dbPrepare($conn, 'SELECT order_id FROM orders_tbl WHERE account_id = :account_id ORDER BY order_created_fld DESC');
        $stmt->execute([':account_id' => $userId]);
        $orders = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $order = apiFetchOrderById($conn, intval($row['order_id']));
            if ($order) {
                $orders[] = $order;
            }
        }

        apiResponse(200, ['success' => true, 'orders' => $orders, 'total' => count($orders)]);
    }

    $orderId = intval($segments[0] ?? 0);
    if ($orderId > 0 && $method === 'GET') {
        $order = apiFetchOrderById($conn, $orderId);
        if (!$order) {
            apiResponse(404, ['success' => false, 'message' => 'Order not found']);
        }
        if (intval($order['account_id']) !== intval($currentUser['account_id']) && $currentUser['role'] !== 'admin') {
            apiResponse(403, ['success' => false, 'message' => 'Access denied']);
        }

        apiResponse(200, ['success' => true, 'order' => $order]);
    }

    apiResponse(404, ['success' => false, 'message' => 'Order endpoint not found']);
}

function handleAdminRoutes(PDO $conn, string $method, array $segments): void
{
    apiRequireAdmin();
    $resource = $segments[0] ?? '';

    if ($resource === 'users' && $method === 'GET') {
        $stmt = dbPrepare($conn, '
            SELECT ua.account_id, ua.name_id, ua.email, ua.password_hash, ua.role,
                   ua.phone_encrypted, ua.phone_iv, ua.phone_tag,
                   un.fname_fld, un.lname_fld
            FROM user_account_tbl ua
            JOIN user_name_tbl un ON un.name_id = ua.name_id
            ORDER BY ua.account_id ASC
        ');
        $stmt->execute();
        $users = array_map(static fn(array $user): array => apiFormatUser($user, false), $stmt->fetchAll(PDO::FETCH_ASSOC));
        apiResponse(200, ['success' => true, 'users' => $users, 'total' => count($users)]);
    }

    if ($resource === 'books' && $method === 'GET') {
        handleBookRoutes($conn, 'GET', []);
    }

    apiResponse(404, ['success' => false, 'message' => 'Admin endpoint not found']);
}

function handleReportRoutes(PDO $conn, string $method, array $segments): void
{
    apiRequireAdmin();
    $resource = $segments[0] ?? '';

    if ($resource === 'sales' && $method === 'GET') {
        $summaryStmt = dbPrepare($conn, "
            SELECT COUNT(*) AS order_count,
                   COALESCE(SUM(total_amount_fld), 0) AS total_sales,
                   COALESCE(AVG(total_amount_fld), 0) AS average_order_value
            FROM orders_tbl
            WHERE order_status_fld != 'cancelled'
        ");
        $summaryStmt->execute();
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: ['order_count' => 0, 'total_sales' => 0, 'average_order_value' => 0];

        apiResponse(200, [
            'success' => true,
            'report' => [
                'order_count' => intval($summary['order_count']),
                'total_sales' => floatval($summary['total_sales']),
                'average_order_value' => floatval($summary['average_order_value']),
            ],
        ]);
    }

    if ($resource === 'orders' && $method === 'GET') {
        $stmt = dbPrepare($conn, "
            SELECT order_status_fld AS status, COUNT(*) AS total_orders, COALESCE(SUM(total_amount_fld), 0) AS total_amount
            FROM orders_tbl
            GROUP BY order_status_fld
            ORDER BY order_status_fld ASC
        ");
        $stmt->execute();
        $groups = array_map(
            static fn(array $row): array => [
                'status' => $row['status'],
                'total_orders' => intval($row['total_orders']),
                'total_amount' => floatval($row['total_amount']),
            ],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        apiResponse(200, ['success' => true, 'report' => $groups]);
    }

    apiResponse(404, ['success' => false, 'message' => 'Report endpoint not found']);
}
