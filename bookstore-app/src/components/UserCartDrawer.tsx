import { Loader2, Minus, Plus, ShoppingBag, Trash2 } from 'lucide-react'
import { formatCurrency } from '../utils/format'
import '../styles/UserCartDrawer.css'

interface CartDrawerItem {
  book_id: number
  title: string
  author: string
  price: number
  stock_quantity: number
  quantity: number
  book_cover_image: string | null
}

interface UserCartDrawerProps {
  isOpen: boolean
  onClose: () => void
  items: CartDrawerItem[]
  subtotal: number
  statusMessage?: string
  errorMessage?: string
  isSubmitting?: boolean
  onIncrement: (bookId: number) => void
  onDecrement: (bookId: number) => void
  onRemove: (bookId: number) => void
  onCheckout: () => void
  onViewOrders?: () => void
}

export default function UserCartDrawer({
  isOpen,
  onClose,
  items,
  subtotal,
  statusMessage = '',
  errorMessage = '',
  isSubmitting = false,
  onIncrement,
  onDecrement,
  onRemove,
  onCheckout,
  onViewOrders,
}: UserCartDrawerProps) {
  if (!isOpen) {
    return null
  }

  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0)

  return (
    <>
      <button
        type="button"
        className="cart-drawer-overlay"
        aria-label="Close cart panel"
        onClick={onClose}
      />
      <aside className="cart-drawer" aria-label="Shopping cart">
        <div className="cart-drawer__header">
          <div>
            <p className="cart-drawer__eyebrow">Shopping Cart</p>
            <h2>{totalItems} {totalItems === 1 ? 'item' : 'items'} selected</h2>
          </div>
          <button
            type="button"
            className="cart-drawer__close"
            onClick={onClose}
            aria-label="Close cart panel"
          >
            x
          </button>
        </div>

        <div className="cart-drawer__body">
          {errorMessage && <p className="cart-drawer__message cart-drawer__message--error">{errorMessage}</p>}
          {statusMessage && <p className="cart-drawer__message cart-drawer__message--success">{statusMessage}</p>}

          {items.length === 0 ? (
            <div className="cart-drawer__empty">
              <ShoppingBag size={34} />
              <p className="cart-drawer__empty-title">Your cart is empty.</p>
              <p className="cart-drawer__empty-copy">
                Add books from the catalog to prepare a checkout order.
              </p>
            </div>
          ) : (
            <div className="cart-drawer__items">
              {items.map((item) => (
                <article key={item.book_id} className="cart-drawer__item">
                  <div className="cart-drawer__item-main">
                    <div className="cart-drawer__item-art">
                      {item.book_cover_image ? (
                        <img
                          src={`/backend/uploads/books/${item.book_cover_image}`}
                          alt={item.title}
                          className="cart-drawer__item-image"
                        />
                      ) : (
                        <div className="cart-drawer__item-fallback">
                          <ShoppingBag size={20} />
                        </div>
                      )}
                    </div>

                    <div className="cart-drawer__item-copy">
                      <h3>{item.title}</h3>
                      <p>{item.author}</p>
                      <span>{formatCurrency(item.price)}</span>
                    </div>
                  </div>

                  <div className="cart-drawer__item-actions">
                    <div className="cart-drawer__quantity">
                      <button type="button" onClick={() => onDecrement(item.book_id)} disabled={item.quantity <= 1}>
                        <Minus size={16} />
                      </button>
                      <strong>{item.quantity}</strong>
                      <button
                        type="button"
                        onClick={() => onIncrement(item.book_id)}
                        disabled={item.quantity >= item.stock_quantity}
                      >
                        <Plus size={16} />
                      </button>
                    </div>

                    <button
                      type="button"
                      className="cart-drawer__remove"
                      onClick={() => onRemove(item.book_id)}
                      aria-label={`Remove ${item.title} from cart`}
                    >
                      <Trash2 size={16} />
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </div>

        <div className="cart-drawer__footer">
          <div className="cart-drawer__summary">
            <span>Subtotal</span>
            <strong>{formatCurrency(subtotal)}</strong>
          </div>
          <button
            type="button"
            className="cart-drawer__checkout"
            onClick={onCheckout}
            disabled={items.length === 0 || isSubmitting}
          >
            {isSubmitting ? <Loader2 className="spin" size={18} /> : <ShoppingBag size={18} />}
            <span>{isSubmitting ? 'Placing order...' : 'Place Order'}</span>
          </button>
          {statusMessage && onViewOrders && (
            <button type="button" className="cart-drawer__secondary" onClick={onViewOrders}>
              View Order History
            </button>
          )}
        </div>
      </aside>
    </>
  )
}
