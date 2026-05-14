# Admin Login Setup Guide

## Overview
Admin login functionality has been successfully implemented! Admins now have a separate login page and dashboard from regular users.

## Implementation Summary

### Backend Changes
1. **New Endpoint**: `backend/admin-login.php`
   - Authenticates admin users based on email and password
   - Validates that user has `role = 'admin'` in the database
   - Returns admin authentication token and user data
   - Uses bcrypt password verification and encryption utilities

### Frontend Changes
1. **New Pages**:
   - `src/pages/AdminLogin.tsx` - Admin login form
   - `src/pages/AdminDashboard.tsx` - Admin dashboard/home page

2. **New Components**:
   - `src/components/AdminProtectedRoute.tsx` - Route protection for admin pages (similar to ProtectedRoute but checks for `adminAuthToken`)

3. **Updated Files**:
   - `src/App.tsx` - Added admin routes (`/admin/login` and `/admin/dashboard`)
   - `src/pages/Landing.tsx` - Added "Admin Login" button
   - `src/styles/Home.css` - Added admin dashboard styling and button classes

### Routes
- **`/admin/login`** - Admin login page (unprotected, accessible to everyone)
- **`/admin/dashboard`** - Admin dashboard (protected, requires adminAuthToken)

### Authentication Storage
- **Regular Users**: Store `authToken` and `user` in localStorage
- **Admins**: Store `adminAuthToken` and `admin` in localStorage

## Setting Up Admin Users

### Method 1: Direct Database Insert (PHP Script)
Create a file `backend/create-admin.php`:

```php
<?php
require_once 'db.php';
require_once 'encryption.php';

try {
    // Admin details
    $fname = 'Admin';
    $lname = 'User';
    $email = 'admin@bookstore.com';
    $phone = '555-1234';
    $password = 'AdminPassword123!';
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT account_id FROM user_account_tbl WHERE email = :email");
    $checkStmt->execute([':email' => $email]);
    
    if ($checkStmt->rowCount() > 0) {
        echo "Email already registered";
        exit();
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert name
    $nameStmt = $conn->prepare("INSERT INTO user_name_tbl (fname_fld, lname_fld) VALUES (:fname, :lname)");
    $nameStmt->execute([':fname' => $fname, ':lname' => $lname]);
    $nameId = $conn->lastInsertId();
    
    // Encrypt phone
    $encryptedPhoneData = EncryptionUtil::encryptForStorage($phone);
    
    // Insert admin account with role = 'admin'
    $insertStmt = $conn->prepare(
        "INSERT INTO user_account_tbl (name_id, email, password_hash, phone_encrypted, phone_iv, phone_tag, role) 
         VALUES (:name_id, :email, :password_hash, :phone_encrypted, :phone_iv, :phone_tag, :role)"
    );
    
    $insertStmt->execute([
        ':name_id' => $nameId,
        ':email' => $email,
        ':password_hash' => $hashedPassword,
        ':phone_encrypted' => $encryptedPhoneData['encrypted'],
        ':phone_iv' => $encryptedPhoneData['iv'],
        ':phone_tag' => $encryptedPhoneData['tag'],
        ':role' => 'admin'  // SET ROLE TO ADMIN
    ]);
    
    echo "Admin user created successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### Method 2: Update Existing User to Admin
If you already have a user in the database, update their role to 'admin':

```sql
UPDATE user_account_tbl 
SET role = 'admin' 
WHERE email = 'user@example.com';
```

### Method 3: Using PhpMyAdmin or Database Client
1. Open your database management tool
2. Find the `user_account_tbl` table
3. Set the `role` column to `'admin'` for desired users

## Testing Admin Login

1. Start your application (both backend and frontend)
2. Navigate to the Landing page: `http://localhost:5173/`
3. Click "Admin Login" button
4. Enter admin credentials:
   - Email: `admin@bookstore.com` (or your admin email)
   - Password: Your admin password
5. If credentials are correct, you'll be redirected to `/admin/dashboard`

## Key Files
- Backend endpoint: [backend/admin-login.php](../../backend/admin-login.php)
- Admin login page: [src/pages/AdminLogin.tsx](../../src/pages/AdminLogin.tsx)
- Admin dashboard: [src/pages/AdminDashboard.tsx](../../src/pages/AdminDashboard.tsx)
- Admin route protection: [src/components/AdminProtectedRoute.tsx](../../src/components/AdminProtectedRoute.tsx)
- Main router: [src/App.tsx](../../src/App.tsx)

## Security Notes
- Admin passwords are hashed using bcrypt (same as user passwords)
- Admin phone numbers are encrypted using AES-256-GCM
- Admin authentication uses separate localStorage keys (`adminAuthToken` vs `authToken`)
- Admin login endpoint checks for `role = 'admin'` in database
- Regular users cannot access admin dashboard even if they know the URL (protected by AdminProtectedRoute)

## Next Steps (Optional)
1. Add admin-only pages (manage users, manage books, view orders, etc.)
2. Add role-based access control (RBAC) middleware
3. Add admin activity logging
4. Create admin management panel to add/edit admin accounts
5. Implement role-based API endpoints that verify admin status
