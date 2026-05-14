import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Check, CheckCircle, Eye, EyeOff, ShieldCheck, Sparkles, UserPlus, X, XCircle } from 'lucide-react'
import '../styles/Auth.css'

const validatePassword = (password: string): { isValid: boolean; errors: string[] } => {
  const errors: string[] = []

  if (password.length < 8) {
    errors.push('Password must be at least 8 characters long')
  }
  if (!/[A-Z]/.test(password)) {
    errors.push('Password must contain at least 1 uppercase letter')
  }
  if (!/[0-9]/.test(password)) {
    errors.push('Password must contain at least 1 number')
  }
  if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?]/.test(password)) {
    errors.push('Password must contain at least 1 symbol (!@#$%^&* etc.)')
  }

  return { isValid: errors.length === 0, errors }
}

const validateName = (name: string): boolean => !/\d/.test(name)
const validateEmail = (email: string): boolean => email.includes('@')

export default function Register() {
  const [fname, setFname] = useState('')
  const [lname, setLname] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [confirmPassword, setConfirmPassword] = useState('')
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const [errors, setErrors] = useState<string[]>([])
  const [success, setSuccess] = useState(false)
  const [loading, setLoading] = useState(false)

  const navigate = useNavigate()

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    setErrors([])

    if (!fname.trim() || !lname.trim() || !email.trim() || !phone.trim() || !password || !confirmPassword) {
      setErrors(['All fields are required'])
      return
    }

    const validationErrors: string[] = []

    if (!validateName(fname)) {
      validationErrors.push('First name cannot contain numbers')
    }
    if (!validateName(lname)) {
      validationErrors.push('Last name cannot contain numbers')
    }
    if (!validateEmail(email)) {
      validationErrors.push('Email must contain @ symbol')
    }
    if (password !== confirmPassword) {
      validationErrors.push('Passwords do not match')
    }

    const passwordValidation = validatePassword(password)
    if (!passwordValidation.isValid) {
      validationErrors.push(...passwordValidation.errors)
    }

    if (phone.length !== 10) {
      validationErrors.push('Phone number must be exactly 10 digits')
    } else if (phone[0] !== '9') {
      validationErrors.push('Phone number must start with 9')
    }

    if (validationErrors.length > 0) {
      setErrors(validationErrors)
      return
    }

    setLoading(true)

    try {
      const fullPhone = `+63${phone}`
      const response = await fetch('/backend/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ fname, lname, email, phone: fullPhone, password }),
      })

      const data = await response.json()

      if (data.success) {
        setSuccess(true)
        setTimeout(() => navigate('/login'), 2000)
      } else {
        setErrors([data.message || 'Registration failed'])
      }
    } catch (error) {
      setErrors(['An error occurred. Please try again.'])
      console.error('Error:', error)
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="auth-shell">
        <div className="success-message">
          <h2><CheckCircle size={32} color="#27ae60" /> Registration Successful!</h2>
          <p>Redirecting to sign-in page...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="auth-shell">
      <div className="auth-layout auth-layout--wide">
        <section className="auth-panel auth-panel--brand">
          <div className="auth-kicker">
            <Sparkles size={16} />
            <span>Create your bookstore account</span>
          </div>

          <h1>Join a cleaner, more modern online bookstore experience.</h1>
          <p>
            Register once to browse the live catalog, manage your profile, place orders,
            and track purchases from your personal customer dashboard.
          </p>

          <div className="auth-feature-list">
            <article className="auth-feature-card">
              <UserPlus size={20} />
              <div>
                <strong>Quick account setup</strong>
                <span>Open your account in minutes with guided validation and clear requirements.</span>
              </div>
            </article>

            <article className="auth-feature-card">
              <ShieldCheck size={20} />
              <div>
                <strong>Protected customer access</strong>
                <span>Keep your account secure while managing orders, profile details, and password updates.</span>
              </div>
            </article>
          </div>

          <div className="auth-brand-footer">
            <span>Why register?</span>
            <p>Customer accounts unlock live checkout, order history, and account management in one place.</p>
          </div>
        </section>

        <section className="auth-panel auth-panel--form">
          <div className="auth-form-card">
            <div className="auth-form-header">
              <p className="auth-form-eyebrow">New Account</p>
              <h2>Create your account</h2>
              <p>Complete the form below to start shopping and managing orders.</p>
            </div>

            {errors.length > 0 && (
              <div className="error-messages">
                {errors.map((error, index) => (
                  <p key={index} className="error">
                    <XCircle size={16} /> {error}
                  </p>
                ))}
              </div>
            )}

            <form onSubmit={handleSubmit} className="auth-form-grid">
              <div className="auth-two-column">
                <div className="form-group">
                  <label htmlFor="fname">First Name</label>
                  <input
                    id="fname"
                    type="text"
                    value={fname}
                    onChange={(event) => setFname(event.target.value.replace(/\d/g, ''))}
                    placeholder="Maria"
                    disabled={loading}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="lname">Last Name</label>
                  <input
                    id="lname"
                    type="text"
                    value={lname}
                    onChange={(event) => setLname(event.target.value.replace(/\d/g, ''))}
                    placeholder="Santos"
                    disabled={loading}
                  />
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="email">Email Address</label>
                <input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  placeholder="you@example.com"
                  disabled={loading}
                />
              </div>

              <div className="form-group">
                <label htmlFor="phone">Mobile Number</label>
                <div className="phone-input-wrapper">
                  <span className="phone-prefix">+63</span>
                  <input
                    id="phone"
                    type="tel"
                    value={phone}
                    onChange={(event) => {
                      const filtered = event.target.value.replace(/\D/g, '')
                      if (filtered.length > 0 && filtered[0] !== '9') {
                        return
                      }
                      setPhone(filtered.slice(0, 10))
                    }}
                    placeholder="9XXXXXXXXX"
                    disabled={loading}
                  />
                </div>
              </div>

              <div className="auth-two-column auth-two-column--stack-mobile">
                <div className="form-group">
                  <label htmlFor="password">Password</label>
                  <div className="password-input-wrapper">
                    <input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={password}
                      onChange={(event) => setPassword(event.target.value)}
                      placeholder="Create password"
                      disabled={loading}
                    />
                    <button
                      type="button"
                      className="password-toggle-btn"
                      onClick={() => setShowPassword((current) => !current)}
                      title={showPassword ? 'Hide password' : 'Show password'}
                    >
                      {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                    </button>
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="confirmPassword">Confirm Password</label>
                  <div className="password-input-wrapper">
                    <input
                      id="confirmPassword"
                      type={showConfirmPassword ? 'text' : 'password'}
                      value={confirmPassword}
                      onChange={(event) => setConfirmPassword(event.target.value)}
                      placeholder="Repeat password"
                      disabled={loading}
                    />
                    <button
                      type="button"
                      className="password-toggle-btn"
                      onClick={() => setShowConfirmPassword((current) => !current)}
                      title={showConfirmPassword ? 'Hide password' : 'Show password'}
                    >
                      {showConfirmPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                    </button>
                  </div>
                </div>
              </div>

              {password && (
                <div className="password-requirements">
                  <p className={password.length >= 8 ? 'valid' : 'invalid'}>
                    {password.length >= 8 ? <Check size={14} /> : <X size={14} />} At least 8 characters
                  </p>
                  <p className={/[A-Z]/.test(password) ? 'valid' : 'invalid'}>
                    {/[A-Z]/.test(password) ? <Check size={14} /> : <X size={14} />} At least 1 uppercase letter
                  </p>
                  <p className={/[0-9]/.test(password) ? 'valid' : 'invalid'}>
                    {/[0-9]/.test(password) ? <Check size={14} /> : <X size={14} />} At least 1 number
                  </p>
                  <p className={/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?]/.test(password) ? 'valid' : 'invalid'}>
                    {/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>/?]/.test(password) ? <Check size={14} /> : <X size={14} />} At least 1 symbol
                  </p>
                </div>
              )}

              <button type="submit" className="btn btn-primary" disabled={loading}>
                {loading ? 'Creating account...' : 'Create Account'}
              </button>
            </form>

            <div className="auth-helper-row">
              <p className="link-text">
                Already registered? <Link to="/login">Sign in here</Link>
              </p>
            </div>
          </div>
        </section>
      </div>
    </div>
  )
}
