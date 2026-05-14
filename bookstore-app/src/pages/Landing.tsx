import { ArrowRight, BookOpenText, CreditCard, ShieldCheck, Sparkles, Truck } from 'lucide-react'
import { Link } from 'react-router-dom'
import webLogo from '../assets/Web_Logo/center version.png'
import '../styles/Landing.css'

const featuredHighlights = [
  {
    title: 'Curated Catalog',
    copy: 'Browse fiction, science, classics, and bestselling nonfiction in one clean storefront.',
    icon: <BookOpenText size={20} />,
  },
  {
    title: 'Fast Checkout',
    copy: 'Place orders quickly with a simple, reliable checkout flow and clear totals.',
    icon: <CreditCard size={20} />,
  },
  {
    title: 'Trusted Accounts',
    copy: 'Manage your profile, passwords, and purchase history from a protected customer area.',
    icon: <ShieldCheck size={20} />,
  },
]

const storefrontStats = [
  { value: '9+', label: 'Book categories' },
  { value: '10', label: 'Seeded bestsellers' },
  { value: '24/7', label: 'Account access' },
]

export default function Landing() {
  return (
    <div className="landing-shell">
      <header className="landing-hero">
        <div className="landing-hero__copy">
          <div className="landing-kicker">
            <Sparkles size={16} />
            <span>Modern online bookstore experience</span>
          </div>
          <h1>Discover your next favorite book in a storefront built for readers.</h1>
          <p>
            General Online Bookstore brings together a polished catalog, secure customer accounts,
            and simple order tracking in one cohesive shopping experience.
          </p>

          <div className="landing-actions">
            <Link to="/register" className="landing-button landing-button--primary">
              <span>Create an account</span>
              <ArrowRight size={18} />
            </Link>
            <Link to="/login" className="landing-button landing-button--secondary">
              Sign in
            </Link>
          </div>

          <div className="landing-stats">
            {storefrontStats.map((stat) => (
              <div key={stat.label} className="landing-stat">
                <strong>{stat.value}</strong>
                <span>{stat.label}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="landing-hero__visual">
          <div className="landing-brand-card">
            <img src={webLogo} alt="General Online Bookstore" className="landing-logo" />
            <div className="landing-brand-card__meta">
              <div>
                <p>Reader-first storefront</p>
                <h2>General Online Bookstore</h2>
              </div>
              <span>Live catalog</span>
            </div>
          </div>

          <div className="landing-floating-card landing-floating-card--one">
            <Truck size={18} />
            <div>
              <strong>Order tracking</strong>
              <span>See purchase history and status updates</span>
            </div>
          </div>

          <div className="landing-floating-card landing-floating-card--two">
            <BookOpenText size={18} />
            <div>
              <strong>Curated recommendations</strong>
              <span>Explore related titles from the same genres</span>
            </div>
          </div>
        </div>
      </header>

      <section className="landing-features">
        {featuredHighlights.map((feature) => (
          <article key={feature.title} className="landing-feature-card">
            <div className="landing-feature-card__icon">{feature.icon}</div>
            <h3>{feature.title}</h3>
            <p>{feature.copy}</p>
          </article>
        ))}
      </section>
    </div>
  )
}
