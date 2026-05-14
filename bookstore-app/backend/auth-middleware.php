<?php
/**
 * auth-middleware.php - Token Verification
 * 
 * HOW TO USE in any protected PHP file:
 * require_once 'auth-middleware.php';
 * $currentUser = authenticate();
 * Then use $currentUser['account_id'], $currentUser['role'], etc.
 */

require_once 'db.php';

function authGetAuthorizationHeaderValue() {
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $headerName => $headerValue) {
            if (strcasecmp($headerName, 'Authorization') === 0) {
                return trim($headerValue);
            }
        }
    }

    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_AUTHORIZATION']);
    }

    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    }

    return '';
}

function authExtractBearerToken($authHeader) {
    if (empty($authHeader)) {
        return '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }

    return trim($authHeader);
}

function authenticate() {
    global $conn;

    // Get token from Authorization header
    $authHeader = authGetAuthorizationHeaderValue();
    $token = authExtractBearerToken($authHeader);

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No token provided. Please login.']);
        exit;
    }

    // Check if token exists and is not expired
    $stmt = dbPrepare($conn, "
        SELECT t.account_id, t.expires_at, ua.role, 
               un.fname_fld, un.lname_fld, ua.email
        FROM user_tokens_tbl t
        JOIN user_account_tbl ua ON t.account_id = ua.account_id
        JOIN user_name_tbl un ON ua.name_id = un.name_id
        WHERE t.token = :token
    ");
    $stmt->execute([':token' => $token]);

    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid token. Please login again.']);
        exit;
    }

    // Check if token is expired
    if (strtotime($tokenData['expires_at']) < time()) {
        // Delete expired token
        $deleteStmt = dbPrepare($conn, "DELETE FROM user_tokens_tbl WHERE token = :token");
        $deleteStmt->execute([':token' => $token]);

        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        exit;
    }

    // Token is valid! Return user info
    return [
        'account_id' => $tokenData['account_id'],
        'fname' => $tokenData['fname_fld'],
        'lname' => $tokenData['lname_fld'],
        'email' => $tokenData['email'],
        'role' => $tokenData['role']
    ];
}

function authenticateAdmin() {
    $user = authenticate();

    // Check if user is admin
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Admins only.']);
        exit;
    }

    return $user;
}
?>
