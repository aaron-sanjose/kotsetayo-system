<?php
/**
 * KotseTayo - Admin Header
 * Include at the top of every admin page (always behind authentication).
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

require_admin();

$admin_page = basename($_SERVER['SCRIPT_NAME']);
$admin_titles = [
    'dashboard.php'   => 'Dashboard',
    'cars.php'        => 'Vehicle Management',
    'add-car.php'     => 'Add Vehicle',
    'edit-car.php'    => 'Edit Vehicle',
    'inquiries.php'   => 'Inquiry Management',
    'customers.php'   => 'Customers / Buyers',
    'sales.php'       => 'Sales Management',
    'reports.php'     => 'Sales Reports',
    'login.php'       => 'Login',
];
$admin_title = $admin_titles[$admin_page] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($admin_title); ?> | KotseTayo Admin</title>
    <link rel="stylesheet" href="<?php echo asset_uri('assets/css/admin.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-layout">
    <?php require __DIR__ . '/admin-sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">&#9776;</button>
            <h1><?php echo e($admin_title); ?></h1>
            <div class="admin-user">
                <span><?php echo e(current_admin()['username'] ?? 'admin'); ?></span>
                <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>

        <?php foreach (get_flashes() as $flash): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?> alert-flash">
            <?php echo e($flash['message']); ?>
        </div>
        <?php endforeach; ?>

        <main class="admin-content">