<?php
/**
 * CarBuy - Shared Helper Functions
 * Include after config/database.php
 */

if (!defined('CARBUY_BASE_INCLUDED')) {

// Format a number as a currency (Philippine Peso by default).
function format_money($amount, $currency = '₱') {
    $amount = (float)$amount;
    return $currency . number_format($amount, 2);
}

// Sanitize text input.
function clean($value) {
    return trim(strip_tags((string)($value ?? '')));
}

// Validate an email address.
function valid_email($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Basic CSRF token generation & verification.
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Build a redirect-friendly status class name.
function status_class(string $status): string {
    $map = [
        'Available' => 'status-available',
        'Reserved'  => 'status-reserved',
        'Sold'      => 'status-sold',
        'Pending'   => 'status-pending',
        'Contacted' => 'status-contacted',
        'Completed' => 'status-completed',
        'Cancelled' => 'status-cancelled',
        'Paid'      => 'status-available',
        'Partial'   => 'status-reserved',
    ];
    return $map[$status] ?? 'status-pending';
}

// First image path for a car, with a placeholder fallback.
// Returns an absolute project URI so it works from any folder depth.
function car_image(array $car): string {
    $proj = project_uri();
    if (!empty($car['image_path'])) {
        $rel = ltrim($car['image_path'], '/');
        $abs = __DIR__ . '/../' . $rel; // includes/../ = project root
        if (file_exists($abs)) {
            return $proj . '/' . $rel;
        }
    }
    return $proj . '/assets/images/car-placeholder.svg';
}

// Shorthand for outputting the root URI prefix in templates.
function asset_uri(string $path = ''): string {
    return project_uri() . '/' . ltrim($path, '/');
}

/**
 * Remove an uploaded file (relative web path like "uploads/cars/x.jpg")
 * from the filesystem project directory. Returns true on success.
 */
function delete_uploaded_file(string $path): bool {
    if ($path === '') return false;
    $rel = ltrim($path, '/');
    // Only ever delete files that live under uploads/.
    if (strpos($rel, 'uploads/') !== 0) return false;
    $abs = __DIR__ . '/../' . $rel;
    if (is_file($abs)) {
        return @unlink($abs);
    }
    return false;
}

// Flash message helpers.
function set_flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}
function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

define('CARBUY_BASE_INCLUDED', true);
}
// ----- Image upload helpers ----------------------------------------------
const UPLOAD_MAX_BYTES = 5242880; // 5 MB
const UPLOAD_ALLOWED = ['jpg', 'jpeg', 'png', 'webp'];

/**
 * Validate a single uploaded image and store it. Returns the web path
 * (e.g. "uploads/cars/abc123.jpg") or an error string prefixed with "ERR:".
 */
function upload_car_image(array $file): string {
    if (!isset($file['error']) || is_array($file['error'])) {
        return 'ERR:Invalid upload request.';
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'ERR:No file was selected.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'ERR:File upload failed (error code ' . $file['error'] . ').';
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return 'ERR:File is too large. Maximum size is ' . (UPLOAD_MAX_BYTES / 1048576) . ' MB.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED, true)) {
        return 'ERR:Only JPG, JPEG, PNG, or WEBP images are allowed.';
    }

    // Verify the real image type using PHP's fileinfo.
    if (function_exists('finfo_open')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMime, true)) {
            return 'ERR:The uploaded file is not a valid image.';
        }
    }

    $dir = __DIR__ . '/../uploads/cars';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return 'ERR:Unable to save the uploaded image.';
    }
    return 'uploads/cars/' . $name;
}