import { useState, useEffect, useMemo } from 'react'
import { X, Minus, Plus, ShoppingCart, Book as BookIcon } from 'lucide-react'
import { formatCurrency } from '../utils/format'
import '../styles/BookDetailsModal.css'

interface BookCategory {
  category_id: number
  category_name: string
}

interface Book {
  book_id: number
  title: string
  author: string
  description: string
  isbn: string
  price: number
  stock_quantity: number
  book_cover_image: string | null
  categories: BookCategory[]
}

interface BookDetailsModalProps {
  book: Book | null
  isOpen: boolean
  onClose: () => void
  allBooks: Book[]
  onSelectBook: (book: Book) => void
  onAddToCart: (book: Book, quantity: number) => void
}

export default function BookDetailsModal({
  book,
  isOpen,
  onClose,
  allBooks,
  onSelectBook,
  onAddToCart,
}: BookDetailsModalProps) {
  const [quantity, setQuantity] = useState(1)

  const relatedBooks = useMemo(() => {
    if (!book || !allBooks.length) return []

    const currentCategoryIds = new Set(book.categories.map((category) => category.category_id))

    return allBooks
      .filter((candidate) => candidate.book_id !== book.book_id)
      .map((candidate) => ({
        book: candidate,
        sharedCount: candidate.categories.filter((category) => currentCategoryIds.has(category.category_id)).length,
      }))
      .filter((match) => match.sharedCount > 0)
      .sort((left, right) => right.sharedCount - left.sharedCount)
      .slice(0, 3)
      .map((match) => match.book)
  }, [book, allBooks])

  useEffect(() => {
    if (book) {
      setQuantity(book.stock_quantity > 0 ? 1 : 0)
      const content = document.querySelector('.bd-content')
      if (content) content.scrollTop = 0
    }
  }, [book, isOpen])

  useEffect(() => {
    const handleEsc = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose()
    }

    if (isOpen) {
      window.addEventListener('keydown', handleEsc)
    }

    return () => window.removeEventListener('keydown', handleEsc)
  }, [isOpen, onClose])

  if (!isOpen || !book) return null

  const inStock = book.stock_quantity > 0

  const handleIncrement = () => {
    if (quantity < book.stock_quantity) {
      setQuantity((prev) => prev + 1)
    }
  }

  const handleDecrement = () => {
    if (quantity > 1) {
      setQuantity((prev) => prev - 1)
    }
  }

  const handleAddToCart = () => {
    onAddToCart(book, quantity)
    onClose()
  }

  const handleModalClick = (event: React.MouseEvent) => {
    event.stopPropagation()
  }

  return (
    <div className="bd-overlay" onClick={onClose}>
      <div className="bd-content" onClick={handleModalClick}>
        <button className="bd-close-btn" onClick={onClose} title="Close">
          <X size={24} />
        </button>

        <div className="bd-body">
          <div className="bd-image-col">
            <div className="bd-main-image">
              {inStock ? (
                <span className="stock-badge in-stock bd-badge">In Stock</span>
              ) : (
                <span className="stock-badge out-of-stock bd-badge">Out of Stock</span>
              )}
              {book.book_cover_image ? (
                <img
                  src={`/backend/uploads/books/${book.book_cover_image}`}
                  alt={book.title}
                />
              ) : (
                <div className="empty-cover">
                  <BookIcon size={64} />
                </div>
              )}
            </div>
          </div>

          <div className="bd-details-col">
            <h2 className="bd-title">{book.title}</h2>

            <div className="bd-meta">
              <span className="meta-label">Author:</span> <span className="meta-value">{book.author}</span>
              <span className="meta-divider">|</span>
              <span className="meta-label">ISBN:</span> <span className="meta-value">{book.isbn || `B${book.book_id}`}</span>
            </div>

            {book.categories.length > 0 && (
              <div className="bd-categories">
                {book.categories.map((category) => (
                  <span key={category.category_id} className="bd-category-pill">
                    {category.category_name}
                  </span>
                ))}
              </div>
            )}

            <div className="bd-price">{formatCurrency(book.price)}</div>

            <div className="bd-description">
              <p>{book.description}</p>
            </div>

            <div className="bd-actions">
              <div className="bd-purchase-note">
                <strong>{book.stock_quantity}</strong> copies currently available for immediate purchase.
              </div>
              <div className="action-row">
                <div className="quantity-col">
                  <span className="quantity-label">Quantity</span>
                  <div className="quantity-selector">
                    <button
                      className="qty-btn"
                      onClick={handleDecrement}
                      disabled={!inStock || quantity <= 1}
                    >
                      <Minus size={16} />
                    </button>
                    <span className="qty-value">{quantity}</span>
                    <button
                      className="qty-btn"
                      onClick={handleIncrement}
                      disabled={!inStock || quantity >= book.stock_quantity}
                    >
                      <Plus size={16} />
                    </button>
                  </div>
                </div>

                <button
                  className="btn-add-to-cart"
                  onClick={handleAddToCart}
                  disabled={!inStock}
                >
                  <ShoppingCart size={18} />
                  Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>

        {relatedBooks.length > 0 && (
          <div className="bd-related">
            <div className="related-header">
              <h3>Related books</h3>
              <div className="header-line"></div>
            </div>

            <div className="related-grid">
              {relatedBooks.map((relatedBook, index) => (
                <div
                  key={relatedBook.book_id}
                  className={`related-item ${index < relatedBooks.length - 1 ? 'has-divider' : ''}`}
                  onClick={() => onSelectBook(relatedBook)}
                >
                  <div className="related-img">
                    {relatedBook.book_cover_image ? (
                      <img src={`/backend/uploads/books/${relatedBook.book_cover_image}`} alt={relatedBook.title} />
                    ) : (
                      <div className="related-empty-img"><BookIcon size={32} /></div>
                    )}
                  </div>
                  <h4 className="related-title" title={relatedBook.title}>{relatedBook.title}</h4>
                  <p className="related-author">{relatedBook.author}</p>
                  <p className="related-price">{formatCurrency(relatedBook.price)}</p>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
