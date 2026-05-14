/**
 * AdminDashboard.tsx - Admin Dashboard Layout with Sidebar Navigation
 * 
 * PURPOSE: Main layout for admin dashboard
 * - Sidebar navigation with Manage Books and View Orders
 * - Clean, consistent UI across all admin pages
 * - Handles admin logout functionality
 * - Redirects to Manage Books on default dashboard view
 */

import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { BookOpen, ClipboardList, LogOut } from 'lucide-react';
import ManageBooks from './ManageBooks';
import '../styles/AdminDashboard.css';
import sideLogo from '../assets/Web_Logo/side version.png';

interface Admin {
  account_id: string;
  fname: string;
  lname: string;
  email: string;
  role: string;
}

export default function AdminDashboard() {
  const [admin, setAdmin] = useState<Admin | null>(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const location = useLocation();

  /**
   * EFFECT HOOK: Load admin data and handle default navigation
   */
  useEffect(() => {
    try {
      const adminData = localStorage.getItem('admin');
      if (adminData) {
        const parsedAdmin = JSON.parse(adminData);
        setAdmin(parsedAdmin);
      }
    } catch (error) {
      console.error('Error loading admin data:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  /**
   * EFFECT HOOK: Redirect to Manage Books if on base dashboard
   */
  useEffect(() => {
    if (!loading && location.pathname === '/admin/dashboard') {
      navigate('/admin/books', { replace: true });
    }
  }, [loading, location.pathname, navigate]);

  /**
   * LOGOUT HANDLER
   */
  const handleLogout = async () => {
    const token = localStorage.getItem('adminAuthToken');

    try {
      const headers: HeadersInit = {
        'Content-Type': 'application/json',
      };

      if (token) {
        headers.Authorization = `Bearer ${token}`;
      }

      await fetch('/backend/logout.php', {
        method: 'POST',
        headers,
      });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('adminAuthToken');
      localStorage.removeItem('admin');
      navigate('/admin/login');
    }
  };

  const handleNavigation = (path: string) => {
    navigate(path);
  };

  /**
   * Determine which page to render based on current route
   */
  const renderContent = () => {
    switch (location.pathname) {
      case '/admin/books':
        return <ManageBooks />;
      case '/admin/orders':
        return <div className="orders-placeholder"><h2><ClipboardList size={24} /> View Orders</h2><p>Orders page coming soon...</p></div>;
      default:
        return <ManageBooks />;
    }
  };

  // Show loading state
  if (loading) {
    return <div className="admin-dashboard"><p>Loading...</p></div>;
  }

  // If no admin data found, show error
  if (!admin) {
    return <div className="admin-dashboard"><p>Error loading admin data</p></div>;
  }

  return (
    <div className="admin-dashboard">
      {/* Header */}
      <header className="admin-header">
        <div className="header-content">
          <div className="header-left">
            <h1 className="logo" style={{ height: '70px', overflow: 'hidden', display: 'flex', alignItems: 'center', margin: 0 }}>
              <img src={sideLogo} alt="Bookstore Logo" className="logo-image" style={{ height: '140px', objectFit: 'contain' }} />
            </h1>
          </div>
          <div className="header-right">
            <div className="admin-info">
              <span className="admin-name">{admin.fname} {admin.lname}</span>
              <span className="admin-role">{admin.role}</span>
            </div>
            <button onClick={handleLogout} className="btn-logout">
              <LogOut size={18} />
              Logout
            </button>
          </div>
        </div>
      </header>

      <div className="admin-container">
        {/* Sidebar Navigation */}
        <aside className="admin-sidebar">
          <nav className="sidebar-nav">
            <div className="nav-section">
              <ul className="nav-list">
                <li>
                  <button
                    className={`nav-item ${location.pathname === '/admin/books' ? 'active' : ''}`}
                    onClick={() => handleNavigation('/admin/books')}
                  >
                    <span className="nav-icon"><BookOpen size={18} /></span>
                    <span className="nav-label">Manage Books</span>
                  </button>
                </li>
                <li>
                  <button
                    className={`nav-item ${location.pathname === '/admin/orders' ? 'active' : ''}`}
                    onClick={() => handleNavigation('/admin/orders')}
                  >
                    <span className="nav-icon"><ClipboardList size={18} /></span>
                    <span className="nav-label">View Orders</span>
                  </button>
                </li>
              </ul>
            </div>
          </nav>
        </aside>

        {/* Main Content - Renders based on current route */}
        <main className="admin-content">
          {renderContent()}
        </main>
      </div>
    </div>
  );
}
