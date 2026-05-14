<?php
require_once 'db.php';
require_once 'encryption.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $email = $data['email'];
        $password = $data['password'];

        $stmt = dbPrepare($conn, "
            SELECT ua.account_id, ua.email, ua.password_hash, ua.role, 
                   ua.phone_encrypted, ua.phone_iv, ua.phone_tag, 
                   un.fname_fld, un.lname_fld 
            FROM user_account_tbl ua
            LEFT JOIN user_name_tbl un ON ua.name_id = un.name_id
            WHERE ua.email = :email
        ");
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not registered']);
            exit;
        }

        if (!password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        if (isset($user['role']) && $user['role'] === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'This account is an admin account. Please use Admin Login.']);
            exit;
        }

        // Decrypt phone
        $decryptedPhone = '';
        try {
            if (!empty($user['phone_encrypted']) && !empty($user['phone_iv']) && !empty($user['phone_tag'])) {
                $decryptedPhone = EncryptionUtil::decryptFromStorage(
                    $user['phone_encrypted'],
                    $user['phone_iv'],
                    $user['phone_tag']
                );
            }
        } catch (Exception $e) {
            $decryptedPhone = '';
        }

        // Generate token
        $token = bin2hex(random_bytes(32));

        // Token expires in 24 hours
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Delete any old tokens for this user first
        $deleteOld = dbPrepare($conn, "DELETE FROM user_tokens_tbl WHERE account_id = :account_id");
        $deleteOld->execute([':account_id' => $user['account_id']]);

        // Save new token to database
        $tokenStmt = dbPrepare($conn, "
            INSERT INTO user_tokens_tbl (account_id, token, expires_at) 
            VALUES (:account_id, :token, :expires_at)
        ");
        $tokenStmt->execute([
            ':account_id' => $user['account_id'],
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'account_id' => $user['account_id'],
                'fname' => $user['fname_fld'],
                'lname' => $user['lname_fld'],
                'email' => $user['email'],
                'phone' => $decryptedPhone
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Login error: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
