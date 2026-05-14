<?php
// Use header() function to set Content-Type response header
header("Content-Type: application/json");

// Use get_loaded_extensions() function to retrieve array of all loaded PHP extensions
$extensions = get_loaded_extensions();
// Use in_array() to check if 'PDO' string is in $extensions array
// Returns boolean: true if found, false if not found
$pdoLoaded = in_array('PDO', $extensions);
// Use in_array() to check if 'mysqlnd' extension is loaded
$mysqlndLoaded = in_array('mysqlnd', $extensions);
// Use in_array() to check if 'pdo_mysql' extension is loaded
$pdoMysqlLoaded = in_array('pdo_mysql', $extensions);

// Use json_encode() to convert PHP array to JSON response
echo json_encode([
    'pdo_available' => $pdoLoaded,
    'mysqlnd_available' => $mysqlndLoaded,
    'pdo_mysql_available' => $pdoMysqlLoaded,
    // Use array_filter() to filter array items that match condition function
    // Use array_values() to re-index array numerically after filtering
    // Chain these functions together for complex array transformation
    // Use strpos() to search for substring within string (returns position or false)
    // Use strtolower() to convert string to lowercase
    // Condition: keep extension if name contains 'sql' OR 'pdo' (case-insensitive)
    'available_extensions' => array_values(array_filter($extensions, function($ext) {
        // Use || operator for logical OR (true if either condition is true)
        // Use !== operator for "not equal" comparison (strict)
        return strpos(strtolower($ext), 'sql') !== false || strpos(strtolower($ext), 'pdo') !== false;
    }))
]);
?>
