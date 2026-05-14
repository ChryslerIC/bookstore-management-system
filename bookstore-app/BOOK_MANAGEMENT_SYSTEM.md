# Book Management System - Admin Feature

## Overview
Admin users can now create and manage books in the bookstore catalog. The system includes image upload with manual adjustment capabilities, form validation, and category selection.

## Features Implemented

### 1. **Image Upload Component** ([src/components/ImageUpload.tsx](src/components/ImageUpload.tsx))
- Drag-and-drop file upload
- Manual image adjustment with zoom and pan
- Width/height adjustment with aspect ratio enforcement
- Real-time preview
- Returns base64 encoded image for transmission

### 2. **Book Management Page** ([src/pages/ManageBooks.tsx](src/pages/ManageBooks.tsx))
- Tab-based navigation (Add Book / Books List)
- Form for creating books with the following fields:
  - **Book Cover Image** - With manual adjustment (3:4 aspect ratio)
  - **Title** - Book title (required)
  - **Description** - Book description/synopsis (required)
  - **ISBN** - International Standard Book Number (required, must be unique)
  - **Price** - Book price in Philippine Pesos (required)
  - **Stock Quantity** - Number of copies in stock (required)
  - **Category** - Select from existing categories (required)
- Form validation on all fields
- Real-time error and success messages
- Books list display with created books

### 3. **Backend Endpoints**

#### Get Categories ([backend/get-categories.php](backend/get-categories.php))
```
GET /backend/get-categories.php
Response: {success: boolean, categories: array}
```
Returns all available categories from the database for admin to select when creating books.

#### Create Book ([backend/create-book.php](backend/create-book.php))
```
POST /backend/create-book.php
Request Body: {
  title: string,
  description: string,
  isbn: string (unique),
  price: number,
  stock_quantity: number,
  category_id: number,
  book_cover_image: string (base64)
}
Response: {success: boolean, message: string, book: object}
```
- Validates all required fields
- Checks ISBN uniqueness
- Saves book cover image to `backend/uploads/books/`
- Inserts book into `books_tbl`
- Links book to category via `book_categories_tbl`

### 4. **Routes** ([src/App.tsx](src/App.tsx))
- `/admin/books` - Protected book management page (requires admin authentication)

### 5. **Styling**
- [src/styles/ImageUpload.css](src/styles/ImageUpload.css) - Image upload component styling
- [src/styles/ManageBooks.css](src/styles/ManageBooks.css) - Book management page styling

## User Flow

### Creating a Book
1. Admin navigates to Admin Dashboard
2. Clicks "Go to Books" button
3. On the Manage Books page, stays on "Add New Book" tab
4. Uploads book cover image via drag-and-drop or file selector
5. Adjusts image with zoom, pan, and manual width/height adjustment
6. Confirms image by clicking "Confirm & Use This Image"
7. Fills in book information:
   - Title
   - Description
   - ISBN
   - Price
   - Stock Quantity
   - Category (dropdown)
8. Clicks "Create Book" to submit
9. On success:
   - Success message displays
   - Form resets
   - Book appears in Books List tab
   - Image saved to server as JPEG

### Viewing Created Books
1. Click "Books List" tab
2. See table of created books with:
   - Title
   - ISBN
   - Price
   - Stock Quantity

## Database Tables Used

- **books_tbl** - Stores book information
  - book_id (auto-increment)
  - title
  - description
  - isbn (unique)
  - price
  - stock_quantity
  - book_cover_image (filename)

- **categories_tbl** - Stores available categories
  - category_id
  - category_name

- **book_categories_tbl** - Links books to categories
  - book_id (foreign key)
  - category_id (foreign key)

## Image Processing

1. **Upload**: Admin selects image via drag-drop or file input
2. **Preview**: Image displayed with manual adjustment controls
3. **Adjustment**: 
   - Zoom: 50% to 300%
   - Pan: Drag image to reposition
   - Resize: Adjust width/height (maintains 3:4 aspect ratio by default)
4. **Export**: Image cropped to canvas and converted to base64 JPEG
5. **Save**: Server saves base64 to file in `backend/uploads/books/`

## File Structure

```
backend/
  ├── get-categories.php         (New: Fetch categories)
  ├── create-book.php            (New: Create books)
  └── uploads/
      └── books/                 (Created on first book upload)
          └── book_*.jpg         (Book cover images)

src/
  ├── components/
  │   ├── ImageUpload.tsx        (New: Image upload component)
  │   └── AdminProtectedRoute.tsx
  ├── pages/
  │   ├── ManageBooks.tsx        (New: Book management page)
  │   └── AdminDashboard.tsx     (Updated: Added navigation)
  ├── styles/
  │   ├── ImageUpload.css        (New: Image upload styling)
  │   └── ManageBooks.css        (New: Book management styling)
  └── App.tsx                     (Updated: Added /admin/books route)
```

## Security Considerations

✅ **Route Protection**: Only authenticated admins can access `/admin/books`
✅ **ISBN Validation**: Prevents duplicate ISBN entries
✅ **File Type Validation**: Only image files accepted
✅ **File Storage**: Images saved with unique names to prevent conflicts
✅ **Form Validation**: All required fields validated on frontend and backend

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support (iOS 14+)

## Limitations & Future Enhancements

- Image is saved to server disk (consider using cloud storage for production)
- No image format validation on backend (accept any image type)
- No edit functionality yet (only create)
- No delete functionality yet
- Book list is local state only (resets on page refresh)

### Future Features
1. ✏️ Edit existing books
2. 🗑️ Delete books
3. 🔍 Search and filter books
4. 📊 View book sales/reviews
5. 🏞️ Upload images to cloud storage
6. 📱 Batch import books from CSV

## Testing the Feature

1. Ensure admin user exists in database with `role = 'admin'`
2. Login as admin at `/login` (Admin Login tab)
3. Navigate to Admin Dashboard
4. Click "Go to Books"
5. Upload a test image
6. Fill in book details:
   - Title: "Test Book"
   - ISBN: "978-1-234567-89-0"
   - Price: 499.99
   - Stock: 10
   - Description: "A test book description"
   - Category: Select any existing category
7. Click "Create Book"
8. Verify success message appears
9. Check Books List tab to see created book

## API Reference

### GET /backend/get-categories.php
**Response:**
```json
{
  "success": true,
  "categories": [
    {
      "category_id": 1,
      "category_name": "Fiction"
    },
    {
      "category_id": 2,
      "category_name": "Non-Fiction"
    }
  ]
}
```

### POST /backend/create-book.php
**Request:**
```json
{
  "title": "The Great Adventure",
  "description": "An epic tale of adventure and discovery",
  "isbn": "978-1-234567-89-0",
  "price": 599.99,
  "stock_quantity": 50,
  "category_id": 1,
  "book_cover_image": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Book created successfully",
  "book": {
    "book_id": 1,
    "title": "The Great Adventure",
    "isbn": "978-1-234567-89-0",
    "price": 599.99,
    "stock_quantity": 50,
    "category_id": 1,
    "book_cover_image": "book_1682346000_a1b2c3d4e5f6g7h8.jpg"
  }
}
```

## Troubleshooting

### Image upload fails
- Check browser console for errors
- Ensure image file is valid format (JPG, PNG, GIF, etc.)
- Check file size is not too large

### Category dropdown is empty
- Verify `categories_tbl` has records in database
- Check network request to `/backend/get-categories.php`
- Verify PHP error logs for database connection issues

### Book creation fails
- Verify ISBN is unique (not already in database)
- Check all required fields are filled
- Verify image was selected and adjusted
- Check browser console for error details

### Images not saving
- Verify `backend/uploads/` directory exists and is writable
- Check PHP file permissions (should be 755)
- Verify disk space is available on server
