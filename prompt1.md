# Kotse Tayo — Car Buy & Sell Management System

Build a complete responsive web-based **Car Buy & Sell Management System** called **CarBuy**.

The system is intended for a small-to-medium car dealership/business that sells used and brand-new vehicles. Customers can browse available vehicles and send purchase inquiries, while administrators can manage vehicles, customers, inquiries, sales, and dashboard reports.

## 1. Technology Requirements

Use ONLY:

* HTML5
* CSS3
* Vanilla JavaScript
* PHP 8+
* MySQL
* XAMPP for local development

Do NOT use:

* React
* Vue
* Angular
* Laravel
* Node.js
* Bootstrap
* Tailwind CSS
* Other frontend frameworks

Use PHP with MySQL for the backend and database operations.

Use PDO or MySQLi with prepared statements for database queries.

The website must be responsive and work properly on:

* Desktop
* Tablet
* Mobile

---

# 2. User Roles

There are two types of users:

## Customer

Customers do not need to create an account.

They can:

* Browse vehicles
* Search vehicles
* Filter vehicles
* View vehicle details
* Submit an inquiry
* Submit a purchase request
* View contact information

## Administrator

Administrators must log in.

They can:

* View dashboard
* Manage vehicles
* Add vehicles
* Edit vehicles
* Delete vehicles
* Upload vehicle images
* Manage inquiries
* Manage customers/buyers
* Record sales
* Mark vehicles as reserved
* Mark vehicles as sold
* View sales reports
* Manage admin account

---

# 3. Public Website

Create the following pages:

## Home — `index.php`

Create a professional dealership landing page.

Sections:

### Navbar

Include:

* CarBuy logo
* Home
* Cars
* About
* Contact
* Admin Login

### Hero Section

Headline:

"Find Your Next Car"

Supporting text:

"Browse quality vehicles and find the right car for your budget and lifestyle."

Buttons:

* Browse Cars
* Sell Your Car / Contact Us

Include a large automotive image.

### Featured Cars

Display 6 available vehicles.

Each vehicle card should contain:

* Image
* Brand
* Model
* Year
* Mileage
* Transmission
* Price
* View Details button

### Why Choose CarBuy

Include:

* Quality Vehicles
* Affordable Prices
* Trusted Service
* Easy Buying Process

### How It Works

Show:

1. Browse Cars
2. Choose Your Vehicle
3. Send an Inquiry
4. Complete the Purchase

### Call To Action

"Ready to find your next car?"

Button:

"Browse Available Cars"

### Footer

Include:

* CarBuy logo
* Short description
* Navigation
* Contact information
* Social media placeholders
* Copyright

---

# 4. Cars Page — `cars.php`

Create a complete vehicle marketplace.

Include:

### Search

Search by:

* Brand
* Model
* Keyword

### Filters

Allow filtering by:

* Brand
* Minimum price
* Maximum price
* Year
* Transmission
* Fuel type
* Vehicle status

### Sorting

Allow:

* Price: Low to High
* Price: High to Low
* Newest
* Oldest

### Vehicle Grid

Each vehicle card displays:

* Vehicle image
* Brand
* Model
* Year
* Price
* Mileage
* Transmission
* Fuel type
* Status
* View Details button

Only vehicles with `Available` status should appear as available for purchase.

Use PHP/MySQL to retrieve the vehicle data dynamically.

---

# 5. Vehicle Details — `car-details.php`

Create a detailed vehicle page.

Display:

* Multiple vehicle images
* Brand
* Model
* Year
* Price
* Mileage
* Transmission
* Fuel type
* Color
* Engine
* Description
* Vehicle status

Include a prominent:

"Send Inquiry"

button.

Also include:

"Request to Buy"

button.

Add a section:

"You May Also Like"

with related vehicles.

---

# 6. Inquiry Form

Create an inquiry form.

Fields:

* Full Name
* Email
* Contact Number
* Interested Vehicle
* Message

The vehicle should automatically be associated with the selected car.

After submission:

1. Validate the form.
2. Save the inquiry to MySQL.
3. Show a success message.
4. Set inquiry status to `Pending`.

Success message:

"Your inquiry has been submitted successfully. Our team will contact you shortly."

Prevent empty submissions and validate email/contact fields.

---

# 7. Purchase Request

Create a purchase request form.

Fields:

* Full Name
* Contact Number
* Email
* Address
* Preferred Contact Method
* Message

Automatically associate the request with the selected vehicle.

Save it in the database.

Set the request status to:

`Pending`

The administrator can then contact the customer and process the sale manually.

Do NOT implement online payment.

---

# 8. About Page — `about.php`

Create an About Us page containing:

* Company introduction
* Mission
* Vision
* Why choose us
* Simple statistics

Example:

"CarBuy is a trusted car dealership focused on making vehicle buying simple, transparent, and convenient."

---

# 9. Contact Page — `contact.php`

Include:

* Business address
* Phone number
* Email
* Business hours
* Contact form
* Google Maps placeholder

Contact form fields:

* Name
* Email
* Subject
* Message

Store submitted messages in MySQL.

---

# 10. Admin Login — `admin/login.php`

Create a secure admin login page.

Fields:

* Username
* Password

Use PHP sessions.

Passwords must be stored using `password_hash()`.

Use `password_verify()` during login.

Prevent access to admin pages unless the administrator is authenticated.

Include:

"Remember me" only if it can be implemented securely.

Do not store plain-text passwords.

---

# 11. Admin Dashboard — `admin/dashboard.php`

Create a professional dashboard.

Display statistics:

### Vehicle Statistics

* Total Vehicles
* Available
* Reserved
* Sold

### Sales Statistics

* Total Sales
* Sales This Month
* Total Revenue

### Inquiry Statistics

* Pending Inquiries
* Contacted Inquiries
* Completed Inquiries

Use MySQL queries to calculate these values dynamically.

---

# 12. Vehicle Management — `admin/cars.php`

Create a vehicle management page.

Display a table containing:

* Image
* Brand
* Model
* Year
* Price
* Status
* Date Added
* Actions

Actions:

* View
* Edit
* Delete
* Mark Available
* Mark Reserved
* Mark Sold

Include:

"Add Vehicle"

button.

---

# 13. Add Vehicle — `admin/add-car.php`

Create a vehicle form.

Fields:

* Brand
* Model
* Year
* Price
* Mileage
* Transmission
* Fuel Type
* Color
* Engine
* Description
* Vehicle Image

Allow multiple images if possible.

Validate:

* Required fields
* Numeric price
* Valid year
* Valid mileage
* Valid image file

Allowed image types:

* JPG
* JPEG
* PNG
* WEBP

Limit file size.

Store uploaded images inside:

`uploads/cars/`

Save image paths in the database.

---

# 14. Edit Vehicle

Create:

`admin/edit-car.php`

Allow administrators to update:

* Vehicle information
* Price
* Description
* Status
* Images

Provide:

"Save Changes"

button.

---

# 15. Delete Vehicle

When deleting a vehicle:

1. Ask for confirmation.
2. Delete the database record.
3. Delete associated images if possible.

Do not allow accidental deletion.

---

# 16. Inquiry Management — `admin/inquiries.php`

Display all customer inquiries.

Columns:

* Customer
* Vehicle
* Email
* Contact
* Message
* Date
* Status
* Actions

Inquiry statuses:

* Pending
* Contacted
* Completed
* Cancelled

Administrator can change the status.

Allow filtering by status.

---

# 17. Customer/Buyer Management — `admin/customers.php`

Display customers who submitted inquiries or purchase requests.

Information:

* Name
* Email
* Contact
* Address
* Number of inquiries
* Purchase status
* Date added

Allow the admin to view customer details.

---

# 18. Sales Management — `admin/sales.php`

Create a sales management page.

Display:

* Vehicle
* Buyer
* Selling Price
* Sale Date
* Payment Status
* Sales Status

Allow admin to record a sale.

Sale form:

* Select Vehicle
* Buyer Name
* Buyer Contact
* Selling Price
* Sale Date
* Notes

After recording a sale:

Automatically change the vehicle status to:

`Sold`

The sold vehicle should no longer appear in the available vehicle listings.

---

# 19. Vehicle Status Automation

Implement automatic status changes.

Vehicle statuses:

```text
Available
Reserved
Sold
```

Workflow:

```text
Available
    ↓
Customer Inquiry
    ↓
Admin Reviews
    ↓
Reserved
    ↓
Sale Completed
    ↓
Sold
```

When a vehicle is marked `Sold`:

* Remove it from available listings.
* Update dashboard statistics.
* Record the sale.

When a vehicle is marked `Available`:

* Make it visible in the marketplace again.

---

# 20. Sales Reports — `admin/reports.php`

Create a simple reporting page.

Display:

* Total vehicles sold
* Total revenue
* Sales this month
* Sales this year
* Average selling price

Show a table of recent sales.

Add filters:

* Date range
* Vehicle
* Buyer

Use PHP/MySQL to calculate the report.

If charts are implemented, use vanilla JavaScript or CSS rather than external chart libraries.

---

# 21. Database

Create a MySQL database named:

`carbuy`

Create these tables:

## `admins`

Fields:

```text
id
username
password
created_at
```

## `cars`

Fields:

```text
id
brand
model
year
price
mileage
transmission
fuel_type
color
engine
description
status
created_at
updated_at
```

## `car_images`

Fields:

```text
id
car_id
image_path
created_at
```

## `inquiries`

Fields:

```text
id
car_id
customer_name
email
contact
message
status
created_at
```

## `customers`

Fields:

```text
id
name
email
contact
address
created_at
```

## `sales`

Fields:

```text
id
car_id
customer_id
selling_price
sale_date
notes
created_at
```

## `contact_messages`

Fields:

```text
id
name
email
subject
message
created_at
```

Use foreign keys where appropriate.

---

# 22. File Structure

Use this structure:

```text
carbuy/
│
├── index.php
├── cars.php
├── car-details.php
├── inquiry.php
├── purchase-request.php
├── about.php
├── contact.php
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── cars.php
│   ├── add-car.php
│   ├── edit-car.php
│   ├── inquiries.php
│   ├── customers.php
│   ├── sales.php
│   └── reports.php
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── admin-header.php
│   ├── admin-sidebar.php
│   └── auth.php
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   │
│   ├── js/
│   │   └── script.js
│   │
│   └── images/
│
├── uploads/
│   └── cars/
│
└── database/
    └── carbuy.sql
```

---

# 23. Design Requirements

Create a modern professional automotive dealership design.

Use:

* Clean white/black/gray color palette
* Large vehicle photography
* Rounded cards
* Clear buttons
* Professional typography
* Spacious layouts
* Responsive design
* Modern dashboard
* Sidebar navigation for admin

Do not make the design overly complicated.

The website should look like a real modern car dealership.

---

# 24. JavaScript Features

Use vanilla JavaScript for:

* Mobile navigation
* Search/filter interactions
* Price range filtering
* Form validation
* Image previews
* Delete confirmations
* Modal dialogs
* Dashboard UI interactions
* Dynamic price display where needed

Do not use jQuery.

---

# 25. PHP Requirements

Use reusable PHP includes.

Create a centralized database connection.

Use:

* Sessions
* Prepared statements
* Input validation
* Output escaping
* Secure file uploads
* Authentication middleware

Never directly insert raw user input into SQL queries.

Use:

```php
htmlspecialchars()
```

when displaying user-generated content.

Use prepared statements for database queries.

---

# 26. Security

Implement basic security practices:

* Password hashing
* Session authentication
* Prepared SQL statements
* Input validation
* Output escaping
* Secure image upload validation
* Admin route protection
* Logout functionality
* CSRF protection for important admin forms if practical

Do not expose database credentials publicly.

---

# 27. Sample Data

Include sample vehicle data so the system is immediately testable.

Add at least:

* Toyota Vios
* Honda Civic
* Toyota Fortuner
* Mitsubishi Xpander
* Honda City
* Toyota Innova

Use realistic sample prices, mileage, specifications, and descriptions.

Create a sample administrator account through the SQL setup, but clearly indicate that the password should be changed after installation.

---

# 28. Error Handling

Create user-friendly messages for:

* Database errors
* Invalid forms
* Missing vehicles
* Failed image uploads
* Unauthorized admin access
* Invalid login
* Duplicate/invalid requests

Do not expose raw PHP errors or SQL errors to normal users.

---

# 29. Overall User Flow

Customer:

```text
Home
 ↓
Browse Cars
 ↓
Search / Filter
 ↓
Vehicle Details
 ↓
Send Inquiry
 ↓
Inquiry Submitted
 ↓
Admin Contacts Customer
 ↓
Vehicle Reserved
 ↓
Sale Completed
 ↓
Vehicle Marked Sold
```

Admin:

```text
Login
 ↓
Dashboard
 ↓
Manage Vehicles
 ↓
Receive Inquiry
 ↓
Contact Customer
 ↓
Reserve Vehicle
 ↓
Record Sale
 ↓
Vehicle Automatically Becomes Sold
 ↓
Sales Report Updated
```

---

# 30. Important Scope Limitation

Keep the system simple.

Do NOT implement:

* Online payment gateway
* Credit/loan processing
* Insurance processing
* Vehicle registration
* Accounting system
* POS system
* Real-time chat
* SMS API
* Email API
* AI
* External vehicle APIs

The main purpose is:

**Vehicle Listing + Customer Inquiry + Vehicle Management + Sales Management + Basic Automation + Reports**

---

# 31. Final Requirement

Generate the complete working project.

Provide:

1. All PHP files
2. All HTML structure
3. All CSS
4. All JavaScript
5. MySQL database SQL file
6. Database connection
7. Admin authentication
8. CRUD functionality
9. Image upload functionality
10. Inquiry system
11. Sales system
12. Dashboard
13. Reports
14. Responsive design

Make sure all pages are connected and functional.

The final system must be runnable using:

```text
XAMPP
Apache
MySQL
PHP
```

The project should work by placing the `carbuy` folder inside:

```text
xampp/htdocs/
```

and importing:

```text
database/carbuy.sql
```

into phpMyAdmin.

Do not provide pseudocode. Generate actual working PHP, HTML, CSS, JavaScript, and MySQL code.
