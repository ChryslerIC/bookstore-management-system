<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/encryption.php';
require_once __DIR__ . '/../backend/auth-middleware.php';
require_once __DIR__ . '/../backend/book-image-storage.php';

function apiResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit();
}

function apiRequestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function apiReadJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function apiPathSegments(): array
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/api', PHP_URL_PATH) ?: '/api';
    $trimmed = trim($path, '/');
    $segments = $trimmed === '' ? [] : explode('/', $trimmed);

    if (!empty($segments) && $segments[0] === 'api') {
        array_shift($segments);
    }

    return array_values($segments);
}

function apiRequireFields(array $data, array $requiredFields): void
{
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
            apiResponse(400, ['success' => false, 'message' => "Missing required field: {$field}"]);
        }
    }
}

function apiGetBearerToken(): string
{
    $header = authGetAuthorizationHeaderValue();
    return authExtractBearerToken($header);
}

function apiRequireUser(): array
{
    return authenticate();
}

function apiRequireAdmin(): array
{
    return authenticateAdmin();
}

function apiMaskPhone(?string $phone): string
{
    if (!$phone) {
        return '';
    }

    $tail = substr($phone, -4);
    return str_repeat('*', max(strlen($phone) - 4, 0)) . $tail;
}

function apiDecryptPhone(array $row): string
{
    if (empty($row['phone_encrypted']) || empty($row['phone_iv']) || empty($row['phone_tag'])) {
        return '';
    }

    try {
        return EncryptionUtil::decryptFromStorage(
            $row['phone_encrypted'],
            $row['phone_iv'],
            $row['phone_tag']
        );
    } catch (Exception $exception) {
        return '';
    }
}

function apiNormalizePhilippinePhone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

    if (preg_match('/^09\d{9}$/', $digits) === 1) {
        return '+63' . substr($digits, 1);
    }

    if (preg_match('/^639\d{9}$/', $digits) === 1) {
        return '+' . $digits;
    }

    if (preg_match('/^\+639\d{9}$/', trim($phone)) === 1) {
        return trim($phone);
    }

    apiResponse(400, ['success' => false, 'message' => 'Phone number must be a valid 11-digit mobile number.']);
}

function apiFetchUserById(PDO $conn, int $accountId): ?array
{
    $stmt = dbPrepare($conn, "
        SELECT ua.account_id, ua.name_id, ua.email, ua.password_hash, ua.role,
               ua.phone_encrypted, ua.phone_iv, ua.phone_tag,
               un.fname_fld, un.lname_fld
        FROM user_account_tbl ua
        JOIN user_name_tbl un ON un.name_id = ua.name_id
        WHERE ua.account_id = :account_id
    ");
    $stmt->execute([':account_id' => $accountId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function apiFormatUser(array $user, bool $includeSensitivePhone = false): array
{
    $phone = apiDecryptPhone($user);

    return [
        'account_id' => intval($user['account_id']),
        'fname' => $user['fname_fld'],
        'lname' => $user['lname_fld'],
        'email' => $user['email'],
        'role' => $user['role'],
        'phone' => $includeSensitivePhone ? $phone : apiMaskPhone($phone),
    ];
}

function apiFetchBookById(PDO $conn, int $bookId): ?array
{
    $stmt = dbPrepare($conn, "
        SELECT book_id, title_fld AS title, author_fld AS author, description_fld AS description,
               isbn_fld AS isbn, price_fld AS price, stock_qty_fld AS stock_quantity, book_cover_image
        FROM books_tbl
        WHERE book_id = :book_id
    ");
    $stmt->execute([':book_id' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        return null;
    }

    $book['book_id'] = intval($book['book_id']);
    $book['price'] = floatval($book['price']);
    $book['stock_quantity'] = intval($book['stock_quantity']);

    $imageState = getBookImageState($book['book_id']);
    $book['book_cover_original_image'] = $imageState['book_cover_original_image'];
    $book['image_scale'] = $imageState['image_scale'];
    $book['image_offset_x'] = $imageState['image_offset_x'];
    $book['image_offset_y'] = $imageState['image_offset_y'];

    $catStmt = dbPrepare($conn, "
        SELECT c.category_id, c.category_name_fld AS category_name
        FROM book_categories_tbl bc
        JOIN categories_tbl c ON c.category_id = bc.category_id
        WHERE bc.book_id = :book_id
        ORDER BY c.category_name_fld ASC
    ");
    $catStmt->execute([':book_id' => $bookId]);
    $book['categories'] = array_map(
        static fn(array $category): array => [
            'category_id' => intval($category['category_id']),
            'category_name' => $category['category_name'],
        ],
        $catStmt->fetchAll(PDO::FETCH_ASSOC)
    );

    return $book;
}

function apiFetchOrderById(PDO $conn, int $orderId): ?array
{
    $stmt = dbPrepare($conn, "
        SELECT o.order_id, o.account_id, o.total_amount_fld, o.order_status_fld,
               o.order_created_fld, o.order_updated_fld,
               ua.email, un.fname_fld, un.lname_fld
        FROM orders_tbl o
        JOIN user_account_tbl ua ON ua.account_id = o.account_id
        JOIN user_name_tbl un ON un.name_id = ua.name_id
        WHERE o.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return null;
    }

    $itemsStmt = dbPrepare($conn, "
        SELECT oi.order_item_id, oi.book_id, oi.quantity_fld, oi.price_at_purchase_fld,
               b.title_fld AS title, b.author_fld AS author
        FROM order_items_tbl oi
        JOIN books_tbl b ON b.book_id = oi.book_id
        WHERE oi.order_id = :order_id
        ORDER BY oi.order_item_id ASC
    ");
    $itemsStmt->execute([':order_id' => $orderId]);

    return [
        'order_id' => intval($order['order_id']),
        'account_id' => intval($order['account_id']),
        'total_amount' => floatval($order['total_amount_fld']),
        'status' => $order['order_status_fld'],
        'created_at' => $order['order_created_fld'],
        'updated_at' => $order['order_updated_fld'],
        'user' => [
            'account_id' => intval($order['account_id']),
            'fname' => $order['fname_fld'],
            'lname' => $order['lname_fld'],
            'email' => $order['email'],
        ],
        'items' => array_map(
            static fn(array $item): array => [
                'order_item_id' => intval($item['order_item_id']),
                'book_id' => intval($item['book_id']),
                'title' => $item['title'],
                'author' => $item['author'],
                'quantity' => intval($item['quantity_fld']),
                'price_at_purchase' => floatval($item['price_at_purchase_fld']),
            ],
            $itemsStmt->fetchAll(PDO::FETCH_ASSOC)
        ),
    ];
}
