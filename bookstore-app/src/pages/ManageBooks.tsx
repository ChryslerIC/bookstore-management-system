/**
 * ManageBooks.tsx - Book Management Page for Admins
 * 
 * PURPOSE: Allows admins to create and manage books
 * - Add new books with cover images, title, description, ISBN, price, stock
 * - Select category from existing categories
 * - Upload and adjust book cover images
 * - Display list of created books
 */

import { useState, useEffect } from 'react';
import { AlertCircle, CheckCircle, Book, Pencil, Trash2 } from 'lucide-react';
import ImageUpload from '../components/ImageUpload';
import '../styles/ManageBooks.css';

interface Category {
  category_id: number;
  category_name: string;
}

interface Book {
  book_id: number;
  title: string;
  author: string;
  description: string;
  isbn: string;
  price: number;
  stock_quantity: number;
  book_cover_image?: string;
  book_cover_original_image?: string | null;
  image_scale?: number;
  image_offset_x?: number;
  image_offset_y?: number;
  categories?: Category[];
}

export default function ManageBooks() {
  // Form state
  const [title, setTitle] = useState('');
  const [author, setAuthor] = useState('');
  const [description, setDescription] = useState('');
  const [isbn, setIsbn] = useState('');
  const [price, setPrice] = useState('');
  const [stockQuantity, setStockQuantity] = useState('');
  const [selectedCategories, setSelectedCategories] = useState<number[]>([]);
  const [bookCoverImage, setBookCoverImage] = useState<string | null>(null);
  const [originalBookCoverImage, setOriginalBookCoverImage] = useState<string | null>(null);
  const [imageScale, setImageScale] = useState(1);
  const [imageOffsetX, setImageOffsetX] = useState(0);
  const [imageOffsetY, setImageOffsetY] = useState(0);

  // UI state
  const [categories, setCategories] = useState<Category[]>([]);
  const [books, setBooks] = useState<Book[]>([]);
  const [loading, setLoading] = useState(false);
  const [loadingCategories, setLoadingCategories] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showForm, setShowForm] = useState(true);
  const [imageResetTrigger, setImageResetTrigger] = useState(0);

  // Delete confirmation modal state
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [deleteBookId, setDeleteBookId] = useState<number | null>(null);
  const [deleteBookTitle, setDeleteBookTitle] = useState('');

  // Edit mode state
  const [editingBook, setEditingBook] = useState<Book | null>(null);
  const [editTitle, setEditTitle] = useState('');
  const [editAuthor, setEditAuthor] = useState('');
  const [editDescription, setEditDescription] = useState('');
  const [editIsbn, setEditIsbn] = useState('');
  const [editPrice, setEditPrice] = useState('');
  const [editStockQuantity, setEditStockQuantity] = useState('');
  const [editSelectedCategories, setEditSelectedCategories] = useState<number[]>([]);
  const [editBookCoverImage, setEditBookCoverImage] = useState<string | null>(null);
  const [editOriginalBookCoverImage, setEditOriginalBookCoverImage] = useState<string | null>(null);
  const [editImageScale, setEditImageScale] = useState(1);
  const [editImageOffsetX, setEditImageOffsetX] = useState(0);
  const [editImageOffsetY, setEditImageOffsetY] = useState(0);
  const [editImageResetTrigger, setEditImageResetTrigger] = useState(0);

  /**
   * EFFECT: Fetch categories on component mount
   */
  useEffect(() => {
    fetchCategories();
  }, []);

  /**
   * EFFECT: Fetch books when showing books list
   */
  useEffect(() => {
    if (!showForm && !editingBook) {
      fetchBooks();
    }
  }, [showForm, editingBook]);

  /**
   * Fetch existing categories from backend
   */
  const fetchCategories = async () => {
    try {
      setLoadingCategories(true);
      setError('');

      const response = await fetch('/backend/get-categories.php');

      if (!response.ok) {
        throw new Error(`Server error: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();

      if (data.success) {
        setCategories(data.categories);
        if (data.categories.length === 0) {
          setError('No categories found. Please create categories first.');
        }
      } else {
        setError(`Failed to load categories: ${data.message}`);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : String(err);
      setError(`Error loading categories: ${errorMessage}`);
      console.error('Error:', err);
    } finally {
      setLoadingCategories(false);
    }
  };

  /**
   * Initialize categories in the database if none exist
   */
  const initializeCategories = async () => {
    try {
      setLoadingCategories(true);
      setError('');

      const response = await fetch('/backend/init-categories.php');

      if (!response.ok) {
        throw new Error(`Server error: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        setSuccess(`Initialized ${data.created.length} new categories`);
        setCategories(data.categories);
      } else {
        setError(`Initialization failed: ${data.message}`);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : String(err);
      setError(`Error initializing categories: ${errorMessage}`);
      console.error('Error:', err);
    } finally {
      setLoadingCategories(false);
    }
  };

  /**
   * Fetch existing books from backend
   */
  const fetchBooks = async () => {
    try {
      setLoading(true);
      setError('');

      const response = await fetch('/backend/list-books.php');

      if (!response.ok) {
        throw new Error(`Server error: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        setBooks(data.books);
      } else {
        setError(`Failed to load books: ${data.message}`);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : String(err);
      setError(`Error loading books: ${errorMessage}`);
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Validate form fields
   */
  const validateForm = (): boolean => {
    if (!title.trim()) {
      setError('Title is required');
      return false;
    }
    if (!author.trim()) {
      setError('Author is required');
      return false;
    }
    if (!description.trim()) {
      setError('Description is required');
      return false;
    }
    if (!isbn.trim()) {
      setError('ISBN is required');
      return false;
    }
    if (!price || parseFloat(price) <= 0) {
      setError('Valid price is required');
      return false;
    }
    if (!stockQuantity || parseInt(stockQuantity) < 0) {
      setError('Valid stock quantity is required');
      return false;
    }
    if (selectedCategories.length === 0) {
      setError('At least one category is required');
      return false;
    }
    if (!bookCoverImage) {
      setError('Book cover image is required');
      return false;
    }
    return true;
  };

  /**
   * Handle form submission
   */
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Validate form
    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      const response = await fetch('/backend/create-book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title,
          author,
          description,
          isbn,
          price: parseFloat(price),
          stock_quantity: parseInt(stockQuantity),
          category_ids: selectedCategories,
          book_cover_image: bookCoverImage,
          book_cover_original_image: originalBookCoverImage ?? bookCoverImage,
          image_scale: imageScale,
          image_offset_x: imageOffsetX,
          image_offset_y: imageOffsetY,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess('Book created successfully!');

        // Reset form
        setTitle('');
        setAuthor('');
        setDescription('');
        setIsbn('');
        setPrice('');
        setStockQuantity('');
        setSelectedCategories([]);
        setBookCoverImage(null);
        setOriginalBookCoverImage(null);
        setImageScale(1);
        setImageOffsetX(0);
        setImageOffsetY(0);
        // Reset image component by incrementing trigger
        setImageResetTrigger(prev => prev + 1);
        // Add book to list
        setBooks([...books, data.book]);

        // Scroll to success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        setError(data.message || 'Failed to create book');
      }
    } catch (err) {
      setError('Error creating book: ' + err);
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Handle image selection from ImageUpload component
   */
  const handleImageSelect = (base64Image: string) => {
    setBookCoverImage(base64Image);
  };

  /**
   * Keep a copy of the full uncropped image so future edits can reopen it.
   */
  const handleOriginalImageSelect = (base64Image: string) => {
    setOriginalBookCoverImage(base64Image);
  };

  /**
   * Handle image position change from ImageUpload
   */
  const handleImagePositionChange = (position: { scale: number; offsetX: number; offsetY: number }) => {
    setImageScale(position.scale);
    setImageOffsetX(position.offsetX);
    setImageOffsetY(position.offsetY);
  };

  /**
   * Handle category checkbox change
   */
  const handleCategoryChange = (categoryId: number, isChecked: boolean) => {
    if (isChecked) {
      setSelectedCategories([...selectedCategories, categoryId]);
    } else {
      setSelectedCategories(selectedCategories.filter(id => id !== categoryId));
    }
  };

  /**
   * Open delete confirmation modal
   */
  const openDeleteConfirm = (bookId: number, bookTitle: string) => {
    setDeleteBookId(bookId);
    setDeleteBookTitle(bookTitle);
    setShowDeleteConfirm(true);
  };

  /**
   * Cancel deletion and close modal
   */
  const cancelDelete = () => {
    setShowDeleteConfirm(false);
    setDeleteBookId(null);
    setDeleteBookTitle('');
  };

  /**
   * Confirm and execute book deletion
   */
  const confirmDelete = async () => {
    if (!deleteBookId) return;

    try {
      setLoading(true);
      setError('');
      setShowDeleteConfirm(false);

      const response = await fetch('/backend/delete-book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ book_id: deleteBookId }),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess(`Book "${deleteBookTitle}" deleted successfully!`);
        // Remove book from list
        setBooks(books.filter(book => book.book_id !== deleteBookId));
        // Scroll to success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        setError(data.message || 'Failed to delete book');
      }
    } catch (err) {
      setError('Error deleting book: ' + err);
      console.error('Error:', err);
    } finally {
      setLoading(false);
      setDeleteBookId(null);
      setDeleteBookTitle('');
    }
  };

  /**
   * Open edit mode with fresh book data from backend
   */
  const openEditMode = async (book: Book) => {
    try {
      // Fetch fresh book data including categories
      const response = await fetch(`/backend/get-book-by-id.php?book_id=${book.book_id}`);
      
      if (!response.ok) {
        throw new Error(`Server error: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.book) {
        const freshBook = data.book;
        
        setEditingBook(freshBook);
        setEditTitle(freshBook.title);
        setEditAuthor(freshBook.author);
        setEditDescription(freshBook.description);
        setEditIsbn(freshBook.isbn);
        setEditPrice(freshBook.price.toString());
        setEditStockQuantity(freshBook.stock_quantity.toString());
        setEditBookCoverImage(freshBook.book_cover_image ? `/backend/uploads/books/${freshBook.book_cover_image}` : null);
        setEditOriginalBookCoverImage(
          freshBook.book_cover_original_image
            ? `/backend/uploads/books/${freshBook.book_cover_original_image}`
            : (freshBook.book_cover_image ? `/backend/uploads/books/${freshBook.book_cover_image}` : null)
        );

        // Restore the saved crop positioning when reopening the editor
        setEditImageScale(freshBook.image_scale ?? 1);
        setEditImageOffsetX(freshBook.image_offset_x ?? 0);
        setEditImageOffsetY(freshBook.image_offset_y ?? 0);

        // Extract category IDs from the book data
        if (freshBook.categories && Array.isArray(freshBook.categories)) {
          const categoryIds = freshBook.categories.map((cat: any) => cat.category_id);
          setEditSelectedCategories(categoryIds);
        }
      } else {
        console.error('Failed to load book details');
        setError('Failed to load book details');
      }
    } catch (err) {
      console.error('Error loading book for edit:', err);
      // Fallback: use the book data from the list if endpoint fails
      setEditingBook(book);
      setEditTitle(book.title);
      setEditAuthor(book.author);
      setEditDescription(book.description);
      setEditIsbn(book.isbn);
      setEditPrice(book.price.toString());
      setEditStockQuantity(book.stock_quantity.toString());
      setEditBookCoverImage(book.book_cover_image ? `/backend/uploads/books/${book.book_cover_image}` : null);
      setEditOriginalBookCoverImage(
        book.book_cover_original_image
          ? `/backend/uploads/books/${book.book_cover_original_image}`
          : (book.book_cover_image ? `/backend/uploads/books/${book.book_cover_image}` : null)
      );

      setEditImageScale(book.image_scale ?? 1);
      setEditImageOffsetX(book.image_offset_x ?? 0);
      setEditImageOffsetY(book.image_offset_y ?? 0);

      fetchBookCategories(book.book_id);
    }
  };

  /**
   * Fetch categories for the book being edited
   */
  const fetchBookCategories = async (bookId: number) => {
    try {
      console.log("Fetching categories for book_id:", bookId);
      const response = await fetch(`/backend/get-book-categories.php?book_id=${bookId}`);

      console.log("Response status:", response.status, response.statusText);

      if (!response.ok) {
        console.error(`HTTP error! status: ${response.status}`);
        const errorText = await response.text();
        console.error("Error response body:", errorText);
        return;
      }

      const data = await response.json();
      console.log("Categories response data:", data);

      if (data.success && Array.isArray(data.categories)) {
        const categoryIds = data.categories.map((cat: any) => cat.category_id);
        setEditSelectedCategories(categoryIds);
        console.log("Loaded categories for book:", categoryIds);
      } else {
        console.warn("Unexpected response format or no categories found:", data);
      }
    } catch (err) {
      console.error("Error fetching book categories:", err);
      // Don't fail the modal opening, just log the error
    }
  };

  /**
   * Close edit mode
   */
  const closeEditMode = () => {
    setEditingBook(null);
    setEditTitle('');
    setEditAuthor('');
    setEditDescription('');
    setEditIsbn('');
    setEditPrice('');
    setEditStockQuantity('');
    setEditBookCoverImage(null);
    setEditOriginalBookCoverImage(null);
    setEditSelectedCategories([]);
    setEditImageScale(1);
    setEditImageOffsetX(0);
    setEditImageOffsetY(0);
    setEditImageResetTrigger(prev => prev + 1);
  };

  /**
   * Handle edit category checkbox change
   */
  const handleEditCategoryChange = (categoryId: number, isChecked: boolean) => {
    if (isChecked) {
      setEditSelectedCategories([...editSelectedCategories, categoryId]);
    } else {
      setEditSelectedCategories(editSelectedCategories.filter(id => id !== categoryId));
    }
  };

  /**
   * Handle edit image selection
   */
  const handleEditImageSelect = (base64Image: string) => {
    setEditBookCoverImage(base64Image);
  };

  const handleEditOriginalImageSelect = (base64Image: string) => {
    setEditOriginalBookCoverImage(base64Image);
  };

  /**
   * Handle edit image position change from ImageUpload
   */
  const handleEditImagePositionChange = (position: { scale: number; offsetX: number; offsetY: number }) => {
    setEditImageScale(position.scale);
    setEditImageOffsetX(position.offsetX);
    setEditImageOffsetY(position.offsetY);
  };

  /**
   * Save edited book
   */
  const handleSaveChanges = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Validate form
    if (!editTitle.trim() || !editAuthor.trim() || !editDescription.trim() ||
      !editIsbn.trim() || !editPrice || !editStockQuantity || editSelectedCategories.length === 0) {
      setError('All fields are required');
      return;
    }

    setLoading(true);

    try {
      const response = await fetch('/backend/update-book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          book_id: editingBook?.book_id,
          title: editTitle,
          author: editAuthor,
          description: editDescription,
          isbn: editIsbn,
          price: parseFloat(editPrice),
          stock_quantity: parseInt(editStockQuantity),
          category_ids: editSelectedCategories,
          book_cover_image: editBookCoverImage,
          book_cover_original_image: editOriginalBookCoverImage ?? editBookCoverImage,
          image_scale: editImageScale,
          image_offset_x: editImageOffsetX,
          image_offset_y: editImageOffsetY,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setSuccess('Book updated successfully!');

        // Update book in list
        setBooks(books.map(book =>
          book.book_id === editingBook?.book_id
            ? { ...book, ...data.book }
            : book
        ));

        closeEditMode();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        setError(data.message || 'Failed to update book');
      }
    } catch (err) {
      setError('Error updating book: ' + err);
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="manage-books-container">
      {/* Messages */}
      {error && (
        <div className="message-banner error-banner">
          <p><AlertCircle size={18} className="icon" /> {error}</p>
        </div>
      )}
      {success && (
        <div className="message-banner success-banner">
          <p><CheckCircle size={18} className="icon" /> {success}</p>
        </div>
      )}

      {/* Tab Navigation */}
      <div className="manage-tabs">
        <button
          className={`tab-button ${showForm ? 'active' : ''}`}
          onClick={() => setShowForm(true)}
        >
          Add New Book
        </button>
        <button
          className={`tab-button ${!showForm ? 'active' : ''}`}
          onClick={() => setShowForm(false)}
        >
          Books List
        </button>
      </div>

      {/* Add Book Form */}
      {showForm && (
        <div className="add-book-form">
          <form onSubmit={handleSubmit}>
            {/* Image Upload */}
            <div className="form-section">
              <h3>Book Cover Image</h3>
              <p className="section-description">Upload and adjust your book cover (Portrait: 4.13W × 6.38H)</p>
              <ImageUpload
                onImageSelect={handleImageSelect}
                onOriginalImageSelect={handleOriginalImageSelect}
                onPositionChange={handleImagePositionChange}
                maxWidth={260}
                maxHeight={400}
                resetTrigger={imageResetTrigger}
              />
              {bookCoverImage && <p className="image-selected"><CheckCircle size={16} className="icon" /> Image selected</p>}
            </div>

            {/* Book Information */}
            <div className="form-section">
              <h3>Book Information</h3>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="title">Title *</label>
                  <input
                    id="title"
                    type="text"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    placeholder="Enter book title"
                    disabled={loading}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="author">Author *</label>
                  <input
                    id="author"
                    type="text"
                    value={author}
                    onChange={(e) => setAuthor(e.target.value)}
                    placeholder="Enter author name"
                    disabled={loading}
                    required
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="isbn">ISBN *</label>
                  <input
                    id="isbn"
                    type="text"
                    value={isbn}
                    onChange={(e) => setIsbn(e.target.value)}
                    placeholder="e.g., 978-0-123456-78-9"
                    disabled={loading}
                    required
                  />
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="description">Description *</label>
                <textarea
                  id="description"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Enter book description"
                  rows={4}
                  disabled={loading}
                  required
                />
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="price">Price (₱) *</label>
                  <input
                    id="price"
                    type="number"
                    step="0.01"
                    value={price}
                    onChange={(e) => setPrice(e.target.value)}
                    placeholder="0.00"
                    disabled={loading}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="stockQuantity">Stock Quantity *</label>
                  <input
                    id="stockQuantity"
                    type="number"
                    value={stockQuantity}
                    onChange={(e) => setStockQuantity(e.target.value)}
                    placeholder="0"
                    disabled={loading}
                    required
                  />
                </div>
              </div>
            </div>

            {/* Category Selection */}
            <div className="form-section">
              <h3>Categories *</h3>
              <p className="section-description">Select one or more categories for this book</p>

              {loadingCategories ? (
                <div className="loading-placeholder">Loading categories...</div>
              ) : categories.length === 0 ? (
                <div className="error-placeholder">
                  <p>No categories available</p>
                  <div className="placeholder-buttons">
                    <button
                      type="button"
                      className="btn btn-secondary"
                      onClick={() => fetchCategories()}
                    >
                      Retry
                    </button>
                    <button
                      type="button"
                      className="btn btn-primary"
                      onClick={() => initializeCategories()}
                    >
                      Initialize Categories
                    </button>
                  </div>
                </div>
              ) : (
                <div className="categories-checkbox-grid">
                  {categories.map((cat) => (
                    <div key={cat.category_id} className="checkbox-item">
                      <input
                        id={`category-${cat.category_id}`}
                        type="checkbox"
                        checked={selectedCategories.includes(cat.category_id)}
                        onChange={(e) => handleCategoryChange(cat.category_id, e.target.checked)}
                        disabled={loading}
                      />
                      <label htmlFor={`category-${cat.category_id}`}>
                        {cat.category_name}
                      </label>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Submit Button */}
            <div className="form-actions">
              <button type="submit" className="btn btn-primary" disabled={loading}>
                {loading ? 'Creating Book...' : 'Create Book'}
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Books List / Edit Book View */}
      {!showForm && (
        <>
          {!editingBook ? (
            <div className="books-list-section">
              <div className="books-list-header">
                <h2>Books Catalog</h2>
                <p className="books-count">Total Books: {books.length}</p>
              </div>

              {loading && (
                <div className="loading-message">Loading books...</div>
              )}

              {!loading && books.length === 0 ? (
                <p className="empty-message">No books created yet. <a href="#" onClick={() => setShowForm(true)}>Add your first book</a></p>
              ) : !loading && (
                <div className="books-grid">
                  {books.map((book) => (
                    <div key={book.book_id} className="book-card">
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
                        <p className="book-price">₱{book.price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>

                        <div className="admin-stock-info">
                          Stock: {book.stock_quantity}
                        </div>
                      </div>

                      <div className="book-actions">
                        <button
                          className="btn btn-edit"
                          type="button"
                          onClick={() => openEditMode(book)}
                          disabled={loading}
                        >
                          <Pencil size={16} /> Edit
                        </button>
                        <button
                          className="btn btn-delete"
                          type="button"
                          onClick={() => openDeleteConfirm(book.book_id, book.title)}
                          disabled={loading}
                        >
                          <Trash2 size={16} /> Delete
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          ) : (
            <div className="edit-book-form">
              <div className="edit-form-header">
                <h2>Edit Book</h2>
              </div>

              <form onSubmit={handleSaveChanges}>
                {/* Image Upload */}
                <div className="form-section">
                  <h3>Book Cover Image</h3>
                  <p className="section-description">Update your book cover (Portrait: 4.13W × 6.38H)</p>
                  <ImageUpload
                    onImageSelect={handleEditImageSelect}
                    onOriginalImageSelect={handleEditOriginalImageSelect}
                    onPositionChange={handleEditImagePositionChange}
                    maxWidth={260}
                    maxHeight={400}
                    resetTrigger={editImageResetTrigger}
                    initialImage={editingBook?.book_cover_image ? `/backend/uploads/books/${editingBook.book_cover_image}` : undefined}
                    initialOriginalImage={editOriginalBookCoverImage}
                    initialScale={editImageScale}
                    initialOffsetX={editImageOffsetX}
                    initialOffsetY={editImageOffsetY}
                    initialIsAlreadyCropped={!!editingBook?.book_cover_image}
                  />
                  {editBookCoverImage && <p className="image-selected"><CheckCircle size={16} className="icon" /> New image selected</p>}
                </div>

                {/* Category Selection */}
                <div className="form-section">
                  <h3>Categories *</h3>
                  <p className="section-description">Select one or more categories for this book</p>

                  {loadingCategories ? (
                    <div className="loading-placeholder">Loading categories...</div>
                  ) : categories.length === 0 ? (
                    <div className="error-placeholder">
                      <p>No categories available</p>
                    </div>
                  ) : (
                    <div className="categories-checkbox-grid">
                      {categories.map((cat) => (
                        <div key={cat.category_id} className="checkbox-item">
                          <input
                            id={`edit-category-${cat.category_id}`}
                            type="checkbox"
                            checked={editSelectedCategories.includes(cat.category_id)}
                            onChange={(e) => handleEditCategoryChange(cat.category_id, e.target.checked)}
                            disabled={loading}
                          />
                          <label htmlFor={`edit-category-${cat.category_id}`}>
                            {cat.category_name}
                          </label>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                {/* Book Information */}
                <div className="form-section">
                  <h3>Book Information</h3>

                  <div className="form-row">
                    <div className="form-group">
                      <label htmlFor="edit-title">Title *</label>
                      <input
                        id="edit-title"
                        type="text"
                        value={editTitle}
                        onChange={(e) => setEditTitle(e.target.value)}
                        placeholder="Enter book title"
                        disabled={loading}
                        required
                      />
                    </div>

                    <div className="form-group">
                      <label htmlFor="edit-author">Author *</label>
                      <input
                        id="edit-author"
                        type="text"
                        value={editAuthor}
                        onChange={(e) => setEditAuthor(e.target.value)}
                        placeholder="Enter author name"
                        disabled={loading}
                        required
                      />
                    </div>
                  </div>

                  <div className="form-row">
                    <div className="form-group">
                      <label htmlFor="edit-isbn">ISBN *</label>
                      <input
                        id="edit-isbn"
                        type="text"
                        value={editIsbn}
                        onChange={(e) => setEditIsbn(e.target.value)}
                        placeholder="e.g., 978-0-123456-78-9"
                        disabled={loading}
                        required
                      />
                    </div>
                  </div>

                  <div className="form-group">
                    <label htmlFor="edit-description">Description *</label>
                    <textarea
                      id="edit-description"
                      value={editDescription}
                      onChange={(e) => setEditDescription(e.target.value)}
                      placeholder="Enter book description"
                      rows={4}
                      disabled={loading}
                      required
                    />
                  </div>

                  <div className="form-row">
                    <div className="form-group">
                      <label htmlFor="edit-price">Price (₱) *</label>
                      <input
                        id="edit-price"
                        type="number"
                        step="0.01"
                        value={editPrice}
                        onChange={(e) => setEditPrice(e.target.value)}
                        placeholder="0.00"
                        disabled={loading}
                        required
                      />
                    </div>

                    <div className="form-group">
                      <label htmlFor="edit-stockQuantity">Stock Quantity *</label>
                      <input
                        id="edit-stockQuantity"
                        type="number"
                        value={editStockQuantity}
                        onChange={(e) => setEditStockQuantity(e.target.value)}
                        placeholder="0"
                        disabled={loading}
                        required
                      />
                    </div>
                  </div>
                </div>

                {/* Form Actions */}
                <div className="form-actions">
                  <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={closeEditMode}
                    disabled={loading}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="btn btn-primary"
                    disabled={loading}
                  >
                    {loading ? 'Saving...' : 'Save Changes'}
                  </button>
                </div>
              </form>
            </div>
          )}
        </>
      )}

      {/* Delete Confirmation Modal */}
      {showDeleteConfirm && (
        <div className="modal-overlay">
          <div className="modal-content delete-confirmation-modal">
            <div className="modal-header">
              <h2><Trash2 size={24} className="icon" /> Delete Book</h2>
            </div>

            <div className="modal-body">
              <p>Are you sure you want to delete <strong>"{deleteBookTitle}"</strong>?</p>
              <p className="modal-warning">This action cannot be undone.</p>
            </div>

            <div className="modal-footer">
              <button
                className="btn btn-secondary"
                onClick={cancelDelete}
                disabled={loading}
              >
                Cancel
              </button>
              <button
                className="btn btn-delete"
                onClick={confirmDelete}
                disabled={loading}
              >
                {loading ? 'Deleting...' : 'Delete'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
