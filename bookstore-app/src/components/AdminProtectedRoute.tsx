/**
 * AdminProtectedRoute.tsx - Admin Route Protection Component
 * 
 * PURPOSE: Prevents unauthorized access to admin pages (e.g., /admin/dashboard)
 * - Checks if admin has authentication token in localStorage
 * - If authenticated as admin: renders the protected page (children)
 * - If not authenticated as admin: redirects to admin login page
 * 
 * HOW IT WORKS:
 * - Wraps protected admin route components in App.tsx
 * - Checks localStorage for 'adminAuthToken' that was set during admin login
 * - If token doesn't exist, admin is redirected to /admin/login
 * - This prevents unauthorized access to /admin/dashboard by typing the URL directly
 */

import { Navigate } from 'react-router-dom';

// TypeScript interface for component props
interface AdminProtectedRouteProps {
  children: React.ReactNode;  // The component to render if authenticated as admin
}

export default function AdminProtectedRoute({ children }: AdminProtectedRouteProps) {
  // Use localStorage.getItem() method to retrieve adminAuthToken from browser storage
  // This token was set by AdminLogin.tsx after successful authentication
  // Returns the token string if it exists, or null if it doesn't exist
  const isAdminAuthenticated = localStorage.getItem('adminAuthToken');

  // Use falsy check (!) to see if admin authentication token does not exist
  // If adminAuthToken is null or empty string, the user is not an authenticated admin
  if (!isAdminAuthenticated) {
    // Use Navigate component from React Router to redirect to /admin/login page
    // The 'replace' prop uses .replaceState() instead of .pushState()
    // This replaces the URL in browser history instead of adding to it
    // Prevents admins from bypassing authentication by clicking browser back button
    return <Navigate to="/admin/login" replace />;
  }

  // If admin authentication token exists, admin is authenticated
  // Use React fragment (<></>) to render the protected component (children)
  // React fragment is an invisible wrapper that returns multiple children without DOM node
  return <>{children}</>;
}
