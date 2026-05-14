import { useEffect, useMemo, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { Book, BookX, ChevronRight, LayoutGrid, List } from 'lucide-react'
import BookDetailsModal from '../components/BookDetailsModal'
import UserCartDrawer from '../components/UserCartDrawer'
import UserTopBar from '../components/UserTopBar'
import { apiRequest } from '../utils/session'
import '../styles/Home.css'

interface BookCategory {
  category_id: number
  category_name: string
}

interface BookItem {
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

interface Category {
  category_id: number
  category_name: string
}

interface CartItem {
  book_id: number
  title: string
  author: string
  price: number
  stock_quantity: number
  quantity: number
  book_cover_image: string | null
}

export default function Home() {
  const location = useLocation()
  const navigate = useNavigate()

  const [books, setBooks] = useState<BookItem[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)
  const [selectedCategories, setSelectedCategories] = useState<number[]>([])
  const [priceMin, setPriceMin] = useState(0)
  const [priceMax, setPriceMax] = useState(10000)
  const [filterPriceMin, setFilterPriceMin] = useState(0)
  const [filterPriceMax, setFilterPriceMax] = useState(10000)
  const [sortBy, setSortBy] = useState('latest')
  const [showCount, setShowCount] = useState(8)
  const [currentPage, setCurrentPage] = useState(1)
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid')
  const [searchQuery, setSearchQuery] = useState('')
  const [appliedSearchQuery, setAppliedSearchQuery] = useState('')
  const [selectedBook, setSelectedBook] = useState<BookItem | null>(null)
  const [isCartOpen, setIsCartOpen] = useState(false)
  const [cartItems, setCartItems] = useState<CartItem[]>([])
  const [cartStatus, setCartStatus] = useState('')
  const [cartError, setCartError] = useState('')
  const [isCheckingOut, setIsCheckingOut] = useState(false)

  const loadCatalog = async () => {
    setLoading(true)

    try {
      const [booksData, categoriesData] = await Promise.all([
        apiRequest<{ success: boolean; books: BookItem[] }>('/api/books'),
        apiRequest<{ success: boolean; categories: Category[] }>('/backend/get-categories.php'),
      ])

      const nextBooks = booksData.books ?? []
      setBooks(nextBooks)
      setCategories(categoriesData.categories ?? [])

      if (nextBooks.length > 0) {
        const prices = nextBooks.map((book) => book.price)
        const minPrice = Math.floor(Math.min(...prices))
        const maxPrice = Math.ceil(Math.max(...prices))
        setPriceMin(minPrice)
        setPriceMax(maxPrice)
        setFilterPriceMin((current) => current || minPrice)
        setFilterPriceMax((current) => (current === 10000 ? maxPrice : current))
      }
    } catch (error) {
      console.error('Error fetching catalog:', error)
      setCartError(error instanceof Error ? error.message : 'Failed to load catalog')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void loadCatalog()
  }, [])

  useEffect(() => {
    const queryFromUrl = new URLSearchParams(location.search).get('search') ?? ''
    setSearchQuery(queryFromUrl)
    setAppliedSearchQuery(queryFromUrl)
  }, [location.search])

  const filteredBooks = useMemo(() => {
    const query = appliedSearchQuery.trim().toLowerCase()

    const result = books.filter((book) => {
      if (query) {
        const matchesTitle = book.title.toLowerCase().includes(query)
        const matchesAuthor = book.author.toLowerCase().includes(query)
        if (!matchesTitle && !matchesAuthor) {
          return false
        }
      }

      if (book.price < filterPriceMin || book.price > filterPriceMax) {
        return false
      }

      if (selectedCategories.length > 0) {
        const bookCategoryIds = book.categories.map((category) => category.category_id)
        if (!selectedCategories.some((categoryId) => bookCategoryIds.includes(categoryId))) {
          return false
        }
      }

      return true
    })

    switch (sortBy) {
      case 'latest':
        result.sort((left, right) => right.book_id - left.book_id)
        break
      case 'price-low':
        result.sort((left, right) => left.price - right.price)
        break
      case 'price-high':
        result.sort((left, right) => right.price - left.price)
        break
      case 'title':
        result.sort((left, right) => left.title.localeCompare(right.title))
        break
    }

    return result
  }, [appliedSearchQuery, books, filterPriceMax, filterPriceMin, selectedCategories, sortBy])

  const totalPages = Math.max(1, Math.ceil(filteredBooks.length / showCount))

  const paginatedBooks = useMemo(() => {
    const startIndex = (currentPage - 1) * showCount
    return filteredBooks.slice(startIndex, startIndex + showCount)
  }, [currentPage, filteredBooks, showCount])

  const cartCount = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.quantity, 0),
    [cartItems],
  )

  const cartSubtotal = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0),
    [cartItems],
  )

  useEffect(() => {
    setCurrentPage(1)
  }, [selectedCategories, filterPriceMin, filterPriceMax, sortBy, appliedSearchQuery, showCount])

  useEffect(() => {
    if (currentPage > totalPages) {
      setCurrentPage(totalPages)
    }
  }, [currentPage, totalPages])

  const getVisiblePageItems = () => {
    if (totalPages <= 8) {
      return Array.from({ length: totalPages }, (_, index) => index + 1)
    }

    if (currentPage <= 4) {
      return [1, 2, 3, 4, 'ellipsis', totalPages - 2, totalPages - 1, totalPages]
    }

    if (currentPage >= totalPages - 3) {
      return [1, 2, 3, 'ellipsis', totalPages - 3, totalPages - 2, totalPages - 1, totalPages]
    }

    return [1, 'ellipsis', currentPage - 1, currentPage, currentPage + 1, 'ellipsis', totalPages]
  }

  const handleBookSelect = async (book: BookItem) => {
    try {
      const response = await apiRequest<{ success: boolean; book: BookItem }>(`/api/books/${book.book_id}`)
      setSelectedBook(response.book ?? book)
    } catch (error) {
      console.error('Error loading book details:', error)
      setSelectedBook(book)
    }
  }

  const handleCategoryToggle = (categoryId: number) => {
    setSelectedCategories((current) =>
      current.includes(categoryId)
        ? current.filter((id) => id !== categoryId)
        : [...current, categoryId],
    )
  }

  const handleResetFilters = () => {
    setSelectedCategories([])
    setFilterPriceMin(priceMin)
    setFilterPriceMax(priceMax)
    setSearchQuery('')
    setAppliedSearchQuery('')
  }

  const handleSearchSubmit = () => {
    setAppliedSearchQuery(searchQuery.trim())
  }

  const handleAddToCart = (book: BookItem, quantity: number) => {
    setCartError('')
    setCartStatus(`${book.title} added to your cart.`)
    setIsCartOpen(true)

    setCartItems((current) => {
      const existing = current.find((item) => item.book_id === book.book_id)

      if (existing) {
        return current.map((item) =>
          item.book_id === book.book_id
            ? {
                ...item,
                quantity: Math.min(item.quantity + quantity, book.stock_quantity),
                stock_quantity: book.stock_quantity,
              }
            : item,
        )
      }

      return [
        ...current,
        {
          book_id: book.book_id,
          title: book.title,
          author: book.author,
          price: book.price,
          stock_quantity: book.stock_quantity,
          quantity,
          book_cover_image: book.book_cover_image,
        },
      ]
    })
  }

  const handleIncrementItem = (bookId: number) => {
    setCartStatus('')
    setCartError('')
    setCartItems((current) =>
      current.map((item) =>
        item.book_id === bookId
          ? { ...item, quantity: Math.min(item.quantity + 1, item.stock_quantity) }
          : item,
      ),
    )
  }

  const handleDecrementItem = (bookId: number) => {
    setCartStatus('')
    setCartError('')
    setCartItems((current) =>
      current.map((item) =>
        item.book_id === bookId
          ? { ...item, quantity: Math.max(item.quantity - 1, 1) }
          : item,
      ),
    )
  }

  const handleRemoveItem = (bookId: number) => {
    setCartStatus('')
    setCartError('')
    setCartItems((current) => current.filter((item) => item.book_id !== bookId))
  }

  const handleCheckout = async () => {
    if (cartItems.length === 0) {
      return
    }

    try {
      setIsCheckingOut(true)
      setCartError('')
      setCartStatus('')

      const response = await apiRequest<{ success: boolean; message: string }>('/api/orders', {
        method: 'POST',
        body: JSON.stringify({
          items: cartItems.map((item) => ({
            book_id: item.book_id,
            quantity: item.quantity,
          })),
          payment_method: 'cash',
          status: 'paid',
        }),
      })

      setCartItems([])
      setCartStatus(response.message)
      await loadCatalog()
    } catch (error) {
      setCartError(error instanceof Error ? error.message : 'Unable to place order')
    } finally {
      setIsCheckingOut(false)
    }
  }

  return (
    <div className="home-container">
      <UserTopBar
        activeNav="home"
        cartOpen={isCartOpen}
        cartCount={cartCount}
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSearchSubmit={handleSearchSubmit}
        onSearchClear={() => {
          setSearchQuery('')
          setAppliedSearchQuery('')
        }}
        onCartClick={() => setIsCartOpen((prev) => !prev)}
      />

      <main className="home-main">
        <div className="home-layout">
          <aside className="filter-sidebar">
            <div className="filter-card">
              <h3 className="filter-title">Categories</h3>
              <div className="category-list">
                {categories.map((category) => (
                  <label key={category.category_id} className="category-checkbox-label">
                    <input
                      type="checkbox"
                      checked={selectedCategories.includes(category.category_id)}
                      onChange={() => handleCategoryToggle(category.category_id)}
                    />
                    <span className="category-name">{category.category_name}</span>
                  </label>
                ))}
                {categories.length === 0 && !loading && (
                  <p className="no-items-text">No categories available</p>
                )}
              </div>
            </div>

            <div className="filter-card">
              <h3 className="filter-title">Filter By Price</h3>
              <div className="price-slider-container">
                <div className="dual-range-slider">
                  <div
                    className="slider-track-fill"
                    style={{
                      left: `${((filterPriceMin - priceMin) / (priceMax - priceMin || 1)) * 100}%`,
                      right: `${100 - ((filterPriceMax - priceMin) / (priceMax - priceMin || 1)) * 100}%`,
                    }}
                  />
                  <input
                    type="range"
                    min={priceMin}
                    max={priceMax}
                    value={filterPriceMin}
                    onChange={(event) => {
                      const value = Math.min(Number(event.target.value), filterPriceMax - 1)
                      setFilterPriceMin(value)
                    }}
                    className="range-input range-min"
                  />
                  <input
                    type="range"
                    min={priceMin}
                    max={priceMax}
                    value={filterPriceMax}
                    onChange={(event) => {
                      const value = Math.max(Number(event.target.value), filterPriceMin + 1)
                      setFilterPriceMax(value)
                    }}
                    className="range-input range-max"
                  />
                </div>
                <p className="price-display">
                  Price: <span className="price-val">PHP {filterPriceMin.toLocaleString()}</span> to{' '}
                  <span className="price-val">PHP {filterPriceMax.toLocaleString()}</span>
                </p>
              </div>
            </div>

            {(selectedCategories.length > 0 || filterPriceMin !== priceMin || filterPriceMax !== priceMax) && (
              <button type="button" className="reset-filters-btn" onClick={handleResetFilters}>
                Reset All Filters
              </button>
            )}
          </aside>

          <section className="books-catalog">
            <div className="catalog-toolbar">
              <div className="toolbar-left">
                <button
                  className={`view-toggle-btn ${viewMode === 'grid' ? 'active' : ''}`}
                  onClick={() => setViewMode('grid')}
                  title="Grid view"
                >
                  <LayoutGrid size={16} />
                </button>
                <button
                  className={`view-toggle-btn ${viewMode === 'list' ? 'active' : ''}`}
                  onClick={() => setViewMode('list')}
                  title="List view"
                >
                  <List size={16} />
                </button>
              </div>

              <div className="toolbar-right">
                <div className="toolbar-select">
                  <select value={sortBy} onChange={(event) => setSortBy(event.target.value)}>
                    <option value="latest">Sort by latest</option>
                    <option value="price-low">Sort by price: low to high</option>
                    <option value="price-high">Sort by price: high to low</option>
                    <option value="title">Sort by title</option>
                  </select>
                </div>
                <div className="toolbar-select">
                  <select value={showCount} onChange={(event) => setShowCount(Number(event.target.value))}>
                    <option value={8}>Show 8</option>
                    <option value={12}>Show 12</option>
                    <option value={20}>Show 20</option>
                    <option value={100}>Show All</option>
                  </select>
                </div>
              </div>
            </div>

            {loading ? (
              <div className="catalog-loading">
                <div className="loading-spinner" />
                <p>Loading books...</p>
              </div>
            ) : filteredBooks.length === 0 ? (
              <div className="catalog-empty">
                <p className="empty-msg">
                  <BookX size={48} className="empty-icon" />
                  No books found matching your filters.
                </p>
                {(selectedCategories.length > 0 || filterPriceMin !== priceMin || filterPriceMax !== priceMax) && (
                  <button type="button" className="reset-filters-btn" onClick={handleResetFilters}>
                    Reset Filters
                  </button>
                )}
              </div>
            ) : viewMode === 'grid' ? (
              <div className="books-grid">
                {paginatedBooks.map((book) => (
                  <div
                    key={book.book_id}
                    className="book-card"
                    onClick={() => handleBookSelect(book)}
                    style={{ cursor: 'pointer' }}
                  >
                    <div className="book-cover-wrap">
                      {book.book_cover_image ? (
                        <img
                          src={`/backend/uploads/books/${book.book_cover_image}`}
                          alt={book.title}
                          className="book-cover-img"
                        />
                      ) : (
                        <div className="book-cover-empty">
                          <Book size={48} />
                        </div>
                      )}
                    </div>
                    <div className="book-meta">
                      <h4 className="book-title">{book.title}</h4>
                      <p className="book-author">{book.author}</p>
                      <p className="book-price">
                        {book.price.toLocaleString('en-PH', {
                          style: 'currency',
                          currency: 'PHP',
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2,
                        })}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="books-list">
                {paginatedBooks.map((book) => (
                  <div
                    key={book.book_id}
                    className="book-list-item"
                    onClick={() => handleBookSelect(book)}
                    style={{ cursor: 'pointer' }}
                  >
                    <div className="book-list-cover-wrap">
                      {book.book_cover_image ? (
                        <img
                          src={`/backend/uploads/books/${book.book_cover_image}`}
                          alt={book.title}
                          className="book-list-cover-img"
                        />
                      ) : (
                        <div className="book-list-cover-empty">
                          <Book size={40} />
                        </div>
                      )}
                    </div>
                    <div className="book-list-info">
                      <h4 className="book-title">{book.title}</h4>
                      <p className="book-author">{book.author}</p>
                      <p className="book-description">{book.description}</p>
                      <p className="book-price">
                        {book.price.toLocaleString('en-PH', {
                          style: 'currency',
                          currency: 'PHP',
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2,
                        })}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {!loading && filteredBooks.length > 0 && totalPages > 1 && (
              <div className="catalog-pagination">
                <div className="pagination-pages">
                  {getVisiblePageItems().map((item, index) =>
                    item === 'ellipsis' ? (
                      <span key={`ellipsis-${index}`} className="pagination-ellipsis">
                        ...
                      </span>
                    ) : (
                      <button
                        key={item}
                        type="button"
                        className={`pagination-page ${currentPage === item ? 'active' : ''}`}
                        onClick={() => setCurrentPage(item as number)}
                      >
                        {item}
                      </button>
                    ),
                  )}
                </div>

                <button
                  type="button"
                  className="pagination-next"
                  onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                >
                  <span>Next</span>
                  <ChevronRight size={16} />
                </button>
              </div>
            )}
          </section>
        </div>
      </main>

      <BookDetailsModal
        book={selectedBook}
        isOpen={selectedBook !== null}
        onClose={() => setSelectedBook(null)}
        allBooks={books}
        onSelectBook={setSelectedBook}
        onAddToCart={handleAddToCart}
      />

      <UserCartDrawer
        isOpen={isCartOpen}
        onClose={() => setIsCartOpen(false)}
        items={cartItems}
        subtotal={cartSubtotal}
        statusMessage={cartStatus}
        errorMessage={cartError}
        isSubmitting={isCheckingOut}
        onIncrement={handleIncrementItem}
        onDecrement={handleDecrementItem}
        onRemove={handleRemoveItem}
        onCheckout={handleCheckout}
        onViewOrders={() => navigate('/transactions')}
      />
    </div>
  )
}
