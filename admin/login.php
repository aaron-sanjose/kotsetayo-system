<?php
/**
 * KotseTayo - Admin Login
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$remember = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $admin = fetch_one("SELECT * FROM admins WHERE username = ?", [$username]);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = (int)$admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            if ($remember) {
                // Store a remember token in a cookie (hashed) for 30 days.
                $token = bin2hex(random_bytes(32));
                $hashed = hash('sha256', $token);
                setcookie('carbyu_remember', $admin['id'] . ':' . $token, [
                    'expires'  => time() + 60 * 60 * 24 * 30,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                query("UPDATE admins SET remember_token = ? WHERE id = ?", [$hashed, $admin['id']]);
            }

            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

require_once __DIR__ . '/../includes/header.php';
// Override styles for a centered login card.
?>
<section class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <img src="<?php echo asset_uri('assets/images/KotseTayo.png'); ?>" alt="KotseTayo logo" class="brand-mark">
            <span class="brand-text">Admin</span>
        </div>
        <h1>Admin Login</h1>
        <p class="login-sub">Please sign in to manage KotseTayo.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="form" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="remember" value="1" <?php echo $remember ? 'checked' : ''; ?>> Remember me (30 days)</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>

        <p class="login-hint">Default credentials: <strong>admin</strong> / <strong>admin123</strong> (please change after first login).</p>
        <p><a href="<?php echo asset_uri('index.php'); ?>" class="login-back">&larr; Back to Website</a></p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>