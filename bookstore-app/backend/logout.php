<?php
require_once 'db.php';

function logoutGetAuthorizationHeaderValue() {
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

function logoutExtractToken($authHeader) {
    if (empty($authHeader)) {
        return null;
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }

    return trim($authHeader);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = logoutExtractToken(logoutGetAuthorizationHeaderValue());

        if (!$token) {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['token'])) {
                $token = trim($data['token']);
            }
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }

        // Delete token from database
        $stmt = dbPrepare($conn, "DELETE FROM user_tokens_tbl WHERE token = :token");
        $stmt->execute([':token' => $token]);

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Logout error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
