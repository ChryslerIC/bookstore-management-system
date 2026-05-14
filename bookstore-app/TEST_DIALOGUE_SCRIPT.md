# Bookstore App - Test Dialogue Script

## Test Case 1: Landing Page Route (/) Works on Refresh
**Action**: Start React app → refresh browser on landing page
**Expected Result**: Landing page displays correctly
**Why It Works**: 
- React Router's `BrowserRouter` handles client-side routing
- The route `<Route path="/" element={<Landing />} />` in App.tsx matches the root path
- Page displays without errors and full URL is readable

---

## Test Case 2: Weak Password Validation on Register Page
**Action**: Navigate to `/register` → enter weak password → click submit
**Expected Result**: Validation error message displayed
**Why It Works**: 
- The Register component validates password strength before submission
- Regex pattern checks for: minimum length, uppercase, lowercase, numbers, special characters
- Front-end validation prevents invalid data from reaching the backend
- Error message displays without sending HTTP request

---

## Test Case 3: Valid Password Registration Success
**Action**: Enter valid password → complete form → submit
**Expected Result**: Success message displays with link to login page
**Why It Works**: 
- Valid password passes all regex validation checks
- Form data is sent to backend (`/backend/register.php`)
- Backend validates again and stores user in database with encrypted password
- Success response triggers success message component
- React Router link allows navigation to `/login`

---

## Test Case 4: Login with Unregistered Account
**Action**: Navigate to `/login` → enter unregistered email → submit
**Expected Result**: Warning/error message appears
**Why It Works**: 
- Frontend sends credentials to backend (`/backend/login.php`)
- Backend queries database for matching email
- No matching user found → backend returns `{success: false}` response
- Error message displays from response data
- User remains on login page

---

## Test Case 5: Login with Registered Account → Redirect to Home
**Action**: Enter registered email and password → submit
**Expected Result**: Automatically redirected to `/home` page
**Why It Works**: 
- Backend finds matching user and verifies password (bcrypt hashing)
- Backend returns `{success: true, token: "...", user: {...}}`
- Frontend stores `authToken` and `user` in `localStorage`
- `localStorage` persists authentication across page refreshes
- `navigate('/home')` redirects to protected dashboard

---

## Test Case 6: Protected Route - Direct URL Access Blocked Before Login
**Action**: Type `/home` directly in URL bar BEFORE logging in
**Expected Result**: Redirected to `/login` page (cannot access `/home`)
**Why It Works**: 
- The `ProtectedRoute` component wraps `/home` in App.tsx
- It checks if `authToken` exists in `localStorage`
- No token found → component renders `<Navigate to="/login" replace />`
- Router redirects to login page automatically
- This prevents unauthorized access to the dashboard

---

## Test Case 7: Logout → Protected Route Blocked Again
**Action**: Click logout button → try accessing `/home` via URL
**Expected Result**: Redirected to `/login` page (access denied)
**Why It Works**: 
- `handleLogout()` removes `authToken` and `user` from `localStorage`
- `localStorage.removeItem()` completely deletes authentication data
- Browser now has no token for ProtectedRoute to verify
- ProtectedRoute checks `localStorage.getItem('authToken')` → returns `null`
- Route guard triggers redirect to `/login` again
- Full logout cycle complete - session ended

---

## Summary of Security Features
✅ **Front-end validation** - Catches weak passwords before submission
✅ **Token-based auth** - `localStorage` stores secure session tokens
✅ **Protected routes** - ProtectedRoute component blocks unauthorized access
✅ **Backend validation** - Database verifies credentials server-side
✅ **Session persistence** - `localStorage` keeps user logged in across refreshes
✅ **Logout cleanup** - Removing token immediately revokes access
