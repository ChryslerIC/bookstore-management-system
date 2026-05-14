/**
 * ProtectedRoute.tsx - Route Protection Component
 * 
 * PURPOSE: Prevents unauthorized access to protected pages (e.g., /home)
 * - Checks if user has authentication token in localStorage
 * - If authenticated: renders the protected page (children)
 * - If not authenticated: redirects to login page
 * 
 * HOW IT WORKS:
 * - Wraps protected route components in App.tsx
 * - Checks localStorage for 'authToken' that was set during login
 * - If token doesn't exist, user is redirected to /login
 * - This prevents users from accessing /home by typing the URL directly
 */

import { Navigate } from 'react-router-dom';

// TypeScript interface for component props
interface ProtectedRouteProps {
  children: React.ReactNode;  // The component to render if authenticated
}

export default function ProtectedRoute({ children }: ProtectedRouteProps) {
  // Use localStorage.getItem() method to retrieve authToken from browser storage
  // This token was set by Login.tsx after successful authentication
  // Returns the token string if it exists, or null if it doesn't exist
  const isAuthenticated = localStorage.getItem('authToken');

  // Use falsy check (!) to see if authentication token does not exist
  // If authToken is null or empty string, the user is not authenticated
  if (!isAuthenticated) {
    // Use Navigate component from React Router to redirect to /login page
    // The 'replace' prop uses .replaceState() instead of .pushState()
    // This replaces the URL in browser history instead of adding to it
    // Prevents users from bypassing authentication by clicking browser back button
    return <Navigate to="/login" replace />;
  }

  // If authentication token exists, user is authenticated
  // Use React fragment (<></>) to render the protected component (children)
  // React fragment is an invisible wrapper that returns multiple children without DOM node
  return <>{children}</>;
}
