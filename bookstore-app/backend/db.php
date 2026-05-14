<?php
/**
 * db.php - Database Connection File
 * 
 * PURPOSE: Establishes connection to MySQL database
 * - Sets up CORS headers for React frontend communication
 * - Handles preflight OPTIONS requests
 * - Creates PDO connection to bookstore_db
 * - Returns error if connection fails
 */

/**
 * CORS HEADERS SECTION
 * Allows React frontend (running on localhost:5173) to communicate with PHP backend
 * These headers must be sent BEFORE any other output
 */

// Use header() function to send HTTP header to client
// Set Access-Control-Allow-Origin header to allow cross-origin requests from any domain
header("Access-Control-Allow-Origin: *");

// Use header() to specify which HTTP methods are allowed (GET, POST, PUT, DELETE, OPTIONS)
// Browser sends preflight request asking which methods are allowed
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Use header() to specify which request headers are allowed (Content-Type, Authorization)
// Browser includes these headers in actual requests after preflight
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Use header() to cache preflight response for 3600 seconds (1 hour)
// Reduces number of preflight requests, improving performance
header("Access-Control-Max-Age: 3600");

// Use header() to set Content-Type response header to JSON
// Tells browser/client to expect JSON formatted response
header("Content-Type: application/json; charset=utf-8");

/**
 * HANDLE PREFLIGHT REQUESTS
 * Browser sends OPTIONS request before actual POST/PUT/DELETE requests
 * We need to respond with 200 OK and the above headers to allow the actual request
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Use http_response_code() to set HTTP status code to 200 OK
    http_response_code(200);
    // Use exit() to stop script execution after sending preflight response
    exit();
}

/**
 * ERROR LOGGING CONFIGURATION
 * Don't display errors directly to users (security risk)
 * Instead, log errors to a file
 */
// Use ini_set() to configure PHP settings
// First parameter: setting name, second parameter: value
// Set display_errors to 0 to hide errors from users (production security best practice)
ini_set('display_errors', 0);
// Use ini_set() to enable error logging functionality
ini_set('log_errors', 1);
// Use ini_set() with error_log setting to specify where PHP logs errors
// Use __DIR__ magic constant to get current directory path
// Combine with '/error.log' to create full path
ini_set('error_log', __DIR__ . '/error.log');
// Use error_reporting constant E_ALL to report all types of errors (fatal, warning, deprecated, etc.)
error_reporting(E_ALL);

/**
 * DATABASE CONFIGURATION
 * Prefer local MySQL, but fall back to the bundled SQLite file so this repo
 * can still run on machines without a running database service.
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'bookstore_db');
define('SQLITE_DB_PATH', __DIR__ . '/../bookstore_runtime.sqlite');

function normalizeSqlForSqlite(string $sql): string
{
    $sql = preg_replace('/\bNOW\(\)/i', "datetime('now')", $sql) ?? $sql;
    $sql = preg_replace('/\bCURDATE\(\)/i', "date('now')", $sql) ?? $sql;
    return $sql;
}

function dbPrepare(PDO $conn, string $sql): PDOStatement
{
    if (($conn->getAttribute(PDO::ATTR_DRIVER_NAME) ?? '') === 'sqlite') {
        $sql = normalizeSqlForSqlite($sql);
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new PDOException('Failed to prepare SQL statement.');
    }

    return $stmt;
}

function seedSqliteFallbackDatabase(PDO $sqlite): void
{
    $sqlite->exec("
        CREATE TABLE IF NOT EXISTS user_name_tbl (
            name_id INTEGER PRIMARY KEY AUTOINCREMENT,
            fname_fld TEXT NOT NULL,
            lname_fld TEXT NOT NULL,
            name_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            name_updated_fld TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS user_account_tbl (
            account_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name_id INTEGER NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            phone_encrypted TEXT,
            phone_iv TEXT,
            phone_tag TEXT,
            role TEXT DEFAULT 'user',
            account_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            account_updated_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (name_id) REFERENCES user_name_tbl(name_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS user_address_tbl (
            address_id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            country_fld TEXT NOT NULL,
            state_province_fld TEXT NOT NULL,
            city_town_fld TEXT NOT NULL,
            barangay_fld TEXT,
            apartment_unit_fld TEXT,
            streetnum_fld TEXT NOT NULL,
            housenum_fld TEXT NOT NULL,
            address_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            address_updated_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS categories_tbl (
            category_id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_name_fld TEXT NOT NULL UNIQUE
        );

        CREATE TABLE IF NOT EXISTS books_tbl (
            book_id INTEGER PRIMARY KEY AUTOINCREMENT,
            title_fld TEXT NOT NULL,
            author_fld TEXT NOT NULL,
            description_fld TEXT,
            isbn_fld TEXT UNIQUE,
            price_fld REAL NOT NULL,
            stock_qty_fld INTEGER NOT NULL DEFAULT 0,
            book_cover_image TEXT,
            original_cover_image TEXT,
            image_scale REAL DEFAULT 1.0,
            image_offset_x REAL DEFAULT 0.0,
            image_offset_y REAL DEFAULT 0.0,
            book_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            book_updated_fld TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS book_categories_tbl (
            book_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            PRIMARY KEY (book_id, category_id),
            FOREIGN KEY (book_id) REFERENCES books_tbl(book_id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories_tbl(category_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS orders_tbl (
            order_id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            total_amount_fld REAL NOT NULL,
            payment_encrypted TEXT,
            payment_iv TEXT,
            payment_tag TEXT,
            order_status_fld TEXT DEFAULT 'pending',
            order_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            order_updated_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS order_items_tbl (
            order_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            book_id INTEGER NOT NULL,
            quantity_fld INTEGER NOT NULL,
            price_at_purchase_fld REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders_tbl(order_id) ON DELETE CASCADE,
            FOREIGN KEY (book_id) REFERENCES books_tbl(book_id)
        );

        CREATE TABLE IF NOT EXISTS reviews_tbl (
            review_id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            book_id INTEGER NOT NULL,
            rating_fld INTEGER NOT NULL,
            comment_fld TEXT,
            review_created_fld TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id) ON DELETE CASCADE,
            FOREIGN KEY (book_id) REFERENCES books_tbl(book_id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS user_tokens_tbl (
            token_id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            expires_at TEXT NOT NULL,
            FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id) ON DELETE CASCADE
        );
    ");

    $existingUsers = intval($sqlite->query('SELECT COUNT(*) FROM user_account_tbl')->fetchColumn());
    if ($existingUsers === 0) {
        $sqlite->exec("
            INSERT INTO user_name_tbl (name_id, fname_fld, lname_fld) VALUES
            (1020, 'Jasmin Raine', 'Dela Cruz'),
            (1021, 'James Lawrence', 'Dela Cruz');

            INSERT INTO user_account_tbl (account_id, name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role) VALUES
            (21, 1020, 'JRD@gmail.com', '\$2y\$12\$IZslXVM7nusLXtxnhTCNbuOIwxHfb7eFrcDZU1l4WXz93CF6WLzaq', 'jFtxfgNRlW719FQ=', 'H6rIYi//RMGapX2c', 'E7CB+AEC0pUt3zVQ9cQ8UA==', 'user'),
            (22, 1021, 'jazrencell@gmail.com', '\$2y\$12\$Q8J123finsbAbaeyHFGvtOPt11NJuK30yUCrkBD7Xz7HSvb.2c2rm', 'am9MDdLUxWi1qWg=', 'R3UR9ElUOFb0Rrgq', 'g+HIK2Onc0du/Leo3JVmLg==', 'admin');

            INSERT INTO categories_tbl (category_id, category_name_fld) VALUES
            (1, 'Fantasy'),
            (2, 'Self-help'),
            (3, 'History'),
            (4, 'Children'),
            (5, 'Classic'),
            (6, 'Poetry'),
            (7, 'Science'),
            (8, 'Science Fiction'),
            (9, 'Thriller');

            INSERT INTO books_tbl (book_id, title_fld, author_fld, description_fld, isbn_fld, price_fld, stock_qty_fld, book_cover_image) VALUES
            (3, 'The Hobbit', 'J.R.R. Tolkien', 'A comfortable, stay-at-home hobbit is swept into an epic quest to reclaim a lost dwarf kingdom from a fearsome dragon.', '9780547928227', 650.00, 40, 'book_1777008205_2c16ce23a86189fc.jpeg'),
            (4, 'Atomic Habits', 'James Clear', 'A practical and proven framework for improving every day by forming good habits and breaking bad ones.', '9780735211292', 1195.00, 84, 'book_1777096179_1a2d15b4195784bd.jpeg'),
            (5, 'A Brief History of Humankind', 'Yuval Noah Harari', 'An exploration of how biology and history have defined us and enhanced our understanding of what it means to be human.', '9780062316097', 1149.99, 12, 'book_1777096154_2770b70bd6267c0b.jpeg'),
            (6, 'Where the Wild Things Are', 'Maurice Sendak', 'A young boy named Max journeys to an island of terrifying but easily tamed beasts after being sent to bed without supper.', '9780060254926', 450.00, 17, 'book_1777096092_a5d80d0c7eb79de2.jpeg'),
            (8, 'To Kill a Mockingbird', 'Harper Lee', 'A gripping, heart-wrenching, and wholly remarkable tale of coming-of-age in a South poisoned by virulent prejudice.', '9780060935467', 550.00, 54, 'book_1777096240_4b2b409e1a67eb31.jpeg'),
            (9, 'The Sun and Her Flowers', 'Rupi Kaur', 'A vibrant and transcendent journey about growth and healing, ancestry and honoring one''s roots.', '9781449486792', 799.00, 22, 'book_1777096311_dc1a089f7e488f76.jpeg'),
            (10, 'Pride and Prejudice', 'Jane Austen', 'A classic novel following the turbulent relationship between Elizabeth Bennet and the haughty Mr. Darcy.', '9780141439518', 350.00, 59, 'book_1777096372_f5658d351df78d9a.jpeg'),
            (11, 'A Brief History of Time', 'Stephen Hawking', 'A landmark volume in science writing that explores fundamental questions about the universe, time, and space.', '9780553380163', 850.00, 14, 'book_1777096434_2ae1980d293a17a1.jpeg'),
            (12, 'Dune', 'Frank Herbert', 'Set on the desert planet Arrakis, this epic tale follows young Paul Atreides as he navigates a complex political and ecological landscape.', '9780441172719', 699.00, 75, 'book_1777183910_1480d2ecdb830111.jpeg'),
            (13, 'The Girl with the Dragon', 'Stieg Larsson', 'A disgraced journalist and a brilliant but troubled hacker team up to solve a decades-old disappearance.', '9780307949486', 599.00, 24, 'book_1777096617_e033c28ef7f5741e.jpeg');

            INSERT INTO book_categories_tbl (book_id, category_id) VALUES
            (3, 1), (4, 2), (5, 3), (6, 4), (8, 5), (9, 6), (10, 5), (11, 7), (12, 8), (13, 9);
        ");

        $sqlite->exec("DELETE FROM sqlite_sequence WHERE name IN ('user_name_tbl','user_account_tbl','categories_tbl','books_tbl')");
    }

    $adminStmt = $sqlite->prepare('SELECT account_id FROM user_account_tbl WHERE email = :email');
    $adminStmt->execute([':email' => 'admin@example.com']);
    if (!$adminStmt->fetchColumn()) {
        $sqlite->exec("INSERT INTO user_name_tbl (fname_fld, lname_fld) VALUES ('System', 'Admin')");
        $nameId = intval($sqlite->lastInsertId());
        $insertAdmin = $sqlite->prepare("
            INSERT INTO user_account_tbl (name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role)
            VALUES (:name_id, :email, :password_hash, :phone_encrypted, :phone_iv, :phone_tag, 'admin')
        ");
        $insertAdmin->execute([
            ':name_id' => $nameId,
            ':email' => 'admin@example.com',
            ':password_hash' => password_hash('Admin@123', PASSWORD_BCRYPT),
            ':phone_encrypted' => 'am9MDdLUxWi1qWg=',
            ':phone_iv' => 'R3UR9ElUOFb0Rrgq',
            ':phone_tag' => 'g+HIK2Onc0du/Leo3JVmLg==',
        ]);
    }
}

/**
 * ESTABLISH DATABASE CONNECTION
 * Uses PDO (PHP Data Objects) for secure prepared statements
 * Prepared statements prevent SQL injection attacks
 */
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $mysqlException) {
    try {
        $conn = new PDO('sqlite:' . SQLITE_DB_PATH);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->exec('PRAGMA foreign_keys = ON');
        seedSqliteFallbackDatabase($conn);
    } catch (PDOException $sqliteException) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $mysqlException->getMessage()
        ]);
        exit();
    }
}
?>
