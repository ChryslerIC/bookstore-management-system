# Bookstore App - API Documentation

## Base URL
```
http://localhost:8000/backend/
```

## CORS Policy
All endpoints support CORS. Requests from `http://localhost:5173` (React dev server) are allowed.

### CORS Headers Sent with Every Response
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

---

## Endpoints

### 1. User Registration
Register a new user with credentials.

**Endpoint:**
```
POST /register.php
```

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "fname": "Admin Lawrence",
  "lname": "Dela Cruz",
  "email": "Admin@gmail.com",
  "phone": "09876543210",
  "password": "adminako0612"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "fname": "Admin Lawrence",
    "lname": "Dela Cruz",
    "email": "Admin@gmail.com",
    "phone": "09876543210"
  }
}
```

**Error Responses:**

Empty Fields (400):
```json
{
  "success": false,
  "message": "Missing required fields"
}
```

User Already Exists (400):
```json
{
  "success": false,
  "message": "Email or username already exists"
}
```

Database Error (500):
```json
{
  "success": false,
  "message": "Error registering user: [error details]"
}
```

---

### 2. User Login
Authenticate user with email and password.

**Endpoint:**
```
POST /login.php
```

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```jsonAdmin@gmail.com",
  "password": "adminako0612
  "email": "john@example.com",
  "password": "SecurePass@123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
  "user": {
    "account_id": 1,
    "fname": "Admin Lawrence",
    "lname": "Dela Cruz",
    "email": "Admin@gmail.com"
  }
}
```

**Error Responses:**

User Not Found (401):
```json
{
  "success": false,
  "message": "User not registered"
}
```

Invalid Credentials (401):
```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

Missing Fields (400):
```json
{
  "success": false,
  "message": "Missing required fields"
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid input or missing fields |
| 401 | Unauthorized - Invalid credentials or user not found |
| 405 | Method Not Allowed - Wrong HTTP method (must be POST) |
| 500 | Server Error - Database or server issue |

---

## Authentication Flow

### Registration Process
1. User submits username, email, password
2. Backend validates:
   - All fields present
   - Email and username unique
3. Password hashed using `password_hash()` with BCRYPT
4. User inserted into database
5. Success message returned to frontend

### Login Process
1. User submits email and password
2. Backend queries database for user by email
3. If user not found → "User not registered" error
4. If found, verify password using `password_verify()`
5. If match → Generate token and return user data
6. Frontend stores token in `localStorage.authToken`

### Protected Routes
1. React checks `localStorage.authToken` before allowing access
2. If token missing → Redirect to login
3. If token present → Allow access to protected page
4. On logout → Clear token from localStorage

---

## Security Notes

### Password Security
- Passwords are hashed using **bcrypt** (`PASSWORD_BCRYPT`)
- Never stored in plain text
- Verified using `password_verify()` function

### Token System
- Simple random token generated using `random_bytes(32)`
- In production, use JWT (JSON Web Tokens)
- Token stored in browser's `localStorage`
- Should be sent in Authorization header for API requests

### CORS
- Allows requests from any origin in development
- In production, restrict to specific domains

### SQL Injection Prevention
- Uses `real_escape_string()` on user inputs
- In production, use prepared statements (parameterized queries)

---

## Testing with cURL

### Test Registration
```bash
curl -X POST http://localhost:8000/backend/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "Test@12345"
  }'
```

### Test Login
```bash
curl -X POST http://localhost:8000/backend/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Test@12345"
  }'
```

### Test CORS (Preflight Request)
```bash
curl -X OPTIONS http://localhost:8000/backend/register.php \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: POST"
```

---

## Database Schema

### users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Field Descriptions
| Field | Type | Description |
|-------|------|-------------|
| id | INT | Auto-incrementing primary key |
| username | VARCHAR(50) | Unique username for account |
| email | VARCHAR(100) | Unique email for account |
| password | VARCHAR(255) | Bcrypt hashed password |
| created_at | TIMESTAMP | Account creation time |
| updated_at | TIMESTAMP | Last update time |

---

## Example Frontend Integration (React)

### Register
```javascript
const response = await fetch('http://localhost:8000/backend/register.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ username, email, password })
});
const data = await response.json();
if (data.success) {
  navigate('/login');
}
```

### Login
```javascript
const response = await fetch('http://localhost:8000/backend/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});
const data = await response.json();
if (data.success) {
  localStorage.setItem('authToken', data.token);
  localStorage.setItem('user', JSON.stringify(data.user));
  navigate('/home');
}
```

---

## Rate Limiting Recommendations
For production, implement rate limiting on:
- `/register.php` - Limit to 5 requests per IP per hour
- `/login.php` - Limit to 10 failed attempts per IP per 30 minutes

---

## Future Enhancements
- [ ] JWT token implementation
- [ ] Account verification via email
- [ ] Password reset functionality
- [ ] User profile management
- [ ] Session timeout
- [ ] Two-factor authentication
- [ ] Role-based access control (RBAC)
