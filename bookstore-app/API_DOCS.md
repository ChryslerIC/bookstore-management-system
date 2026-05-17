# Bookstore Management System API Documentation

## Base URL

```text
http://127.0.0.1:8001/api
```

## Response Format

All endpoints return JSON.

Example success:

```json
{
  "success": true,
  "message": "Operation completed"
}
```

Example error:

```json
{
  "success": false,
  "message": "Validation failed"
}
```

## Authentication

The API uses a custom bearer-token implementation.

Protected endpoints require:

```text
Authorization: Bearer <token>
```

## Required Endpoints

### Authentication

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`

### Profile and Users

- `GET /api/users/profile`
- `PUT /api/users/profile`
- `PUT /api/users/change-password`

### Books

- `POST /api/books`
- `GET /api/books`
- `GET /api/books/{book_id}`
- `PUT /api/books/{book_id}`
- `DELETE /api/books/{book_id}`

### Orders

- `POST /api/orders`
- `GET /api/orders/{order_id}`
- `GET /api/orders/user/{user_id}`

### Admin and Reporting

- `GET /api/admin/users`
- `GET /api/admin/books`
- `GET /api/reports/sales`
- `GET /api/reports/orders`

## Status Codes

- `200 OK` successful read or update
- `201 Created` successful resource creation
- `400 Bad Request` validation failure or malformed input
- `401 Unauthorized` missing or invalid authentication
- `403 Forbidden` authenticated but not allowed
- `404 Not Found` resource or route not found
- `405 Method Not Allowed` unsupported HTTP method
- `500 Internal Server Error` server or database failure

## AES-256-GCM Security Documentation

### Encrypted Fields

The project encrypts at least two sensitive fields:

1. `user_account_tbl.phone_encrypted`
   - related storage fields:
   - `phone_iv`
   - `phone_tag`

2. `orders_tbl.payment_encrypted`
   - related storage fields:
   - `payment_iv`
   - `payment_tag`

### Where Encryption Occurs

Encryption happens before database insert or update:

- Registration:
  - `POST /api/auth/register`
- Profile update:
  - `PUT /api/users/profile`
- Order creation:
  - `POST /api/orders`

Implementation:
- `backend/encryption.php`
- `api/index.php`

### Where Decryption Occurs

Decryption happens before returning sensitive data in responses:

- User profile retrieval:
  - `GET /api/users/profile`
- User login response:
  - `POST /api/auth/login`
- User formatting helper used by protected reads

Implementation:
- `api/bootstrap.php`
- `backend/encryption.php`

### Key Management

Encryption keys are not hardcoded in controllers or models.

The backend loads the AES-256-GCM key from:
- environment variable `ENCRYPTION_KEY_HEX`, or
- local untracked configuration file `backend/config.local.php`

Expected format:
- 64 hexadecimal characters
- converted to 32 bytes for AES-256

### Algorithm Requirements Used

- Algorithm: `AES-256-GCM`
- PHP functions:
  - `openssl_encrypt()`
  - `openssl_decrypt()`
- IV length: `12 bytes`
- Tag length: `16 bytes`

### Password Handling

Passwords are not encrypted.

They are hashed with:

```php
password_hash($password, PASSWORD_BCRYPT)
```
