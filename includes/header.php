<?php
/**
 * KotseTayo - Public Header/Navbar
 * Include at the top of every public page.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['SCRIPT_NAME']);

function nav_active(string $page): string {
    global $current_page;
    return $current_page === $page ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KotseTayo - Car Buy &amp; Sell Management System</title>
    <meta name="description" content="KotseTayo is a trusted car dealership for buying and selling quality new and used vehicles.">
    <link rel="stylesheet" href="<?php echo asset_uri('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a href="<?php echo asset_uri('index.php'); ?>" class="brand">
            <img src="<?php echo asset_uri('assets/images/KotseTayo.png'); ?>" alt="KotseTayo logo" class="brand-mark">
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="mainNav">
            <a href="<?php echo asset_uri('index.php'); ?>"<?php echo nav_active('index.php'); ?>>Home</a>
            <a href="<?php echo asset_uri('cars.php'); ?>"<?php echo nav_active('cars.php'); ?>>Cars</a>
            <a href="<?php echo asset_uri('about.php'); ?>"<?php echo nav_active('about.php'); ?>>About</a>
            <a href="<?php echo asset_uri('contact.php'); ?>"<?php echo nav_active('contact.php'); ?>>Contact</a>
            <a href="<?php echo asset_uri('admin/login.php'); ?>" class="nav-admin-btn">Admin Login</a>
        </nav>
    </div>
</header>

<?php foreach (get_flashes() as $flash): ?>
<div class="alert alert-<?php echo e($flash['type']); ?> container">
    <?php echo e($flash['message']); ?>
</div>
<?php endforeach; ?>
<main>