/**
 * App.tsx - Main Application Component
 * 
 * PURPOSE: Sets up the main application routing using React Router
 * - Defines all application routes and their corresponding pages
 * - Wraps the /home route with ProtectedRoute for authentication
 * - Redirects unknown routes back to the landing page
 * 
 * TECHNIQUES USED:
 * - React Router v6 for client-side routing (BrowserRouter, Routes, Route, Navigate)
 * - Component composition pattern for page wrapping
 * - Higher-Order Component (HOC) pattern with ProtectedRoute wrapper
 */

import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Home from './pages/Home';
import UserProfile from './pages/UserProfile';
import UserTransactions from './pages/UserTransactions';
import AdminDashboard from './pages/AdminDashboard';
import ProtectedRoute from './components/ProtectedRoute';
import AdminProtectedRoute from './components/AdminProtectedRoute';

function App() {
  return (
    // Use BrowserRouter to enable client-side routing without page reloads
    <Router>
      {/* Use Routes container to manage all route definitions */}
      <Routes>
        {/* Route 1: Landing page - shown at http://localhost:5173/ */}
        {/* This is the home/welcome page visible to all users */}
        <Route path="/" element={<Landing />} />

        {/* Route 2: Registration page - http://localhost:5173/register */}
        {/* Allows new users to create an account */}
        <Route path="/register" element={<Register />} />

        {/* Route 3: Login page - http://localhost:5173/login */}
        {/* Allows registered users to authenticate */}
        <Route path="/login" element={<Login />} />

        {/* Route 4: Protected home page - http://localhost:5173/home */}
        {/* Use ProtectedRoute HOC component to wrap and protect the route */}
        {/* If user doesn't have authToken in localStorage, redirect to /login */}
        {/* If user has authToken, render the Home component */}
        <Route
          path="/home"
          element={
            <ProtectedRoute>
              <Home />
            </ProtectedRoute>
          }
        />

        <Route
          path="/profile"
          element={
            <ProtectedRoute>
              <UserProfile />
            </ProtectedRoute>
          }
        />

        <Route
          path="/transactions"
          element={
            <ProtectedRoute>
              <UserTransactions />
            </ProtectedRoute>
          }
        />

        {/* Route 5: Admin login page - http://localhost:5173/admin/login */}
        {/* Uses the same unified Login component as /login */}
        {/* Users can switch tabs to admin login from the same page */}
        <Route path="/admin/login" element={<Login />} />

        {/* Route 6: Protected admin dashboard - http://localhost:5173/admin/dashboard */}
        {/* Use AdminProtectedRoute HOC component to wrap and protect the route */}
        {/* If admin doesn't have adminAuthToken in localStorage, redirect to /login */}
        {/* If admin has adminAuthToken, render the AdminDashboard component */}
        <Route
          path="/admin/dashboard"
          element={
            <AdminProtectedRoute>
              <AdminDashboard />
            </AdminProtectedRoute>
          }
        />

        {/* Route 7: Protected book management page - http://localhost:5173/admin/books */}
        {/* Allows admins to create and manage books */}
        {/* Use AdminProtectedRoute to ensure only authenticated admins can access */}
        {/* Rendered within AdminDashboard layout */}
        <Route
          path="/admin/books"
          element={
            <AdminProtectedRoute>
              <AdminDashboard />
            </AdminProtectedRoute>
          }
        />

        {/* Route 8: Protected orders page - http://localhost:5173/admin/orders */}
        {/* Allows admins to view orders */}
        {/* Use AdminProtectedRoute to ensure only authenticated admins can access */}
        {/* Rendered within AdminDashboard layout */}
        <Route
          path="/admin/orders"
          element={
            <AdminProtectedRoute>
              <AdminDashboard />
            </AdminProtectedRoute>
          }
        />

        {/* Fallback route: If user navigates to any undefined route */}
        {/* Use Navigate component with 'replace' to redirect to landing page */}
        {/* The 'replace' prop replaces the URL in history instead of adding to it */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Router>
  );
}

export default App;
