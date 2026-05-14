<?php
// Very simple test - no includes, direct connection test
// Use header() function to set Content-Type response header to JSON
header("Content-Type: application/json; charset=utf-8");

// Use error_log() function to log messages to error file
// Parameters: message, message_type (3 = append to specified file), file path
// Use date("Y-m-d H:i:s") to format current date/time as readable string
error_log("Test connection started at " . date("Y-m-d H:i:s"), 3, __DIR__ . "/error.log");

try {
    // Use error_log() to log connection attempt to file
    error_log("Attempting PDO connection...", 3, __DIR__ . "/error.log");
    
    // Use string concatenation (.) operator to build DSN connection string
    $dsn = 'mysql:host=localhost;dbname=bookstore_db;charset=utf8mb4';
    // Use new PDO() constructor to create database connection object
    $conn = new PDO($dsn, 'root', '');
    // Use $conn->setAttribute() to enable exception mode for error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Use error_log() to confirm successful connection
    error_log("Connection successful!", 3, __DIR__ . "/error.log");
    
    // Test that tables exist
    // Use $conn->query() to execute SQL SELECT query
    $result = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='bookstore_db'");
    // Use $result->fetchAll(PDO::FETCH_ASSOC) to retrieve all rows as associative array
    // PDO::FETCH_ASSOC means each row is an associative array with column names as keys
    $tables = $result->fetchAll(PDO::FETCH_ASSOC);
    // Use array_map() function to transform array
    // Function parameter: anonymous function using function() keyword
    // Extracts 'TABLE_NAME' from each table row
    $tableNames = array_map(function($t) { return $t['TABLE_NAME']; }, $tables);
    
    // Use error_log() with json_encode() to log table names to file
    error_log("Tables found: " . json_encode($tableNames), 3, __DIR__ . "/error.log");
    
    // Use json_encode() to convert PHP array to JSON response
    echo json_encode([
        'success' => true,
        'connection' => 'OK',
        'tables' => $tableNames
    ]);
    
} catch (Exception $e) {
    // Use catch block to handle any exceptions
    // Use error_log() to log exception message to file
    // Use $e->getMessage() method to get error message from exception object
    error_log("Exception: " . $e->getMessage(), 3, __DIR__ . "/error.log");
    // Use http_response_code() to set HTTP status code to 500 Server Error
    http_response_code(500);
    // Use json_encode() to return error response as JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>