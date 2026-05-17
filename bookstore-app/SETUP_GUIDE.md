# Setup Guide

## Requirements

- PHP 8+
- MySQL or MariaDB
- OpenSSL enabled in PHP

## 1. Create the Database

Import:

```text
backend/database.sql
```

This creates:
- users and user names
- books and categories
- orders and order items
- token storage
- encrypted-field columns for AES-256-GCM

## 2. Configure Local Secrets

Copy:

```text
backend/config.local.example.php
```

to:

```text
backend/config.local.php
```

Then set:
- `LOCAL_DB_HOST`
- `LOCAL_DB_PORT`
- `LOCAL_DB_NAME`
- `LOCAL_DB_USER`
- `LOCAL_DB_PASSWORD`
- `LOCAL_ENCRYPTION_KEY_HEX`

Generate a key:

```bash
openssl rand -hex 32
```

## 3. Start the Backend

From the project root:

```bash
php -S 127.0.0.1:8001 router.php
```

## 4. Verify the API

Open:

```text
http://127.0.0.1:8001/api
```

or:

```text
http://127.0.0.1:8001/backend/health-check.php
```

## Security Notes

- This project requires MySQL/MariaDB only.
- SQLite fallback has been removed to match backend project constraints.
- Encryption keys must be supplied through environment variables or local config.
- Do not commit `backend/config.local.php`.
