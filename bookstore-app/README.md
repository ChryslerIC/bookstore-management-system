# Bookstore Management System

Backend development project for an online bookstore management system using:
- Vanilla PHP 8+
- MySQL or MariaDB
- JSON REST-style API
- Custom token-based authentication
- AES-256-GCM encryption for sensitive fields

This repository also includes a demo frontend, but the assessed backend deliverable is the PHP API in:
- `api/`
- `backend/`

## Backend Scope

Implemented required endpoint groups:
- Authentication
- Profile and user management
- Books
- Orders
- Admin and reporting

Main API entry points:
- `api/index.php`
- `api/bootstrap.php`
- `router.php`

## Security Summary

Required security features included:
- Password hashing with `password_hash()`
- AES-256-GCM encryption using `openssl_encrypt()` and `openssl_decrypt()`
- Random 12-byte IV per encryption
- Authentication tags stored and verified on decrypt
- Validation and JSON error responses

Encrypted sensitive fields:
- User phone number
- Order payment method

## MySQL Requirement

This project now requires MySQL or MariaDB only.

Setup files:
- `backend/database.sql`
- `backend/config.local.example.php`
- `SETUP_GUIDE.md`
- `API_DOCS.md`

## Quick Start

1. Import `backend/database.sql` into MySQL/MariaDB.
2. Copy `backend/config.local.example.php` to `backend/config.local.php`.
3. Fill in your database credentials and 64-character encryption key.
4. Start PHP:

```bash
php -S 127.0.0.1:8001 router.php
```

5. API root:

```text
http://127.0.0.1:8001/api
```

## Encryption Key

Generate a compliant key with:

```bash
openssl rand -hex 32
```

Store it in:
- environment variable `ENCRYPTION_KEY_HEX`, or
- `backend/config.local.php`
