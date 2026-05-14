import { useEffect, useState } from 'react'
import { CheckCircle2, Eye, EyeOff, Loader2, Save, ShieldCheck } from 'lucide-react'
import UserCartDrawer from '../components/UserCartDrawer'
import UserTopBar from '../components/UserTopBar'
import '../styles/Home.css'
import '../styles/UserPages.css'
import { apiRequest, getStoredUser, setStoredUser, type SessionUser } from '../utils/session'

interface ProfileResponse {
  success: boolean
  profile: SessionUser
}

function toLocalPhoneNumber(value: string): string {
  const digits = value.replace(/\D/g, '')

  if (digits.startsWith('63') && digits.length === 12) {
    return `0${digits.slice(2)}`
  }

  if (digits.startsWith('9') && digits.length === 10) {
    return `0${digits}`
  }

  return digits.slice(0, 11)
}

function isValidLocalPhoneNumber(value: string): boolean {
  return /^09\d{9}$/.test(value)
}

export default function UserProfile() {
  const [isCartOpen, setIsCartOpen] = useState(false)
  const [loading, setLoading] = useState(true)
  const [profileError, setProfileError] = useState('')
  const [profileSuccess, setProfileSuccess] = useState('')
  const [passwordSuccess, setPasswordSuccess] = useState('')
  const [showPasswordSuccessModal, setShowPasswordSuccessModal] = useState(false)
  const [savingProfile, setSavingProfile] = useState(false)
  const [savingPassword, setSavingPassword] = useState(false)
  const [showCurrentPassword, setShowCurrentPassword] = useState(false)
  const [showNewPassword, setShowNewPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)

  const storedUser = getStoredUser()

  const [profileForm, setProfileForm] = useState({
    fname: storedUser?.fname ?? '',
    lname: storedUser?.lname ?? '',
    email: storedUser?.email ?? '',
    phone: storedUser?.phone ?? '',
  })

  const [passwordForm, setPasswordForm] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
  })
  const emptyCartItems: never[] = []

  useEffect(() => {
    const loadProfile = async () => {
      try {
        setLoading(true)
        setProfileError('')
        const data = await apiRequest<ProfileResponse>('/api/users/profile')
        setProfileForm({
          fname: data.profile.fname,
          lname: data.profile.lname,
          email: data.profile.email,
          phone: toLocalPhoneNumber(data.profile.phone ?? ''),
        })
        setStoredUser(data.profile)
      } catch (error) {
        setProfileError(error instanceof Error ? error.message : 'Failed to load profile')
      } finally {
        setLoading(false)
      }
    }

    void loadProfile()
  }, [])

  useEffect(() => {
    if (!passwordSuccess) {
      return
    }

    setShowPasswordSuccessModal(true)
    const timeoutId = window.setTimeout(() => {
      setShowPasswordSuccessModal(false)
      setPasswordSuccess('')
    }, 2200)

    return () => window.clearTimeout(timeoutId)
  }, [passwordSuccess])

  const handleProfileSave = async (event: React.FormEvent) => {
    event.preventDefault()
    setProfileError('')
    setProfileSuccess('')

    if (!isValidLocalPhoneNumber(profileForm.phone)) {
      setProfileError('Phone number must be 11 digits and start with 09')
      return
    }

    try {
      setSavingProfile(true)
      const data = await apiRequest<ProfileResponse & { message: string }>('/api/users/profile', {
        method: 'PUT',
        body: JSON.stringify(profileForm),
      })
      setStoredUser(data.profile)
      setProfileSuccess(data.message)
    } catch (error) {
      setProfileError(error instanceof Error ? error.message : 'Failed to update profile')
    } finally {
      setSavingProfile(false)
    }
  }

  const handlePasswordSave = async (event: React.FormEvent) => {
    event.preventDefault()
    setProfileError('')
    setPasswordSuccess('')

    if (passwordForm.newPassword !== passwordForm.confirmPassword) {
      setProfileError('New password and confirmation do not match')
      return
    }

    if (passwordForm.newPassword.length < 8) {
      setProfileError('New password must be at least 8 characters long')
      return
    }

    try {
      setSavingPassword(true)
      const data = await apiRequest<{ success: boolean; message: string }>('/api/users/change-password', {
        method: 'PUT',
        body: JSON.stringify({
          current_password: passwordForm.currentPassword,
          new_password: passwordForm.newPassword,
        }),
      })
      setPasswordForm({
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
      })
      setPasswordSuccess(data.message)
    } catch (error) {
      setProfileError(error instanceof Error ? error.message : 'Failed to update password')
    } finally {
      setSavingPassword(false)
    }
  }

  return (
    <div className="home-container">
      <UserTopBar
        activeNav="profile"
        cartOpen={isCartOpen}
        onCartClick={() => setIsCartOpen((prev) => !prev)}
      />

      <main className="home-main">
        <section className="user-page-shell">
          <div className="user-page-header">
            <div>
              <p className="user-page-eyebrow">User Profile</p>
              <h1>Manage your bookstore account</h1>
              <p>Update your contact details and keep your login secure for the live project demonstration.</p>
            </div>
          </div>

          {loading ? (
            <div className="user-page-placeholder">
              <Loader2 className="user-page-spinner" size={32} />
              <p>Loading your profile…</p>
            </div>
          ) : (
            <div className="profile-layout">
              <aside className="profile-summary-card">
                <div className="profile-summary-avatar">
                  {(profileForm.fname[0] ?? 'U').toUpperCase()}
                </div>
                <h2>{profileForm.fname} {profileForm.lname}</h2>
                <p>{profileForm.email}</p>
                <dl className="profile-summary-list">
                  <div>
                    <dt>Phone</dt>
                    <dd>{profileForm.phone || 'Not set'}</dd>
                  </div>
                  <div>
                    <dt>Account ID</dt>
                    <dd>{storedUser?.account_id ?? 'Unknown'}</dd>
                  </div>
                </dl>
              </aside>

              <div className="profile-forms">
                {(profileError || profileSuccess) && (
                  <div className="user-page-messages">
                    {profileError && <p className="user-page-message error">{profileError}</p>}
                    {profileSuccess && <p className="user-page-message success"><CheckCircle2 size={16} /> {profileSuccess}</p>}
                  </div>
                )}

                <form className="user-panel-card" onSubmit={handleProfileSave}>
                  <div className="user-panel-heading">
                    <h2>Profile Details</h2>
                    <p>These values are saved through the live `/api/users/profile` endpoint.</p>
                  </div>

                  <div className="user-form-grid">
                    <label>
                      <span>First Name</span>
                      <input
                        value={profileForm.fname}
                        onChange={(event) => setProfileForm((current) => ({ ...current, fname: event.target.value }))}
                        required
                      />
                    </label>
                    <label>
                      <span>Last Name</span>
                      <input
                        value={profileForm.lname}
                        onChange={(event) => setProfileForm((current) => ({ ...current, lname: event.target.value }))}
                        required
                      />
                    </label>
                    <label>
                      <span>Email</span>
                      <input
                        type="email"
                        value={profileForm.email}
                        onChange={(event) => setProfileForm((current) => ({ ...current, email: event.target.value }))}
                        required
                      />
                    </label>
                    <label>
                      <span>Phone</span>
                      <input
                        value={profileForm.phone}
                        onChange={(event) =>
                          setProfileForm((current) => ({
                            ...current,
                            phone: event.target.value.replace(/\D/g, '').slice(0, 11),
                          }))
                        }
                        placeholder="09123456789"
                        inputMode="numeric"
                        pattern="09[0-9]{9}"
                        required
                      />
                    </label>
                  </div>

                  <button type="submit" className="user-page-primary-btn" disabled={savingProfile}>
                    {savingProfile ? <Loader2 className="spin" size={18} /> : <Save size={18} />}
                    <span>{savingProfile ? 'Saving…' : 'Save Profile'}</span>
                  </button>
                </form>

                <form className="user-panel-card" onSubmit={handlePasswordSave}>
                  <div className="user-panel-heading">
                    <h2>Change Password</h2>
                    <p>This uses the live `/api/users/change-password` endpoint for a realistic demo flow.</p>
                  </div>

                  <div className="user-form-grid user-form-grid--stacked">
                    <label>
                      <span>Current Password</span>
                      <div className="password-input-wrapper">
                        <input
                          type={showCurrentPassword ? 'text' : 'password'}
                          value={passwordForm.currentPassword}
                          onChange={(event) => setPasswordForm((current) => ({ ...current, currentPassword: event.target.value }))}
                          required
                        />
                        <button
                          type="button"
                          className="password-toggle-btn"
                          onClick={() => setShowCurrentPassword((current) => !current)}
                          title={showCurrentPassword ? 'Hide password' : 'Show password'}
                        >
                          {showCurrentPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                      </div>
                    </label>
                    <label>
                      <span>New Password</span>
                      <div className="password-input-wrapper">
                        <input
                          type={showNewPassword ? 'text' : 'password'}
                          value={passwordForm.newPassword}
                          onChange={(event) => setPasswordForm((current) => ({ ...current, newPassword: event.target.value }))}
                          required
                        />
                        <button
                          type="button"
                          className="password-toggle-btn"
                          onClick={() => setShowNewPassword((current) => !current)}
                          title={showNewPassword ? 'Hide password' : 'Show password'}
                        >
                          {showNewPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                      </div>
                    </label>
                    <label>
                      <span>Confirm New Password</span>
                      <div className="password-input-wrapper">
                        <input
                          type={showConfirmPassword ? 'text' : 'password'}
                          value={passwordForm.confirmPassword}
                          onChange={(event) => setPasswordForm((current) => ({ ...current, confirmPassword: event.target.value }))}
                          required
                        />
                        <button
                          type="button"
                          className="password-toggle-btn"
                          onClick={() => setShowConfirmPassword((current) => !current)}
                          title={showConfirmPassword ? 'Hide password' : 'Show password'}
                        >
                          {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                      </div>
                    </label>
                  </div>

                  <button type="submit" className="user-page-secondary-btn" disabled={savingPassword}>
                    {savingPassword ? <Loader2 className="spin" size={18} /> : <ShieldCheck size={18} />}
                    <span>{savingPassword ? 'Updating…' : 'Update Password'}</span>
                  </button>
                </form>
              </div>
            </div>
          )}
        </section>
      </main>

      {showPasswordSuccessModal && (
        <div className="user-success-popin" role="status" aria-live="polite">
          <div className="user-success-popin__card">
            <CheckCircle2 size={30} />
            <div>
              <strong>Password Updated</strong>
              <p>{passwordSuccess || 'Your password has been changed successfully.'}</p>
            </div>
          </div>
        </div>
      )}

      <UserCartDrawer
        isOpen={isCartOpen}
        onClose={() => setIsCartOpen(false)}
        items={emptyCartItems}
        subtotal={0}
        onIncrement={() => undefined}
        onDecrement={() => undefined}
        onRemove={() => undefined}
        onCheckout={() => undefined}
      />
    </div>
  )
}
