<?php
// Use header() function to set CORS headers allowing cross-origin requests
header("Access-Control-Allow-Origin: *");
// Use header() to set Content-Type response header to JSON format
header("Content-Type: application/json");
// Use header() to specify which headers are allowed in requests
header("Access-Control-Allow-Headers: Content-Type");

// Use ini_set() to configure PHP error handling
// Hide errors from output (don't display to users)
ini_set('display_errors', 0);
// Use ini_set() with error_log setting and __DIR__ magic constant to log errors
ini_set('error_log', __DIR__ . '/error.log');

try {
    // Use string concatenation (.) operator to build DSN for database connection
    $dsn = 'mysql:host=localhost;dbname=bookstore_db;charset=utf8mb4';
    // Use new PDO() constructor to create database connection
    $conn = new PDO($dsn, 'root', '');
    // Use $conn->setAttribute() to enable exception error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Use array literal [] to create list of table names to check
    $tables = ['user_name_tbl', 'user_account_tbl'];
    // Use array literal [] to initialize empty array for missing tables
    $missing_tables = [];

    // Use foreach loop to iterate over each table name in $tables array
    foreach ($tables as $table) {
        // Use $conn->query() to execute SQL SHOW TABLES query
        // Returns PDOStatement object with query results
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        // Use $stmt->rowCount() to check how many rows (tables) were found
        // If rowCount() == 0, the table doesn't exist
        if ($stmt->rowCount() == 0) {
            // Use array .push() pattern: $array[] = value to add item to array
            $missing_tables[] = $table;
        }
    }

    // Use count() function to get number of items in $missing_tables array
    if (count($missing_tables) > 0) {
        // Use json_encode() to convert PHP array to JSON string
        echo json_encode([
            'status' => 'TABLES_MISSING',
            'message' => 'Database exists but tables are missing',
            'missing_tables' => $missing_tables,
            'instructions' => 'Create the tables using the SQL schema provided'
        ]);
        exit();
    }

    // Use json_encode() to return success response as JSON
    echo json_encode([
        'status' => 'SUCCESS',
        'message' => 'Database and tables are ready',
        'database' => 'bookstore_db',
        'tables' => $tables,
        'raw_api_endpoint' => 'http://localhost:8000/backend/register.php'
    ]);
} catch (Exception $e) {
    // Use catch block to handle any exceptions thrown during execution
    http_response_code(500);
    // Use $e->getMessage() method to get error message from exception object
    // Use . operator for string concatenation
    echo json_encode([
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
