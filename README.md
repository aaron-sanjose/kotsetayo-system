# KotseTayo — Car Buy & Sell Management System

A complete web application for a car dealership that lets customers browse, search, and inquire about vehicles, and gives the admin full control over inventory, inquiries, customers, and sales — all built with plain PHP and MySQL (PDO), with no frameworks or build tools required.

- PHP 7.4+ - MySQL 5.7+ - MIT License (shields badges intentionally omitted for plain-text portability)

---

## Features

### Public Website
- **Home** — hero section, featured vehicles, "Why Choose KotseTayo" and "How It Works" sections.
- **Browse & Search Cars** — filter by keyword, brand, year, transmission, fuel type, price range, and status; sort by newest, oldest, price (low→high / high→low).
- **Car Details** — full vehicle specs (mileage, transmission, fuel, color, engine), multiple images, and status badge.
- **Inquiry** — send a question about a specific vehicle (saved in the admin panel).
- **Purchase Request** — submit a purchase/buyer request for a vehicle.
- **About & Contact** — company info plus a contact form that stores messages for admins.

### Admin Panel (/admin)
- **Login** — session-based authentication with a secure "Remember me" (30 days) cookie token.
- **Dashboard** — live statistics (total vehicles, available/reserved/sold, sales, revenue, inquiries) plus recently placed inquiries and sales.
- **Vehicle Management** — add, edit, and delete cars with multiple image uploads (up to 5 MB each; JPG/JPEG/PNG/WEBP only; real file-type verification).
- **Inquiry Management** — track, filter, and update the status of customer inquiries.
- **Customers / Buyers** — manage buyer records.
- **Sales Management** — record sales with payment status (Pending / Paid / Partial) and notes.
- **Sales Reports** — summary reporting for the sales team.
- **Admin Profile** — change your own password.

## Tech Stack

| Layer     | Technology                                                  |
| --------- | ----------------------------------------------------------- |
| Language  | PHP (procedural, no framework)                              |
| Database  | MySQL 5.7+ / MariaDB 10.x via PDO (prepared statements)     |
| Frontend  | Semantic HTML5, custom CSS (style.css / admin.css), vanilla JS (script.js) |
| Graphics  | Inline SVG illustrations (no image CDN)                     |
| Server    | XAMPP / Apache (tested on XAMPP)                            |
| Encoding  | UTF-8 (utf8mb4)                                              |

## Requirements

- **XAMPP** (or any LAMP stack) with Apache + MySQL/MariaDB
- **PHP 7.4+** (uses random_bytes(), typed return values, nullable types)
- fileinfo extension enabled (used for upload MIME validation — on by default in XAMPP)

## Installation (XAMPP)

1. **Copy the project** into your web root (e.g. `C:\xampp\htdocs\kotsetayo\index.php`).

2. **Start the servers** — launch **Apache** and **MySQL** from the XAMPP Control Panel.

3. **Create the database**:
   - Open http://localhost/phpmyadmin
   - Click **Import**, choose database/kotsetayo.sql, and click **Go**.
   - This creates the kotsetayo database, all tables, and sample data (cars, images, customers, inquiries, sales, contact messages).

4. **Run the site**:
   - Public site: http://localhost/kotsetayo
   - Admin panel: http://localhost/kotsetayo/admin/login.php

> Note: If your MySQL credentials differ from the XAMPP defaults, edit config/database.php (DB_HOST, DB_USER, DB_PASS).

For an existing database, run `database/migrations/20261010_add_car_image_sort_order.sql` once in phpMyAdmin before using the image ordering controls.

### Default Admin Account

| Username | Password   |
| -------- | ---------- |
| admin    | admin123   |

> Important: Change this password after your first login (via the admin Profile page).

## Folder Structure

```
kotsetayo/
├── admin/                  # Admin panel pages
│   ├── login.php           #   Admin login (with remember me)
│   ├── dashboard.php       #   Statistics / overview
│   ├── cars.php            #   Vehicle list (admin)
│   ├── add-car.php         #   Add vehicle + upload images
│   ├── edit-car.php        #   Edit vehicle / images
│   ├── inquiries.php       #   Inquiry management
│   ├── customers.php       #   Buyer records
│   ├── sales.php           #   Sales management
│   ├── reports.php         #   Sales reports
│   ├── profile.php         #   Change admin password
│   └── logout.php          #   Logout
├── assets/                 # Static assets
│   ├── css/                #   style.css (public), admin.css (admin)
│   ├── js/                 #   script.js
│   └── images/             #   SVG illustrations (cars, hero, placeholder)
├── config/
│   └── database.php        # PDO connection, query helpers, base_url()
├── database/
│   └── kotsetayo.sql          # Database schema + sample data
├── includes/
│   ├── header.php          #   Public header/navbar
│   ├── footer.php          #   Public footer
│   ├── admin-header.php    #   Admin layout + auth guard
│   ├── admin-sidebar.php   #   Admin navigation
│   ├── functions.php       #   Helpers (formatting, CSRF, uploads, flash)
│   └── auth.php            #   Auth middleware & remember-me logic
├── uploads/
│   └── cars/               #   Uploaded car images
├── index.php               # Home
├── cars.php                # Browse / search / filter
├── car-details.php         # Single car details
├── inquiry.php             # Send inquiry about a car
├── purchase-request.php    # Purchase/buyer request
├── about.php               # About page
└── contact.php             # Contact form
```

> Note: User-uploaded images go to uploads/cars/ (web path stored in the car_images table). Asset/image paths are built with project_uri() so they work from any folder depth (root, admin, etc.).

## Database Schema

The kotsetayo database (InnoDB, utf8mb4_unicode_ci) contains 7 tables:

| Table              | Purpose                                                        |
| ------------------ | -------------------------------------------------------------- |
| admins             | Admin accounts (id, username, password hash, remember_token)  |
| cars               | Vehicles: brand, model, year, price, mileage, transmission, fuel, color, engine, description, status (Available / Reserved / Sold) |
| car_images         | One-to-many images per car (car_id -> cars.id, cascade delete)|
| inquiries          | Customer inquiries per car (Pending / Contacted / Completed / Cancelled) |
| customers          | Buyer records (unique on email + contact)                      |
| sales              | Sold vehicles, selling price, sale date, payment status, notes |
| contact_messages   | Visitor messages from the contact page                         |

Sample seed data is included in database/kotsetayo.sql.

## Security Notes

The codebase applies these security best practices out of the box:

- **PDO prepared statements** everywhere (SQL-injection safe).
- **Password hashing** with password_hash() / password_verify().
- **CSRF protection** — tokens are generated and verified on every state-changing form (includes/functions.php).
- **Output escaping** — every echoed value passes through e() (htmlspecialchars).
- **Upload safety** — extension allow-list, real MIME check via fileinfo, 5 MB size limit, and files are only deleted when they live under uploads/.
- **Session handling** — session_regenerate_id() on login, HTTP-only + SameSite=Lax remember-me cookie storing a hashed token.
- **Error handling** — raw database errors are logged (error_log()), never shown to end users.

## Development

Run a quick PHP syntax check across the whole project on Windows:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## License

Released under the MIT License — add your own LICENSE file with the appropriate copyright details before publishing.