# Quick Start Guide

## 3-Step Setup

### Step 1: Set Up Database (5 mins)
1. Open phpMyAdmin or MySQL CLI
2. Copy & paste contents of `backend/database.sql`
3. Run the SQL query
4. Verify `bookstore_db` created with `users` table

### Step 2: Start Backend (1 min)
```bash
# In project root directory
php -S localhost:8000
```
✓ Backend running at `http://localhost:8000`

### Step 3: Start Frontend (1 min)
```bash
# In new terminal window
npm run dev
```
✓ App running at `http://localhost:5173`

---

## File Locations Reference

```
bookstore-app/
│
├─ Frontend (React)
│  ├─ src/
│  │  ├─ pages/
│  │  │  ├─ Landing.tsx          ← Landing page
│  │  │  ├─ Register.tsx         ← Registration with validation
│  │  │  ├─ Login.tsx            ← Login form
│  │  │  └─ Home.tsx             ← Protected dashboard
│  │  ├─ components/
│  │  │  └─ ProtectedRoute.tsx   ← Route protection logic
│  │  ├─ styles/
│  │  │  ├─ Landing.css
│  │  │  ├─ Auth.css
│  │  │  └─ Home.css
│  │  ├─ App.tsx                 ← Routing configuration
│  │  └─ main.tsx
│  └─ package.json
│
├─ Backend (PHP)
│  └─ backend/
│     ├─ db.php                  ← Database connection
│     ├─ register.php            ← Register endpoint
│     ├─ login.php               ← Login endpoint
│     └─ database.sql            ← SQL setup script
│
├─ Documentation
│  ├─ SETUP_GUIDE.md             ← Full setup instructions
│  ├─ API_DOCS.md                ← API documentation
│  ├─ ACTIVITY_CHECKLIST.md      ← Requirements checklist
│  └─ QUICK_START.md             ← This file
│
└─ Configuration
   ├─ vite.config.ts
   ├─ tsconfig.json
   └─ eslint.config.js
```

---

## Key URLs

| Service | URL | Status |
|---------|-----|--------|
| Frontend | http://localhost:5173 | React App |
| Backend | http://localhost:8000 | PHP Server |
| phpmyadmin | http://localhost/phpmyadmin | Database Admin |

---

## Test Credentials

After registering in the app, use those credentials to login.

Example test account:
- **First Name:** Admin
- **Last Name:** Lawrence
- **Email:** Admin@gmail.com
- **Phone:** 09876543210
- **Password:** adminako0612

---

## Common Commands

```bash
# Install dependencies
npm install

# Start dev server
npm run dev

# Build for production
npm run build

# Start backend
php -S localhost:8000

# Run linting
npm run lint
```

---

## Password Requirements

```
✓ Min 8 characters
✓ At least 1 UPPERCASE letter
✓ At least 1 number (0-9)
✓ At least 1 symbol (!@#$%^&* etc.)
```

Valid Example: `SecurePass@123`

---

## API Quick Reference

### Register
```
POST http://localhost:8000/backend/register.php
{
  "fname": "Admin Lawrence",
  "lname": "Dela Cruz",
  "email": "Admin@gmail.com",
  "phone": "09876543210",
  "password": "adminako0612"
}
```

### Login
```
POST http://Admin@gmail.com",
  "password": "adminako0612
  "email": "john@example.com",
  "password": "SecurePass@123"
}
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| CORS Error | Restart PHP server, check headers in PHP files |
| DB connection failed | Check db.php credentials, ensure MySQL running |
| Routes not working | Restart dev server: `npm run dev` |
| Port already in use | Change port: `php -S localhost:8001` |
| React Router not found | Run: `npm install react-router-dom` |
| TypeScript errors | Run: `npm run lint` to check errors |

---

## Demo Checklist (Quick)

- [ ] Landing page loads at `/`
- [ ] Register works with validation
- [ ] Login with registered account succeeds
- [ ] Unregistered account shows "User not registered"
- [ ] Protected `/home` redirects if not logged in
- [ ] Logout clears session
- [ ] Refresh `/home` without login → redirect to login

---

## Group Video Submission Items

1. **Recording**: mp4 or webm format (max 20 mins)
2. **Content**:
   - All features working live
   - Code walkthroughs
   - Database showing users
3. **Members**: All visible with cameras on
4. **Upload**: College cloud storage (designated uploader)
5. **Link**: Paste link in Activity submission form

---

## Contact Info Template

```
Video Link: [paste link here]

Member 1: [Last Name, First Name Middle Initial]
Member 2: [Last Name, First Name Middle Initial]
Member 3: [Last Name, First Name Middle Initial]
```

---

## Additional Resources

- React Router Docs: https://reactrouter.com/
- PHP Manual: https://www.php.net/manual/
- MySQL Docs: https://dev.mysql.com/doc/
- bcrypt Info: https://en.wikipedia.org/wiki/Bcrypt

---

**Last Updated:** April 2026
**Status:** Ready for submission ✓
