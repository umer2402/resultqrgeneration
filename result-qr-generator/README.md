# Result QR Generator

A complete Core PHP, MySQL, Bootstrap 5, and JavaScript web application for managing student results and verifying them through QR codes.

## Features

- Admin login with secure password verification
- Dashboard with search, stats, recent results, edit, delete, and QR regeneration
- Student result form with automatic percentage calculation
- QR code generation and download
- Public result verification page using secure token URLs
- Responsive Bootstrap 5 interface

## Project Structure

```text
result-qr-generator/
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── add-result.php
├── edit-result.php
├── save-result.php
├── delete-result.php
├── generate-qr.php
├── result.php
├── config/
│   └── db.php
├── includes/
│   ├── footer.php
│   ├── header.php
│   ├── result-form.php
│   └── sidebar.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── qr/
├── database/
│   └── result_qr_generator.sql
└── README.md
```

## Setup on XAMPP

1. Copy the `result-qr-generator` folder into your `htdocs` directory.
2. Start Apache and MySQL from the XAMPP control panel.
3. Create a database named `result_qr_generator` in phpMyAdmin, or import the SQL file directly.
4. Import `database/result_qr_generator.sql`.
5. Open `config/db.php` and update:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
6. In `config/db.php`, update `APP_BASE_URL` if you want a fixed URL. Example:
   - `http://localhost/result-qr-generator`
7. Open the app in your browser:
   - `http://localhost/result-qr-generator`

## Default Admin Login

- Email: `admin@example.com`
- Password: `admin123`

## How QR URLs Work

Each result record gets a unique `qr_token` generated with `bin2hex(random_bytes(16))`. When a QR code is created, it stores a URL like:

```text
https://yourdomain.com/result.php?token=UNIQUE_QR_TOKEN
```

When someone scans the QR image, the browser opens `result.php`, which looks up the token in the `students_results` table and displays only that student's result record.

## cPanel Deployment

1. Upload the `result-qr-generator` folder to `public_html` or your chosen subfolder.
2. Create a MySQL database and user from cPanel.
3. Import `database/result_qr_generator.sql` using phpMyAdmin in cPanel.
4. Update `config/db.php` with the cPanel database host, database name, username, and password.
5. Set `APP_BASE_URL` in `config/db.php` to your live URL, for example:
   - `https://yourdomain.com/result-qr-generator`
6. Make sure the `assets/qr/` folder is writable by PHP.
7. Visit your website and log in with the default admin account.

## QR Service Note

The project uses the QuickChart QR API to generate PNG files and store them locally in `assets/qr/`. This keeps the project simple and works well on localhost and shared hosting. The server needs outbound internet access when generating or regenerating QR codes.

## CGPA / Decimal Support

The application supports decimal values for `total_marks` and `obtained_marks`, so you can store values like `4.00` and `3.25` for CGPA-based results.

If your database was imported before this update, run this SQL once on your live server:

```sql
ALTER TABLE students_results
MODIFY total_marks DECIMAL(8,2) DEFAULT NULL,
MODIFY obtained_marks DECIMAL(8,2) DEFAULT NULL,
MODIFY percentage DECIMAL(6,2) DEFAULT NULL;
```
