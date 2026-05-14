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
            WHERE ua.email = :email AND ua.role = 'admin'
        ");
        $stmt->execute([':email' => $email]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Admin user not found or invalid credentials']);
            exit;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        // Decrypt phone
        $decryptedPhone = '';
        try {
            if (!empty($admin['phone_encrypted']) && !empty($admin['phone_iv']) && !empty($admin['phone_tag'])) {
                $decryptedPhone = EncryptionUtil::decryptFromStorage(
                    $admin['phone_encrypted'],
                    $admin['phone_iv'],
                    $admin['phone_tag']
                );
            }
        } catch (Exception $e) {
            $decryptedPhone = '';
        }

        // Generate token
        $token = bin2hex(random_bytes(32));

        // Token expires in 24 hours
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Delete any old tokens for this admin first
        $deleteOld = dbPrepare($conn, "DELETE FROM user_tokens_tbl WHERE account_id = :account_id");
        $deleteOld->execute([':account_id' => $admin['account_id']]);

        // Save new token to database
        $tokenStmt = dbPrepare($conn, "
            INSERT INTO user_tokens_tbl (account_id, token, expires_at) 
            VALUES (:account_id, :token, :expires_at)
        ");
        $tokenStmt->execute([
            ':account_id' => $admin['account_id'],
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Admin login successful',
            'token' => $token,
            'admin' => [
                'account_id' => $admin['account_id'],
                'fname' => $admin['fname_fld'],
                'lname' => $admin['lname_fld'],
                'email' => $admin['email'],
                'phone' => $decryptedPhone,
                'role' => $admin['role']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
