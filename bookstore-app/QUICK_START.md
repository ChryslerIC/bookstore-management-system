# Quick Start

## 1. Import Database

Run the SQL in:

```text
backend/database.sql
```

## 2. Configure Local Settings

Copy:

```text
backend/config.local.example.php
```

to:

```text
backend/config.local.php
```

Then set your MySQL credentials and AES key.

Generate a key with:

```bash
openssl rand -hex 32
```

## 3. Start PHP

```bash
php -S 127.0.0.1:8001 router.php
```

## 4. Test API Root

```text
http://127.0.0.1:8001/api
```

## Default Admin Account

Imported by `backend/database.sql`:

- Email: `admin@example.com`
- Password: `Admin@123`

## Encrypted Sensitive Fields

- phone number
- payment method

Both use AES-256-GCM with:
- random IV
- stored authentication tag
- decryption before API output
