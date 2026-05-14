import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AlertCircle, BookOpenText, Eye, EyeOff, Loader2, ShieldCheck, Sparkles } from 'lucide-react'
import '../styles/Auth.css'

export default function Login() {
  const [activeTab, setActiveTab] = useState<'user' | 'admin'>('user')
  const [userEmail, setUserEmail] = useState('')
  const [userPassword, setUserPassword] = useState('')
  const [showUserPassword, setShowUserPassword] = useState(false)
  const [userError, setUserError] = useState('')
  const [userLoading, setUserLoading] = useState(false)
  const [adminEmail, setAdminEmail] = useState('')
  const [adminPassword, setAdminPassword] = useState('')
  const [showAdminPassword, setShowAdminPassword] = useState(false)
  const [adminError, setAdminError] = useState('')
  const [adminLoading, setAdminLoading] = useState(false)
  const [isRedirecting, setIsRedirecting] = useState(false)

  const navigate = useNavigate()

  const parseJsonResponse = async (response: Response) => {
    const rawText = await response.text()

    try {
      return JSON.parse(rawText)
    } catch {
      if (rawText.trim().startsWith('<!doctype') || rawText.trim().startsWith('<html')) {
        throw new Error('Backend returned HTML instead of JSON. Check if the PHP server is running and the Vite proxy is pointing to the correct backend URL.')
      }

      throw new Error(`Backend returned an invalid response: ${rawText.slice(0, 120)}`)
    }
  }

  const handleUserSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    setUserError('')
    setUserLoading(true)

    try {
      const response = await fetch('/backend/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: userEmail, password: userPassword }),
      })

      const data = await parseJsonResponse(response)

      if (data.success && data.token) {
        localStorage.setItem('authToken', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        setIsRedirecting(true)
        setTimeout(() => navigate('/home'), 1000)
      } else if (data.success) {
        setUserError('Login succeeded but no session token was returned.')
      } else {
        setUserError(data.message || 'Login failed')
      }
    } catch (error) {
      setUserError(error instanceof Error ? error.message : 'An error occurred. Please try again.')
      console.error('Error:', error)
    } finally {
      setUserLoading(false)
    }
  }

  const handleAdminSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    setAdminError('')
    setAdminLoading(true)

    try {
      const response = await fetch('/backend/admin-login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: adminEmail, password: adminPassword }),
      })

      const data = await parseJsonResponse(response)

      if (data.success && data.token) {
        localStorage.setItem('adminAuthToken', data.token)
        localStorage.setItem('admin', JSON.stringify(data.admin))
        setIsRedirecting(true)
        setTimeout(() => navigate('/admin/dashboard'), 1000)
      } else if (data.success) {
        setAdminError('Login succeeded but no session token was returned.')
      } else {
        setAdminError(data.message || 'Login failed')
      }
    } catch (error) {
      setAdminError(error instanceof Error ? error.message : 'An error occurred. Please try again.')
      console.error('Error:', error)
    } finally {
      setAdminLoading(false)
    }
  }

  const isUserView = activeTab === 'user'
  const activeError = isUserView ? userError : adminError
  const activeLoading = isUserView ? userLoading : adminLoading

  return (
    <div className="auth-shell">
      <div className="auth-layout auth-layout--wide">
        <section className="auth-panel auth-panel--brand">
          <div className="auth-kicker">
            <Sparkles size={16} />
            <span>{isUserView ? 'Customer access portal' : 'Protected admin access'}</span>
          </div>

          <h1>{isUserView ? 'Welcome back to your bookstore account.' : 'Manage inventory, users, and reports securely.'}</h1>
          <p>
            {isUserView
              ? 'Sign in to continue browsing books, placing orders, and reviewing your account activity.'
              : 'Use the admin portal to oversee catalog updates, order reporting, and bookstore operations.'}
          </p>

          <div className="auth-feature-list">
            <article className="auth-feature-card">
              <BookOpenText size={20} />
              <div>
                <strong>{isUserView ? 'Live catalog access' : 'Catalog control'}</strong>
                <span>{isUserView ? 'Browse available titles and continue shopping instantly.' : 'Create, edit, and manage bookstore inventory in one place.'}</span>
              </div>
            </article>

            <article className="auth-feature-card">
              <ShieldCheck size={20} />
              <div>
                <strong>{isUserView ? 'Secure account area' : 'Protected operations'}</strong>
                <span>{isUserView ? 'Manage profile details, passwords, and transaction history.' : 'Keep reporting and administrative actions separated from customer logins.'}</span>
              </div>
            </article>
          </div>

          <div className="auth-brand-footer">
            <span>General Online Bookstore</span>
            <p>Reader-first shopping experience with live API-backed flows.</p>
          </div>
        </section>

        <section className="auth-panel auth-panel--form">
          <div className="auth-form-card" style={{ position: 'relative' }}>
            {isRedirecting && (
              <div className="login-success-overlay">
                <Loader2 className="spinner" size={40} />
                <span className="success-text">Sign-in successful</span>
              </div>
            )}

            <div className="auth-form-header">
              <p className="auth-form-eyebrow">Account Access</p>
              <h2>{isUserView ? 'Sign in to continue' : 'Admin sign in'}</h2>
              <p>{isUserView ? 'Use your customer account to continue to the bookstore.' : 'Only authorized bookstore administrators should continue here.'}</p>
            </div>

            <div className="login-tabs">
              <button
                type="button"
                className={`tab-button ${isUserView ? 'active' : ''}`}
                onClick={() => setActiveTab('user')}
              >
                User Sign In
              </button>
              <button
                type="button"
                className={`tab-button ${!isUserView ? 'active' : ''}`}
                onClick={() => setActiveTab('admin')}
              >
                Admin Sign In
              </button>
            </div>

            {activeError && (
              <div className="error-banner">
                <p className="warning"><AlertCircle size={18} /> {activeError}</p>
              </div>
            )}

            {isUserView ? (
              <form onSubmit={handleUserSubmit} className="auth-form-grid">
                <div className="form-group">
                  <label htmlFor="user-email">Email Address</label>
                  <input
                    id="user-email"
                    type="email"
                    value={userEmail}
                    onChange={(event) => setUserEmail(event.target.value)}
                    placeholder="you@example.com"
                    disabled={userLoading}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="user-password">Password</label>
                  <div className="password-input-wrapper">
                    <input
                      id="user-password"
                      type={showUserPassword ? 'text' : 'password'}
                      value={userPassword}
                      onChange={(event) => setUserPassword(event.target.value)}
                      placeholder="Enter your password"
                      disabled={userLoading}
                      required
                    />
                    <button
                      type="button"
                      className="password-toggle-btn"
                      onClick={() => setShowUserPassword((current) => !current)}
                      title={showUserPassword ? 'Hide password' : 'Show password'}
                    >
                      {showUserPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                    </button>
                  </div>
                </div>

                <button type="submit" className="btn btn-primary" disabled={activeLoading}>
                  {activeLoading ? 'Signing in...' : 'Sign In'}
                </button>
              </form>
            ) : (
              <form onSubmit={handleAdminSubmit} className="auth-form-grid">
                <div className="form-group">
                  <label htmlFor="admin-email">Admin Email</label>
                  <input
                    id="admin-email"
                    type="email"
                    value={adminEmail}
                    onChange={(event) => setAdminEmail(event.target.value)}
                    placeholder="admin@example.com"
                    disabled={adminLoading}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="admin-password">Password</label>
                  <div className="password-input-wrapper">
                    <input
                      id="admin-password"
                      type={showAdminPassword ? 'text' : 'password'}
                      value={adminPassword}
                      onChange={(event) => setAdminPassword(event.target.value)}
                      placeholder="Enter your password"
                      disabled={adminLoading}
                      required
                    />
                    <button
                      type="button"
                      className="password-toggle-btn"
                      onClick={() => setShowAdminPassword((current) => !current)}
                      title={showAdminPassword ? 'Hide password' : 'Show password'}
                    >
                      {showAdminPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                    </button>
                  </div>
                </div>

                <button type="submit" className="btn btn-primary" disabled={activeLoading}>
                  {activeLoading ? 'Signing in...' : 'Enter Admin Portal'}
                </button>
              </form>
            )}

            <div className="auth-helper-row">
              {isUserView ? (
                <p className="link-text">
                  New to the bookstore? <Link to="/register">Create an account</Link>
                </p>
              ) : (
                <p className="link-text">
                  Looking for customer access? <button type="button" className="inline-switch" onClick={() => setActiveTab('user')}>Switch to user sign in</button>
                </p>
              )}
            </div>
          </div>
        </section>
      </div>
    </div>
  )
}
