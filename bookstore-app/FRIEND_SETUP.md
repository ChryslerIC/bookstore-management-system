# How to Run the Bookstore App (For Friends)

## What You Need to Install First

Before running this app, your friends need to have these installed on their computer:

1. **Node.js** (includes npm)
   - Download from: https://nodejs.org/
   - Choose LTS version
   - Verify installation: Open terminal and run `node --version`

2. **PHP 7.2 or higher**
   - Download from: https://www.php.net/downloads
   - OR install via XAMPP: https://www.apachefriends.org/
   - Verify installation: Open terminal and run `php --version`

3. **MySQL/MariaDB**
   - Usually included with XAMPP
   - OR download separately from: https://www.mysql.com/downloads/
   - Verify installation: Open terminal and run `mysql --version`

---

## Step-by-Step Setup Instructions

### Step 1: Extract & Navigate (2 minutes)
```bash
# Extract the zip file
# Open terminal/command prompt
# Navigate to the bookstore-app folder
cd path/to/bookstore-app
```

### Step 2: Install Frontend Dependencies (3 minutes)
```bash
npm install
```
This downloads all React, Router, and build tool packages.

### Step 3: Set Up Database (5 minutes)
1. Start MySQL server (via XAMPP or command line)
2. Open phpMyAdmin at `http://localhost/phpmyadmin/` 
   - OR use MySQL command line
3. Create a new database named `bookstore_db`
4. In the database, run the SQL queries in `backend/database.sql`
   - This creates the `users` table automatically

### Step 4: Start the Backend (Terminal Window 1)
```bash
# In the bookstore-app folder
php -S localhost:8000
```
✓ Backend is now running at `http://localhost:8000`
✓ Keep this terminal open

### Step 5: Start the Frontend (Terminal Window 2)
```bash
# Open a NEW terminal/command prompt
# Navigate to the bookstore-app folder again
cd path/to/bookstore-app

# Run the development server
npm run dev
```
✓ Frontend is now running at `http://localhost:5173`

---

## Accessing the App

Open your browser and go to: **http://localhost:5173**

### Test It Out:
1. **Landing Page** - You'll see the welcome page
2. **Register** - Create a new account
   - Password must have: 8+ chars, uppercase, number, symbol
3. **Login** - Use your registered credentials
4. **Home Page** - See the protected dashboard
5. **Logout** - Click to exit the session

---

## Troubleshooting

### "npm: command not found"
- Node.js is not installed
- Install from: https://nodejs.org/

### "php: command not found"
- PHP is not installed
- Install from: https://www.php.net/ or XAMPP

### "Connection refused on port 8000"
- Backend server not started
- Run: `php -S localhost:8000` in first terminal

### "Connection refused on port 5173"
- Frontend server not started
- Run: `npm run dev` in second terminal

### Database errors
- MySQL not running
- Start MySQL service (XAMPP, Docker, or system service)
- Verify database `bookstore_db` exists
- Check `backend/database.sql` was executed

---

## Summary

| What | Command | Terminal |
|------|---------|----------|
| Install dependencies | `npm install` | Any |
| Start Backend | `php -S localhost:8000` | Terminal 1 |
| Start Frontend | `npm run dev` | Terminal 2 |
| Access App | Visit `http://localhost:5173` | Browser |

---

## Key Points to Remember

✅ Keep BOTH terminals running (backend + frontend)
✅ Database must be set up before running
✅ Use separate terminal windows for backend and frontend
✅ If something breaks, try stopping (Ctrl+C) and restarting servers
✅ All 3 services needed: MySQL, PHP backend, React frontend

---

## File Structure

```
bookstore-app/
├── backend/                    # PHP backend code
│   ├── login.php
│   ├── register.php
│   ├── database.sql           # Database setup script
│   ├── db.php
│   ├── config.php
│   └── ...
├── src/                        # React frontend code
│   ├── pages/
│   ├── components/
│   ├── styles/
│   └── ...
├── package.json               # Node.js dependencies
├── vite.config.ts             # Vite build config
└── README.md                  # Main documentation
```

Enjoy the app! 🎉
