import { useMemo, useState } from 'react';
import type { KeyboardEvent } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { House, LogOut, ReceiptText, Search, ShoppingBasket, UserRound, X } from 'lucide-react';
import '../styles/UserTopBar.css';
import sideLogo from '../assets/Web_Logo/side version.png';

interface UserTopBarProps {
  activeNav?: 'home' | 'profile' | 'transactions' | null;
  cartOpen?: boolean;
  cartCount?: number;
  transactionCount?: number;
  searchQuery?: string;
  onSearchChange?: (value: string) => void;
  onSearchSubmit?: () => void;
  onSearchClear?: () => void;
  onCartClick?: () => void;
}

export default function UserTopBar({
  activeNav = null,
  cartOpen = false,
  cartCount = 0,
  transactionCount = 0,
  searchQuery,
  onSearchChange,
  onSearchSubmit,
  onSearchClear,
  onCartClick,
}: UserTopBarProps) {
  const navigate = useNavigate();
  const [localSearchQuery, setLocalSearchQuery] = useState('');

  const isControlledSearch = typeof searchQuery === 'string' && typeof onSearchChange === 'function';
  const currentSearchQuery = isControlledSearch ? searchQuery : localSearchQuery;

  const navItems = useMemo(() => ([
    {
      key: 'home',
      label: 'Home',
      icon: <House size={22} strokeWidth={1.8} />,
      to: '/home',
      badgeCount: 0,
    },
    {
      key: 'profile',
      label: 'Profile',
      icon: <UserRound size={22} strokeWidth={1.8} />,
      to: '/profile',
      badgeCount: 0,
    },
    {
      key: 'transactions',
      label: 'Transactions',
      icon: <ReceiptText size={22} strokeWidth={1.8} />,
      to: '/transactions',
      badgeCount: transactionCount,
    },
    {
      key: 'cart',
      label: 'Cart',
      icon: <ShoppingBasket size={22} strokeWidth={1.8} />,
      badgeCount: cartCount,
    },
  ]), [cartCount, transactionCount]);

  const updateSearch = (value: string) => {
    if (isControlledSearch) {
      onSearchChange(value);
      return;
    }

    setLocalSearchQuery(value);
  };

  const handleSearchSubmit = () => {
    if (onSearchSubmit) {
      onSearchSubmit();
      return;
    }

    const params = new URLSearchParams();
    if (currentSearchQuery.trim()) {
      params.set('search', currentSearchQuery.trim());
    }
    navigate(`/home${params.toString() ? `?${params.toString()}` : ''}`);
  };

  const handleSearchClear = () => {
    if (onSearchClear) {
      onSearchClear();
      return;
    }

    setLocalSearchQuery('');
  };

  const handleSearchKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
    if (event.key === 'Enter') {
      handleSearchSubmit();
    }
  };

  const handleLogout = async () => {
    const token = localStorage.getItem('authToken');

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
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      navigate('/login');
    }
  };

  return (
    <nav className="user-topbar">
      <div className="user-topbar__content">
        <button
          type="button"
          className="user-topbar__logo"
          onClick={() => navigate('/home')}
          aria-label="Go to home"
        >
          <img src={sideLogo} alt="Bookstore Logo" className="user-topbar__logo-image" />
        </button>

        <div className="user-topbar__center">
          <div className="user-topbar__search">
            <input
              type="text"
              placeholder="Search products..."
              className="user-topbar__search-input"
              value={currentSearchQuery}
              onChange={(event) => updateSearch(event.target.value)}
              onKeyDown={handleSearchKeyDown}
            />
            <div className="user-topbar__search-actions">
              {currentSearchQuery && (
                <button
                  type="button"
                  className="user-topbar__search-clear"
                  onClick={handleSearchClear}
                  title="Clear search"
                >
                  <X size={18} />
                </button>
              )}
              <button
                type="button"
                className="user-topbar__search-submit"
                onClick={handleSearchSubmit}
                title="Search"
              >
                <Search size={20} />
              </button>
            </div>
          </div>
        </div>

        <div className="user-topbar__right">
          <div className="user-topbar__nav-icons" aria-label="User navigation">
            {navItems.map((item) => {
              const isActive = activeNav === item.key;

              const buttonContent = (
                <>
                  <span className="user-topbar__icon">{item.icon}</span>
                  {item.badgeCount > 0 && (
                    <span className="user-topbar__badge">{item.badgeCount}</span>
                  )}
                </>
              );

              if (item.key === 'cart') {
                return (
                  <button
                    key={item.key}
                    type="button"
                    className={`user-topbar__icon-button ${cartOpen ? 'active' : ''}`}
                    onClick={onCartClick}
                    title={item.label}
                    aria-label={item.label}
                  >
                    {buttonContent}
                  </button>
                );
              }

              return (
                <NavLink
                  key={item.key}
                  to={item.to!}
                  className={({ isActive: routeActive }) =>
                    `user-topbar__icon-button ${(isActive || routeActive) ? 'active' : ''}`
                  }
                  title={item.label}
                  aria-label={item.label}
                >
                  {buttonContent}
                </NavLink>
              );
            })}
          </div>

          <button type="button" onClick={handleLogout} className="user-topbar__logout">
            <LogOut size={18} />
            Logout
          </button>
        </div>
      </div>
    </nav>
  );
}
