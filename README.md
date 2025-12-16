# Mini CMS (PHP 8.3 + MySQL + OpenAI)

A minimal, production-ready CMS with public blog listing and admin CRUD, built with PHP 8.3+, PDO, Composer, Bootstrap 5, and OpenAI integration.

## Features
- Secure auth with password hashing, sessions, CSRF tokens.
- Admin dashboard for posts CRUD with image uploads.
- Public-facing blog listing with responsive cards.
- Individual post viewing page with full content display.
- AI content generation via OpenAI GPT-4o-mini (AJAX).
- PDO-based models with prepared statements.

## Requirements
- PHP 8.3+ with PDO MySQL extension
- MySQL 8+
- Composer

## Setup
1. Install dependencies:
   ```bash
   composer install
   ```
2. Copy env template and fill values:
   ```bash
   cp .env.example .env
   ```
   Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`, `OPENAI_API_KEY`.
3. Create database and tables:
   ```sql
   CREATE DATABASE mini_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

   USE mini_cms;

   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     name VARCHAR(100) NOT NULL,
     email VARCHAR(150) NOT NULL UNIQUE,
     password VARCHAR(255) NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE posts (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     content TEXT NOT NULL,
     image VARCHAR(255) NULL,
     user_id INT NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```
4. Ensure upload directory exists (auto-created on install, but double-check):
   ```bash
   mkdir -p public/uploads
   ```
5. Point your web server document root to `public/` (e.g., Apache/XAMPP). Start server and visit:
   - `/register.php` to create an account
   - `/login.php` then `/dashboard.php` for admin CRUD
   - `/` for public blog listing
   - `/view_post.php?id=X` to view individual posts

## Security Notes
- Passwords stored via `password_hash()` / `password_verify()`.
- Session fixation mitigated with `session_regenerate_id()` on login.
- CSRF tokens on forms and AJAX.
- File uploads validated for MIME type (using finfo or getimagesize fallback), extension, and size (<2MB).
- Output escaped with `htmlspecialchars`.

## OpenAI
- Uses `gpt-4o-mini` chat completions via Guzzle.
- Requires `OPENAI_API_KEY` in `.env`.
- AJAX endpoint: `/ai_generate.php`.

## Development Tips
- Adjust PHP upload limits if needed (`upload_max_filesize`, `post_max_size`).
- For HTTPS deployments, set secure session cookie parameters at the web server level.

### OpenAI SSL Certificate Issues

If you encounter `cURL error 60: SSL certificate problem` on Windows or development environments:

**Option 1 (Recommended for Production):**
Download the latest `cacert.pem` from https://curl.se/ca/, place it (e.g., `certs/cacert.pem`), and set in `.env`:
```
OPENAI_CA_BUNDLE=/full/path/to/cacert.pem
```

**Option 2 (Development Only):**
For local development on Windows, you can temporarily disable SSL verification by adding to `.env`:
```
OPENAI_DISABLE_SSL_VERIFY=true
```
⚠️ **Warning:** Only use this in development! This disables SSL certificate verification and is insecure for production.

### File Upload Validation

The application uses `finfo` (Fileinfo extension) for MIME type validation when available, with automatic fallback to `getimagesize()` for image validation. If you encounter "Class 'finfo' not found" errors, the application will automatically use the fallback method. For best results, enable the Fileinfo extension in `php.ini`:
```
extension=fileinfo
```

