# PHP Blog CMS

A lightweight CMS built with PHP 8, MySQL, and Apache for managing blog posts and categories.

## Features

- Admin authentication with password hashing, session hardening, inactivity timeout, and CSRF protection.
- Category and post management, including drafts, publishing, unique slugs, excerpts, and cover images.
- Public homepage with pagination, category pages, search, reading time, and related posts.
- Responsive layouts for the public site and admin area.
- Upload validation for JPEG, PNG, and WebP images, with a 2 MB limit.

## Screenshots

Add screenshots of the public homepage, a post page, and the admin dashboard to a `screenshots/` directory, then link them here. No screenshots are included yet.

## Setup (XAMPP on Windows)

1. Copy the project folder to `C:\xampp\htdocs\swd\ola-07-php-blog`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/`, choose **Import**, select `database.sql`, and run the import. The script creates the `swd_blog` database and its tables.
4. If connecting with non-default MySQL credentials, update `app/db.php` or set the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables.
5. From PowerShell or Command Prompt, create the first admin:

   ```powershell
   cd C:\xampp\htdocs\swd\ola-07-php-blog
   C:\xampp\php\php.exe create-admin.php "Your Name" you@example.com
   ```

   Enter and confirm the password when prompted.
6. Optionally add example content:

   ```powershell
   C:\xampp\php\php.exe seed.php
   ```

7. Visit the public site at `http://localhost/swd/ola-07-php-blog/` and sign in at `http://localhost/swd/ola-07-php-blog/admin/login.php`.

The Apache rewrite rules require `mod_rewrite` and `AllowOverride All` for this folder.

## Export the database from phpMyAdmin

To save the current database as `database.sql`, open phpMyAdmin, select **swd_blog**, choose **Export**, select **Quick** and **SQL**, then click **Export**. Save the downloaded file as `database.sql`. For a backup intended to recreate the schema and data elsewhere, include structure and data in the export.

## Folder structure

```text
.
├── admin/
│   ├── categories/
│   └── posts/
├── app/                 # Database, helpers, CSRF, and authentication
├── assets/              # Admin and public CSS
├── uploads/             # Cover images; script execution is blocked
├── category.php         # Public category page
├── create-admin.php     # CLI admin creator
├── database.sql         # Database schema import
├── index.php            # Public homepage
├── post.php             # Public post page
├── schema.sql           # Schema reference
├── search.php           # Public search
└── seed.php             # CLI example-content seeder
```
