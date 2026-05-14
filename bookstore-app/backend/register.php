<?php
/**
 * register.php - User Registration API Endpoint
 * 
 * PURPOSE: Handles user registration requests from React frontend
 * - Validates required fields
 * - Checks if email already exists
 * - Hashes password using bcrypt
 * - Inserts user into two tables:
 *   1. user_name_tbl (first name, last name)
 *   2. user_account_tbl (email, password, phone, role)
 * - Encrypts phone number before database storage
 * - Returns success/failure response as JSON
 * 
 * ENDPOINT: POST /backend/register.php
 * REQUEST BODY: {fname, lname, email, phone, password}
 * RESPONSE: {success: boolean, message: string, user: object}
 */

// Include database connection and encryption utilities
require_once 'db.php';
require_once 'encryption.php';

try {
    /**
     * HANDLE CORS PREFLIGHT REQUEST
     * Browser sends OPTIONS request before POST to check permissions
     */
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    /**
     * MAIN REGISTRATION LOGIC - Only execute for POST requests
     */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Use file_get_contents("php://input") to read raw HTTP request body stream
        // Use json_decode() with true parameter to convert JSON string to PHP associative array
        $data = json_decode(file_get_contents("php://input"), true);

        /**
         * STEP 1: Validate all required fields are present
         * Check that all five fields were provided in the request
         */
        // Use isset() function to check if each array key exists (handles null/missing values)
        if (!isset($data['fname']) || !isset($data['lname']) || !isset($data['email']) || 
            !isset($data['phone']) || !isset($data['password'])) {
            // Wrong! Client didn't send required fields
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        // Extract form data from request array using bracket notation syntax
        $fname = $data['fname'];        // First name
        $lname = $data['lname'];        // Last name
        $email = $data['email'];        // Email address
        $phone = $data['phone'];        // Phone number
        $password = $data['password'];  // Password (will be hashed)

        /**
         * STEP 2: Check if email is already registered
         * Prevent duplicate email registrations
         */
        // Use $conn->prepare() to create prepared statement with :email parameter placeholder
        // Prepared statements prevent SQL injection attacks
        $checkStmt = dbPrepare($conn, "SELECT account_id FROM user_account_tbl WHERE email = :email");
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->fetch()) {
            // Email already exists in database
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit();
        }

        /**
         * STEP 3: Hash the password using bcrypt
         * Bcrypt is a secure one-way hashing algorithm
         * PASSWORD_BCRYPT automatically generates salt and rounds
         * The hashed password will be stored in database (never store plain passwords!)
         */
        // Use password_hash($password, PASSWORD_BCRYPT) to securely hash password
        // PASSWORD_BCRYPT constant specifies the bcrypt algorithm
        // password_hash() automatically generates a unique salt and applies multiple rounds
        // Result is a 60-character hash that cannot be reversed
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        /**
         * STEP 4: Insert user name into user_name_tbl table
         * This table stores first and last names
         * Returns an auto-incremented name_id that we'll use as foreign key
         */
        // Use $conn->prepare() to create prepared statement for INSERT
        $nameStmt = dbPrepare($conn, "INSERT INTO user_name_tbl (fname_fld, lname_fld) VALUES (:fname, :lname)");
        // Use $nameStmt->execute() with parameter binding for safe SQL execution
        $nameStmt->execute([':fname' => $fname, ':lname' => $lname]);
        
        // Use $conn->lastInsertId() to retrieve the auto-generated primary key from INSERT
        // This name_id is the foreign key reference we'll use in user_account_tbl
        $nameId = $conn->lastInsertId();

        /**
         * STEP 5: Encrypt phone number using AES-256-GCM
         * Security: Phone number is encrypted before storage
         * encryptForStorage() returns three base64-encoded components:
         * - encrypted: The ciphertext
         * - iv: Initialization vector (unique per encryption)
         * - tag: Authentication tag (detects tampering)
         */
        // Call static method EncryptionUtil::encryptForStorage() to encrypt phone
        // Returns associative array with 'encrypted', 'iv', 'tag' keys
        $encryptedPhoneData = EncryptionUtil::encryptForStorage($phone);

        /**
         * STEP 6: Insert user account into user_account_tbl table
         * This table stores:
         * - name_id: Foreign key reference to user_name_tbl
         * - email: User's email (unique)
         * - password_hash: Bcrypt hashed password
         * - phone_encrypted, phone_iv, phone_tag: Encrypted phone components
         * - role: User role (default: 'user')
         */
        // Use $conn->prepare() to create prepared statement for INSERT
        $insertStmt = dbPrepare($conn,
            "INSERT INTO user_account_tbl (name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role) 
             VALUES (:name_id, :email, :password_hash, :phone_encrypted, :phone_iv, :phone_tag, 'user')"
        );
        
        // Use $insertStmt->execute() with associative array of parameters
        // Each parameter placeholder :name is bound to its corresponding value
        $insertStmt->execute([
            ':name_id' => $nameId,
            ':email' => $email,
            ':password_hash' => $hashedPassword,
            ':phone_encrypted' => $encryptedPhoneData['encrypted'],  // Use bracket notation to access array element
            ':phone_iv' => $encryptedPhoneData['iv'],
            ':phone_tag' => $encryptedPhoneData['tag']
        ]);

        /**
         * STEP 7: Return success response
         * HTTP 201 = Created (new resource created)
         * Note: We mask the phone number in response for security
         */
        // Use http_response_code(201) to set HTTP status code to 201 Created
        http_response_code(201);
        // Use json_encode() to convert PHP associative array to JSON string response
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'phone' => '***-****' // Masked for security (don't send plain phone to frontend)
            ]
        ]);
    } else {
        // Request was not POST, reject it
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    // Any database or encryption error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Registration error: ' . $e->getMessage()
    ]);
}
?>
