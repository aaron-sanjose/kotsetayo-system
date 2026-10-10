-- ============================================================
-- KotseTayo - Car Buy & Sell Management System
-- Database: kotsetayo
-- Import this file into phpMyAdmin (XAMPP) to set up the system.
--
-- SAMPLE ADMIN ACCOUNT
--   Username : admin
--   Password : admin123
--   IMPORTANT: Change this password after your first login!
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kotsetayo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kotsetayo`;

-- ------------------------------------------------------------
-- Table: admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: cars
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cars` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brand` VARCHAR(50) NOT NULL,
  `model` VARCHAR(80) NOT NULL,
  `year` SMALLINT NOT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `mileage` INT NOT NULL DEFAULT 0,
  `transmission` ENUM('Automatic', 'Manual') NOT NULL DEFAULT 'Automatic',
  `fuel_type` VARCHAR(30) NOT NULL DEFAULT 'Gasoline',
  `color` VARCHAR(30) NOT NULL DEFAULT '',
  `engine` VARCHAR(60) NOT NULL DEFAULT '',
  `description` TEXT,
  `status` ENUM('Available', 'Reserved', 'Sold') NOT NULL DEFAULT 'Available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cars_brand` (`brand`),
  KEY `idx_cars_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ------------------------------------------------------------
-- Table: car_images
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `car_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `car_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_car_images_car` (`car_id`),
  KEY `idx_car_images_order` (`car_id`, `sort_order`),
  CONSTRAINT `fk_car_images_car`
    FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: inquiries
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `car_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `contact` VARCHAR(40) NOT NULL,
  `message` TEXT,
  `status` ENUM('Pending', 'Contacted', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiries_car` (`car_id`),
  KEY `idx_inquiries_status` (`status`),
  CONSTRAINT `fk_inquiries_car`
    FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ------------------------------------------------------------
-- Table: customers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `contact` VARCHAR(40) NOT NULL,
  `address` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_email_contact` (`email`, `contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: sales
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `car_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `selling_price` DECIMAL(12, 2) NOT NULL,
  `sale_date` DATE NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Partial') NOT NULL DEFAULT 'Pending',
  `notes` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sales_car` (`car_id`),
  KEY `idx_sales_customer` (`customer_id`),
  KEY `idx_sales_sale_date` (`sale_date`),
  CONSTRAINT `fk_sales_car`
    FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: contact_messages
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `subject` VARCHAR(150) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Sample admin account (password: admin123 - CHANGE AFTER INSTALL)
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$BGj1c9VzlexIF/t1/uSTp.RopZ/DjepZTKjmhrtPojEjCmEycGQ4.');

-- Sample vehicles
INSERT INTO `cars`
  (`brand`, `model`, `year`, `price`, `mileage`, `transmission`, `fuel_type`, `color`, `engine`, `description`, `status`)
VALUES
('Toyota',    'Vios 1.5 S',     2020, 768000.00,  42000, 'Automatic', 'Gasoline', 'Gray',     '1.5L 4-cylinder',    'Well-maintained Toyota Vios in excellent condition. Single owner, fully documented service history, fuel efficient and perfect for daily city driving.', 'Available'),
('Honda',     'Civic 1.8',       2019, 1180000.00, 53000, 'Automatic', 'Gasoline', 'White',    '1.8L i-VTEC',       'Sporty and reliable Honda Civic with clean interior, new tires, and a smooth automatic transmission. A great value compact sedan.',                    'Available'),
('Toyota',    'Fortuner 2.8',    2021, 2180000.00, 28000, 'Automatic', 'Diesel',   'Black',    '2.8L Turbo Diesel', 'Powerful 7-seater Toyota Fortuner. Full specifications, excellent for family and long drives. Ready for immediate release.',                             'Available'),
('Mitsubishi','Xpander GLS',     2021, 1180000.00, 35000, 'Automatic', 'Gasoline', 'Silver',   '1.5L MIVEC',        'Modern Mitsubishi Xpander MPV with comfortable seating, good fuel economy, and a spacious cabin. Great for family trips.',                             'Available'),
('Honda',     'City 1.5 S',      2018, 690000.00,  61000, 'Manual',    'Gasoline', 'Red',      '1.5L i-VTEC',       'Economical and reliable Honda City. Clean title and well maintained. Documented history available.',                                                   'Reserved'),
('Toyota',    'Innova 2.0 E',    2020, 1540000.00, 47000, 'Automatic', 'Diesel',   'Dark Gray','2.0L Diesel',       'Spacious Toyota Innova for large families. Comfortable ride and outstanding reliability. Low mileage.',                                                  'Sold'),
('Nissan',    'Almera 1.0',      2022, 830000.00,  15000, 'Automatic', 'Gasoline', 'Blue',     '1.0L Turbo',        'Almost brand-new Nissan Almera with low fuel consumption and modern features.',                                                                        'Available'),
('Ford',      'Ranger 2.0 XLT',  2021, 1850000.00, 31000, 'Manual',    'Diesel',   'White',    '2.0L Diesel',       'Dependable pickup that is versatile for both work and personal use.',                                                                                   'Available');

-- Sample vehicle images (each car gets 1-2 placeholder images)
INSERT INTO `car_images` (`car_id`, `image_path`) VALUES
(1, 'assets/images/car-vios.svg'),
(1, 'assets/images/car-vios.svg'),
(2, 'assets/images/car-civic.svg'),
(3, 'assets/images/car-fortuner.svg'),
(4, 'assets/images/car-xpander.svg'),
(5, 'assets/images/car-city.svg'),
(6, 'assets/images/car-innova.svg'),
(7, 'assets/images/car-almera.svg'),
(8, 'assets/images/car-vienna.svg');

-- Sample customers
INSERT INTO `customers` (`name`, `email`, `contact`, `address`) VALUES
('Juan Dela Cruz', 'juan.delacruz@example.com', '09171234567', '123 Mabini St., Manila'),
('Maria Santos',   'maria.santos@example.com',  '09281234567', '456 Rizal Ave., Quezon City'),
('Pedro Reyes',    'pedro.reyes@example.com',   '09391234567', '789 Bonifacio Drive, Makati');

-- Sample inquiries
INSERT INTO `inquiries` (`car_id`, `customer_name`, `email`, `contact`, `message`, `status`) VALUES
(1, 'Juan Dela Cruz', 'juan.delacruz@example.com', '09171234567', 'Is the price negotiable? Available for viewing tomorrow.', 'Pending'),
(2, 'Maria Santos',   'maria.santos@example.com',   '09381234567', 'Does this include free LTO registration?',                  'Contacted'),
(3, 'Pedro Reyes',    'pedro.reyes@example.com',    '09391234567', 'Interested in a test drive next weekend.',                    'Completed'),
(5, 'Anna Lopez',     'anna.lopez@example.com',     '09061234567', 'What is the final cash price?',                               'Cancelled');

-- Sample sales (corresponds to the sold Innova)
INSERT INTO `sales` (`car_id`, `customer_id`, `selling_price`, `sale_date`, `payment_status`, `notes`) VALUES
(6, 1, 1540000.00, CURDATE(), 'Paid', 'Full payment received. Vehicle released to buyer.');

-- Sample contact messages
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`) VALUES
('Test Visitor', 'visitor@example.com', 'Hours', 'What time do you open on Saturdays?');