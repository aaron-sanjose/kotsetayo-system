<?php
/**
 * CarBuy - Admin Logout
 * Destroys the admin session and redirects to the login page.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_admin();

header('Location: login.php');
exit;