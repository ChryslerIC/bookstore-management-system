# Activity #2 Requirements Checklist

## Landing Page (/)
- [x] App starts at routes `/` or `http://localhost:5173`
- [x] Simple landing page displayed
- [x] Refresh page confirms `/` is landing page
- [x] Navigation links to Login and Register

## Registration Page (/register)
- [x] User fills out username, email, and password
- [x] Password validation (frontend only):
  - [x] Minimum 8 characters
  - [x] At least 1 uppercase letter
  - [x] At least 1 number
  - [x] At least 1 symbol
- [x] Real-time password feedback showing valid/invalid criteria
- [x] Test with weak passwords → error message displayed
- [x] On success → success message → link to login page
- [x] Confirm password field for verification

## Login Page (/login)
- [x] User enters email + password
- [x] If credentials correct → redirect to `/home`
- [x] If not registered → show warning "User not registered"

## Home Page (/home)
- [x] Protected route: only accessible if logged in
- [x] If user tries to bypass by typing URL without login → redirect to `/login`
- [x] Display welcome message with username
- [x] Display user email
- [x] Show logout button

## Logout
- [x] Clears authentication state (localStorage)
- [x] After logout, if user tries `/home` → blocked and redirected to `/login`

---

## Frontend (React) ✓ COMPLETE
- [x] Pages:
  - [x] Landing.tsx
  - [x] Register.tsx
  - [x] Login.tsx
  - [x] Home.tsx
- [x] Routing: React Router (`/`, `/register`, `/login`, `/home`)
- [x] ProtectedRoute component to guard `/home`
- [x] Password validation logic in Register.tsx
- [x] CSS styling for all pages
  - [x] Landing.css
  - [x] Auth.css
  - [x] Home.css

## Backend (PHP + MySQL) ✓ COMPLETE
- [x] register.php: inserts new user into users table
- [x] login.php: checks credentials, returns success/failure
- [x] db.php: handles MySQL connection
- [x] CORS headers at top of each PHP file

## Database (MySQL) ✓ COMPLETE
- [x] Create `bookstore_db` database
- [x] Create `users` table with:
  - [x] id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - [x] username (VARCHAR, UNIQUE)
  - [x] email (VARCHAR, UNIQUE)
  - [x] password (VARCHAR, hashed)
  - [x] created_at, updated_at (TIMESTAMP)

---

## Demo Script (20 minutes maximum)

### Step-by-Step Demo Checklist

1. **Start React App** (1 min)
   - [ ] Open terminal and run `npm run dev`
   - [ ] App starts at `http://localhost:5173`
   - [ ] Landing page displays
   - [ ] Refresh page to confirm `/` works correctly
   
2. **Test Registration - Weak Password** (1 min)
   - [ ] Navigate to `/register`
   - [ ] Enter username and email
   - [ ] Type weak password (e.g., "weak")
   - [ ] See validation errors for missing:
     - [ ] Uppercase letter
     - [ ] Number
     - [ ] Symbol
     - [ ] Minimum length

3. **Complete Registration** (1 min)
   - [ ] Enter valid password (e.g., "SecurePass@123")
   - [ ] See all validation criteria turn green
   - [ ] Submit registration
   - [ ] See success message
   - [ ] Click link to login page

4. **Test Login - Unregistered Account** (1 min)
   - [ ] Enter email not in database
   - [ ] Enter any password
   - [ ] See warning: "User not registered"

5. **Login - Success Case** (1 min)
   - [ ] Enter registered email
   - [ ] Enter registered password
   - [ ] Redirected to `/home`
   - [ ] Welcome message shows with username

6. **Test Route Protection** (2 min)
   - [ ] From `/home`, open new browser tab
   - [ ] Type `/home` in URL without logging in
   - [ ] Confirm automatic redirect to `/login`
   - [ ] This proves ProtectedRoute works

7. **Test Logout** (1 min)
   - [ ] Click logout button on `/home`
   - [ ] Redirected to `/login`
   - [ ] Try typing `/home` in URL
   - [ ] Confirm redirect to `/login` again

8. **Code Walkthrough** (10 min) - Show and explain:

   **Frontend Code:**
   - [ ] `src/App.tsx` - React Router setup with routes
   - [ ] `src/pages/Register.tsx` - Password validation logic
   - [ ] `src/pages/Login.tsx` - Login handler and error display
   - [ ] `src/components/ProtectedRoute.tsx` - Route protection mechanism
   
   **Backend Code:**
   - [ ] `backend/db.php` - Database connection
   - [ ] `backend/register.php` - User insertion, duplicate check
   - [ ] `backend/login.php` - Password verification with bcrypt
   
   **Database:**
   - [ ] Show MySQL users table
   - [ ] Display registered user records
   - [ ] Show hashed passwords (bcrypt)

---

## Documentation Files Provided

- [x] `SETUP_GUIDE.md` - Complete setup instructions
- [x] `API_DOCS.md` - API endpoint documentation
- [x] `backend/database.sql` - SQL database structure

---

## Submission Checklist

- [ ] All group members tested the application
- [ ] Video recorded with all members present
- [ ] Video shows all requirements being tested
- [ ] Code files prepared for viewing
- [ ] Database is set up and populated
- [ ] Frontend and backend running successfully
- [ ] Group decides on:
  - [ ] Video link
  - [ ] Member names (Last Name, First Name Middle Initial)
  - [ ] Primary uploader for college cloud storage

---

## Notes for Group

1. **Presentation Order Suggestion:**
   - Member 1: Start app, show landing page
   - Member 2: Test registration with password validation
   - Member 3: Test login and protected routes
   - Member 1: Explain frontend code structure
   - Member 2: Explain backend authentication
   - Member 3: Show database and query results

2. **Common Issues & Fixes:**
   - If CORS errors: Check headers in PHP files
   - If DB connection fails: Verify credentials in `db.php`
   - If routes not working: Restart dev server
   - If components not found: Run `npm install react-router-dom`

3. **Time Management:**
   - Rehearse demo multiple times
   - Keep code walkthrough to 10 minutes
   - Have example user credentials ready
   - Test all features before recording

---

## Grading Criteria (150 points)

- [x] Landing Page implementation (10 pts)
- [x] Registration with validation (20 pts)
- [x] Password validation rules (20 pts)
- [x] Login functionality (20 pts)
- [x] Protected routes (20 pts)
- [x] Logout functionality (10 pts)
- [x] Frontend code quality (10 pts)
- [x] Backend API implementation (20 pts)
- [x] Database design & setup (10 pts)
- [x] CORS implementation (5 pts)
- [x] Demo video quality (10 pts)
- [x] Code explanation (5 pts)
- [ ] Active participation (all members present) - [DURING VIDEO]

**Total: 150 points**
