/**
 * AdminLogin.tsx - Admin Login Page Component
 * 
 * PURPOSE: Authenticates admin users
 * - Takes email and password input
 * - Sends credentials to backend for verification
 * - Verifies user has 'admin' role
 * - Stores admin authentication token and admin data in localStorage on success
 * - Redirects to /admin/dashboard page on successful login
 * - Shows error messages for invalid credentials
 */

import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AlertCircle } from 'lucide-react';
import '../styles/Auth.css';

export default function AdminLogin() {
  // Use useState hook (React state management) to manage form fields and UI states
  const [email, setEmail] = useState('');        // Email input field
  const [password, setPassword] = useState('');  // Password input field
  const [error, setError] = useState('');        // Error message to display
  const [loading, setLoading] = useState(false); // Flag to disable form during submission
  const navigate = useNavigate();                // Use React Router hook to navigate to /admin/dashboard

  /**
   * FORM SUBMISSION HANDLER
   * Sends admin login credentials to backend and handles authentication
   */
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();               // Prevent default form submission behavior  
    setError('');                     // Clear previous errors
    setLoading(true);                 // Disable form inputs during submission

    try {
      // Use Fetch API to send HTTP POST request to backend admin-login.php
      const response = await fetch('/backend/admin-login.php', {
        method: 'POST',               // HTTP POST method for sending credentials
        headers: {
          'Content-Type': 'application/json',
        },
        // Use JSON.stringify() to convert JavaScript object to JSON string
        body: JSON.stringify({ email, password }),
      });

      // Use .json() method to parse the JSON response from backend
      const data = await response.json();

      // Check if login was successful
      if (data.success && data.token) {
        // Use localStorage (browser storage API) to store admin authentication token
        // Persists across page refreshes and browser sessions
        localStorage.setItem('adminAuthToken', data.token);
        
        // Use JSON.stringify() to convert admin object to JSON string for storage
        // Store admin information (name, email, role) in localStorage
        localStorage.setItem('admin', JSON.stringify(data.admin));
        
        // Use navigate() to redirect admin to the protected /admin/dashboard page
        navigate('/admin/dashboard');
      } else if (data.success) {
        setError('Login succeeded but no session token was returned.');
      } else {
        // If login failed, display backend error message
        setError(data.message || 'Login failed');
      }
    } catch (error) {
      // If network error occurred, show generic error message
      setError('An error occurred. Please try again.');
      console.error('Error:', error);
    } finally {
      // Re-enable the form (stop loading)
      setLoading(false);
    }
  };

  return (
    <div className="auth-container">
      <div className="auth-form">
        <h2>Admin Login</h2>
        {error && (
          <div className="error-banner">
            <p className="warning"><AlertCircle size={18} /> {error}</p>
          </div>
        )}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">Admin Email</label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Enter admin email"
              disabled={loading}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="password">Password</label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Enter password"
              disabled={loading}
              required
            />
          </div>

          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? 'Logging in...' : 'Admin Login'}
          </button>
        </form>

        <p className="link-text">
          <a href="/login">Login as User</a> | <a href="/">Back to Home</a>
        </p>
      </div>
    </div>
  );
}
