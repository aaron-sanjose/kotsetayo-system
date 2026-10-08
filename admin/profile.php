<?php
/**
 * KotseTayo - Admin Account (change password / username)
 */
require_once __DIR__ . '/../includes/admin-header.php';

$admin = current_admin();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $current_pw = (string)($_POST['current_password'] ?? '');
        $new_username = clean($_POST['username'] ?? '');
        $new_pw = (string)($_POST['new_password'] ?? '');
        $confirm_pw = (string)($_POST['confirm_password'] ?? '');

        $db_admin = fetch_one("SELECT * FROM admins WHERE id = ?", [$admin['id']]);

        if (!password_verify($current_pw, $db_admin['password'])) {
            $errors[] = 'Your current password is incorrect.';
        }
        if ($new_username === '') {
            $errors[] = 'Username cannot be empty.';
        } else {
            $exists = fetch_one("SELECT id FROM admins WHERE username = ? AND id != ?", [$new_username, $admin['id']]);
            if ($exists) $errors[] = 'That username is already taken.';
        }
        if ($new_pw !== '' && strlen($new_pw) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($new_pw !== $confirm_pw) {
            $errors[] = 'New password and confirmation do not match.';
        }

        if (empty($errors)) {
            if ($new_pw !== '') {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                query("UPDATE admins SET username = ?, password = ?, remember_token = NULL WHERE id = ?", [$new_username, $hash, $admin['id']]);
            } else {
                query("UPDATE admins SET username = ? WHERE id = ?", [$new_username, $admin['id']]);
            }
            $_SESSION['admin_username'] = $new_username;
            $success = 'Account updated successfully.';
        }
    }
}

$token = csrf_token();
$db_admin = fetch_one("SELECT * FROM admins WHERE id = ?", [$admin['id']]);
?>

<div class="panel">
    <div class="panel-head"><h2>Manage Admin Account</h2></div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="profile.php" class="form admin-form form-narrow" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo e($db_admin['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="current_password">Current Password *</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password (leave blank to keep current)</label>
            <input type="password" id="new_password" name="new_password" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>