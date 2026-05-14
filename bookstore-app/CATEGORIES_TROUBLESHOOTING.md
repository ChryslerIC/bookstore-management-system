# Troubleshooting Guide: Categories 500 Error

## Problem
Getting `500 Internal Server Error` when fetching categories from `http://localhost:8000/backend/get-categories.php`

## Root Causes & Solutions

### 1. **Database Not Connected**
**Symptoms:** 500 error, categories list won't load

**Solutions:**
- Verify MySQL server is running
- Check database credentials in `backend/db.php`
- Test connection: Visit `http://localhost:8000/backend/health-check.php`

**Check Health:**
```
GET http://localhost:8000/backend/health-check.php
```
This will show:
- Database connection status
- Available tables
- Category count

### 2. **Categories Table Empty**
**Symptoms:** Categories load but dropdown is empty

**Solution:**
Click "Initialize Categories" button in the book creation form, or manually run:
```
GET/POST http://localhost:8000/backend/init-categories.php
```

This will create standard book categories:
- Fiction, Non-Fiction, Science Fiction, Mystery, Romance, Thriller, Fantasy, Biography, History, Self-Help, Technology, Business, Children, Young Adult

### 3. **Categories Table Doesn't Exist**
**Symptoms:** Database connected but categories_tbl missing

**Solutions:**
Verify the table exists in your database. If not, create it:

```sql
CREATE TABLE IF NOT EXISTS categories_tbl (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Then run initialization:
```
GET http://localhost:8000/backend/init-categories.php
```

### 4. **Database User Permissions**
**Symptoms:** Connection works but query fails

**Solution:**
Ensure the MySQL user has SELECT permissions on `categories_tbl`:
```sql
GRANT SELECT ON bookstore_db.categories_tbl TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

## Quick Fixes

### Step 1: Check Health Status
Visit: `http://localhost:8000/backend/health-check.php`

Example response:
```json
{
  "status": "healthy",
  "database": {"status": "connected"},
  "tables": {"status": "accessible", "list": ["categories_tbl", "books_tbl", ...]},
  "categories": {"status": "ok", "count": 14}
}
```

### Step 2: If Categories Count = 0
Click "Initialize Categories" button in book management form, or run:
```
http://localhost:8000/backend/init-categories.php
```

### Step 3: Check Browser Console
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for detailed error messages
4. Copy error and check PHP error logs

## Check PHP Error Logs

If you have access to server logs:

**Windows (XAMPP):**
```
C:\xampp\apache\logs\error.log
C:\xampp\apache\logs\access.log
```

**Linux (Apache):**
```
/var/log/apache2/error.log
```

**PHP Log:**
```
Check php.ini for error_log path
```

## Endpoints for Debugging

### 1. Health Check
```
GET http://localhost:8000/backend/health-check.php
```
Shows full diagnostic info

### 2. Get Categories
```
GET http://localhost:8000/backend/get-categories.php
```
Returns all categories or error details

### 3. Initialize Categories
```
GET/POST http://localhost:8000/backend/init-categories.php
```
Creates default categories if missing

## Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Failed to prepare statement" | Database not connected | Check db.php credentials |
| "No categories found" | Table empty | Click "Initialize Categories" |
| "Table doesn't exist" | categories_tbl missing | Create table, run init |
| "Connection refused" | MySQL not running | Start MySQL server |
| "Access denied for user" | Wrong credentials | Update db.php |

## Manual Database Setup

If you need to manually set up categories:

```sql
-- Create categories table
CREATE TABLE categories_tbl (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories_tbl (category_name) VALUES
('Fiction'),
('Non-Fiction'),
('Science Fiction'),
('Mystery'),
('Romance'),
('Thriller'),
('Fantasy'),
('Biography'),
('History'),
('Self-Help'),
('Technology'),
('Business'),
('Children'),
('Young Adult');

-- Verify insertion
SELECT * FROM categories_tbl;
```

## Testing the Fix

1. **Visit Health Check:**
   - Go to `http://localhost:8000/backend/health-check.php`
   - Verify `"status": "healthy"`
   - Check `categories.count > 0`

2. **Reload Book Management:**
   - Navigate to Admin Dashboard
   - Click "Go to Books"
   - Category dropdown should now be populated

3. **Create Test Book:**
   - Fill book form with test data
   - Category dropdown should work
   - Create book successfully

## Still Having Issues?

1. Check browser DevTools Console (F12)
2. Check PHP error logs (see paths above)
3. Visit `http://localhost:8000/backend/health-check.php` for diagnostics
4. Verify MySQL is running and accessible
5. Check database credentials in `backend/db.php`

## File Locations

| File | Purpose |
|------|---------|
| `backend/get-categories.php` | Fetch categories (fixed with error handling) |
| `backend/init-categories.php` | Create default categories |
| `backend/health-check.php` | Diagnose database issues |
| `backend/db.php` | Database connection config |
| `src/pages/ManageBooks.tsx` | Book management UI |
