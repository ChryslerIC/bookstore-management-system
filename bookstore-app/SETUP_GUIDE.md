# Bookstore App - Setup Guide

## Project Overview
This is a full-stack application demonstrating React frontend with PHP backend authentication using MySQL.

**Features:**
- Landing page with navigation
- User registration with password validation
- User login with credentials
- Protected home page
- Logout functionality

## Prerequisites
- Node.js & npm
- PHP 7.2+
- MySQL/MariaDB
- XAMPP or similar local server environment

---

## Frontend Setup (React + Vite)

### 1. Install Dependencies
```bash
cd bookstore-app
npm install
```

### 2. Run Development Server
```bash
npm run dev
```
The app will be available at `http://localhost:5173`

### 3. Build for Production
```bash
npm run build
```

---

## Backend Setup (PHP + MySQL)

### 1. Create Database
Open phpMyAdmin or MySQL command line and run:

```sql
CREATE DATABASE bookstore_db;
USE bookstore_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Configure PHP Backend
Update `backend/db.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASSWORD', '');          // Your MySQL password
define('DB_NAME', 'bookstore_db');
```

### 3. Start a Local PHP Server
Navigate to the project root and run:
```bash
php -S localhost:8000
```

This serves the backend at `http://localhost:8000`

---

## API Endpoints

### Register User
**POST** `http://localhost:8000/backend/register.php`
```json
{
  "fname": "Admin Lawrence",
  "lname": "Dela Cruz",
  "email": "Admin@gmail.com",
  "phone": "09876543210",
  "password": "adminako0612"
}
```

**Response (Success):**
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

### Login User
**POST** `http://localhost:8000/backend/login.php`
```jsonAdmin@gmail.com",
  "password": "adminako0612
  "email": "john@example.com",
  "password": "Password@123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "token_string_here",
  "user": {
    "account_id": 1,
    "fname": "Admin Lawrence",
    "lname": "Dela Cruz",
    "email": "Admin@gmail.com"
  }
}
```

---

## Password Validation Rules
Passwords must contain:
- ✓ Minimum 8 characters
- ✓ At least 1 uppercase letter (A-Z)
- ✓ At least 1 number (0-9)
- ✓ At least 1 symbol (!@#$%^&* etc.)

Example valid password: `SecurePass@123`

---

## Application Routes

| Route | Purpose | Authentication |
|-------|---------|-----------------|
| `/` | Landing page | Public |
| `/register` | User registration | Public |
| `/login` | User login | Public |
| `/home` | Dashboard (protected) | Required |

---

## Demo Script (20 minutes)

### 1. Start Application (1 min)
- Open terminal and run `npm run dev`
- Open browser to `http://localhost:5173`
- Refresh page to confirm `/` is landing page

### 2. Test Registration (3 min)
- Click "Register" button
- Enter username, email
- Try weak password (e.g., "weak") → See validation errors
- Enter valid password → See success message
- Click link to login page

### 3. Test Login (2 min)
- Enter unregistered email → See "User not registered" warning
- Enter registered credentials → Redirect to `/home`

### 4. Test Protection (2 min)
- From `/home`, note the logout button
- Open new tab and navigate to `http://localhost:5173/home` directly
- Confirm redirect to `/login` (protected route works)
- Logout and try accessing `/home` again

### 5. Show Code (10 min)
- **Frontend:**
  - `src/pages/Register.tsx` - Registration with validation
  - `src/pages/Login.tsx` - Login logic
  - `src/components/ProtectedRoute.tsx` - Route protection
  - `src/App.tsx` - Routing setup
  
- **Backend:**
  - `backend/db.php` - Database connection
  - `backend/register.php` - Registration endpoint
  - `backend/login.php` - Login endpoint
  
- **Database:**
  - Show `users` table with registered users
  - Demonstrate hashed passwords with `password_hash()`

---

## Troubleshooting

### CORS Errors
Ensure all PHP files have CORS headers:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
```

### Database Connection Failed
- Check MySQL is running
- Verify database credentials in `backend/db.php`
- Ensure `bookstore_db` database exists

### React Routing Not Working
- Ensure `react-router-dom` is installed: `npm install react-router-dom`
- Restart dev server after installing packages

### Login Token Issues
- Clear localStorage: Open DevTools → Console → `localStorage.clear()`
- Refresh the page and try again

---

## Project Structure
```
bookstore-app/
├── src/
│   ├── pages/
│   │   ├── Landing.tsx
│   │   ├── Register.tsx
│   │   ├── Login.tsx
│   │   └── Home.tsx
│   ├── components/
│   │   └── ProtectedRoute.tsx
│   ├── styles/
│   │   ├── Landing.css
│   │   ├── Auth.css
│   │   └── Home.css
│   ├── App.tsx
│   └── main.tsx
├── backend/
│   ├── db.php
│   ├── register.php
│   └── login.php
└── package.json
```

---

## Notes for Group Presentation
- **All members must present** - distribute the demo tasks
- **Show live testing** - don't just read slides
- **Include video walkthrough** - record both frontend and backend
- **Highlight key features:**
  - Password validation (security aspect)
  - Protected routes (authentication)
  - CORS implementation (API integration)
  - Database interaction (SQL)
