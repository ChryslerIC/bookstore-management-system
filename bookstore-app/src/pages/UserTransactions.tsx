import { useEffect, useMemo, useState } from 'react'
import { CheckCircle2, Loader2, Plus, ReceiptText, ShoppingBag, Trash2 } from 'lucide-react'
import UserCartDrawer from '../components/UserCartDrawer'
import UserTopBar from '../components/UserTopBar'
import '../styles/Home.css'
import '../styles/UserPages.css'
import { apiRequest, getStoredUser } from '../utils/session'

interface Book {
  book_id: number
  title: string
  author: string
  price: number
  stock_quantity: number
}

interface OrderItem {
  order_item_id: number
  book_id: number
  title: string
  author: string
  quantity: number
  price_at_purchase: number
}

interface Order {
  order_id: number
  account_id: number
  total_amount: number
  status: string
  created_at: string
  updated_at: string
  items: OrderItem[]
}

export default function UserTransactions() {
  const [isCartOpen, setIsCartOpen] = useState(false)
  const [books, setBooks] = useState<Book[]>([])
  const [orders, setOrders] = useState<Order[]>([])
  const [loading, setLoading] = useState(true)
  const [submittingOrder, setSubmittingOrder] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [lineItems, setLineItems] = useState([{ book_id: 3, quantity: 1 }])

  const storedUser = getStoredUser()

  const loadTransactions = async () => {
    if (!storedUser?.account_id) {
      setError('No user session found. Please log in again.')
      setLoading(false)
      return
    }

    try {
      setLoading(true)
      setError('')

      const [booksData, ordersData] = await Promise.all([
        apiRequest<{ success: boolean; books: Book[] }>('/api/books'),
        apiRequest<{ success: boolean; orders: Order[] }>(`/api/orders/user/${storedUser.account_id}`),
      ])

      setBooks(booksData.books)
      setOrders(ordersData.orders)
      setLineItems((current) =>
        current.map((item) => ({
          ...item,
          book_id: booksData.books.some((book) => book.book_id === item.book_id)
            ? item.book_id
            : (booksData.books[0]?.book_id ?? 3),
        })),
      )
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : 'Failed to load transaction data')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void loadTransactions()
  }, [])

  const orderTotal = useMemo(() => (
    lineItems.reduce((sum, item) => {
      const matchedBook = books.find((book) => book.book_id === item.book_id)
      return sum + (matchedBook?.price ?? 0) * item.quantity
    }, 0)
  ), [books, lineItems])

  const addLineItem = () => {
    const fallbackBookId = books[0]?.book_id ?? 3
    setLineItems((current) => [...current, { book_id: fallbackBookId, quantity: 1 }])
  }

  const updateLineItem = (index: number, field: 'book_id' | 'quantity', value: number) => {
    setLineItems((current) =>
      current.map((item, itemIndex) =>
        itemIndex === index ? { ...item, [field]: value } : item,
      ),
    )
  }

  const removeLineItem = (index: number) => {
    setLineItems((current) => current.filter((_, itemIndex) => itemIndex !== index))
  }

  const handleCreateOrder = async (event: React.FormEvent) => {
    event.preventDefault()
    setError('')
    setSuccess('')

    if (lineItems.length === 0) {
      setError('Add at least one book to create an order.')
      return
    }

    try {
      setSubmittingOrder(true)
      const response = await apiRequest<{ success: boolean; message: string }>('/api/orders', {
        method: 'POST',
        body: JSON.stringify({
          items: lineItems.map((item) => ({
            book_id: item.book_id,
            quantity: item.quantity,
          })),
          payment_method: 'cash',
          status: 'paid',
        }),
      })

      setSuccess(response.message)
      setLineItems([{ book_id: books[0]?.book_id ?? 3, quantity: 1 }])
      await loadTransactions()
    } catch (submitError) {
      setError(submitError instanceof Error ? submitError.message : 'Failed to create order')
    } finally {
      setSubmittingOrder(false)
    }
  }

  const emptyCartItems: never[] = []

  return (
    <div className="home-container">
      <UserTopBar
        activeNav="transactions"
        cartOpen={isCartOpen}
        transactionCount={orders.length}
        onCartClick={() => setIsCartOpen((prev) => !prev)}
      />

      <main className="home-main">
        <section className="user-page-shell">
          <div className="user-page-header">
            <div>
              <p className="user-page-eyebrow">Transactions</p>
              <h1>Orders and Checkout</h1>
              <p>Review your purchase history and place a new order from the available catalog.</p>
            </div>
            <div className="user-page-chip">
              <ReceiptText size={18} />
              <span>{orders.length} total orders</span>
            </div>
          </div>

          {loading ? (
            <div className="user-page-placeholder">
              <Loader2 className="user-page-spinner" size={32} />
              <p>Loading your transaction data…</p>
            </div>
          ) : (
            <div className="transactions-layout">
              <div className="transactions-main">
                {(error || success) && (
                  <div className="user-page-messages">
                    {error && <p className="user-page-message error">{error}</p>}
                    {success && <p className="user-page-message success"><CheckCircle2 size={16} /> {success}</p>}
                  </div>
                )}

                <div className="transactions-summary-grid">
                  <article className="summary-stat-card">
                    <span>Total Orders</span>
                    <strong>{orders.length}</strong>
                  </article>
                  <article className="summary-stat-card">
                    <span>Books Available</span>
                    <strong>{books.length}</strong>
                  </article>
                  <article className="summary-stat-card">
                    <span>Latest Spend</span>
                    <strong>
                      ₱{(orders[0]?.total_amount ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </strong>
                  </article>
                </div>

                <section className="user-panel-card">
                  <div className="user-panel-heading">
                    <h2>Transaction History</h2>
                    <p>Review recent purchases, payment status, and itemized order totals.</p>
                  </div>

                  {orders.length === 0 ? (
                    <div className="user-page-empty-state">
                      <ShoppingBag size={30} />
                      <p>No orders yet. Create your first order from the checkout panel.</p>
                    </div>
                  ) : (
                    <div className="order-history-list">
                      {orders.map((order) => (
                        <article key={order.order_id} className="order-card">
                          <div className="order-card__header">
                            <div>
                              <h3>Order #{order.order_id}</h3>
                              <p>{new Date(order.created_at).toLocaleString()}</p>
                            </div>
                            <span className={`order-status order-status--${order.status}`}>{order.status}</span>
                          </div>

                          <div className="order-card__items">
                            {order.items.map((item) => (
                              <div key={item.order_item_id} className="order-line-item">
                                <div>
                                  <strong>{item.title}</strong>
                                  <p>{item.author}</p>
                                </div>
                                <div className="order-line-item__meta">
                                  <span>Qty {item.quantity}</span>
                                  <span>₱{item.price_at_purchase.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                              </div>
                            ))}
                          </div>

                          <div className="order-card__footer">
                            <span>Total</span>
                            <strong>₱{order.total_amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                          </div>
                        </article>
                      ))}
                    </div>
                  )}
                </section>
              </div>

              <aside className="demo-order-panel user-panel-card">
                <div className="user-panel-heading">
                  <h2>Create New Order</h2>
                  <p>Select books, set quantities, and place a paid order instantly.</p>
                </div>

                <form onSubmit={handleCreateOrder} className="demo-order-form">
                  <div className="demo-order-lines">
                    {lineItems.map((item, index) => {
                      const matchedBook = books.find((book) => book.book_id === item.book_id)

                      return (
                        <div key={`${item.book_id}-${index}`} className="demo-order-line">
                          <label>
                            <span>Book</span>
                            <select
                              value={item.book_id}
                              onChange={(event) => updateLineItem(index, 'book_id', Number(event.target.value))}
                            >
                              {books.map((book) => (
                                <option key={book.book_id} value={book.book_id}>
                                  {book.title} ({book.stock_quantity} in stock)
                                </option>
                              ))}
                            </select>
                          </label>

                          <label>
                            <span>Qty</span>
                            <input
                              type="number"
                              min={1}
                              max={matchedBook?.stock_quantity ?? 1}
                              value={item.quantity}
                              onChange={(event) => updateLineItem(index, 'quantity', Number(event.target.value))}
                            />
                          </label>

                          <button
                            type="button"
                            className="demo-order-remove"
                            onClick={() => removeLineItem(index)}
                            disabled={lineItems.length === 1}
                            title="Remove line"
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      )
                    })}
                  </div>

                  <button type="button" className="demo-order-add" onClick={addLineItem}>
                    <Plus size={16} />
                    <span>Add another item</span>
                  </button>

                  <div className="demo-order-total">
                    <span>Order total</span>
                    <strong>
                      ₱{orderTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </strong>
                  </div>

                  <button type="submit" className="user-page-primary-btn" disabled={submittingOrder || books.length === 0}>
                    {submittingOrder ? <Loader2 className="spin" size={18} /> : <ShoppingBag size={18} />}
                    <span>{submittingOrder ? 'Placing order…' : 'Place Paid Order'}</span>
                  </button>
                </form>
              </aside>
            </div>
          )}
        </section>
      </main>

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
