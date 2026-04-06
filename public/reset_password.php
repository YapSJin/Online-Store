<?php
// reset_password.php
date_default_timezone_set('Asia/Kuala_Lumpur');
require '../config/db.php';

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    $errors[] = 'Invalid request.';
} else {
    // Check if token exists and not expired
    $stmt = $_db->prepare("
        SELECT email, UNIX_TIMESTAMP(expires_at) AS expires_ts
        FROM password_resets
        WHERE reset_token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $errors[] = 'Invalid or expired reset link.';
    }
}

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic checks
    if (empty($newPassword)) {
        $errors[] = 'New password is required.';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    // Password complexity check
    if (!preg_match('/[A-Z]/', $newPassword)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $newPassword)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $newPassword)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if (!preg_match('/[\W_]/', $newPassword)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // Final check and update
    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $upd = $_db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $upd->execute([$hashedPassword, $reset['email']]);

        $_db->prepare("DELETE FROM password_resets WHERE reset_token = ?")->execute([$token]);

        $success = '✅ Password has been successfully reset. <a href="login.php">Go to login</a>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/reset_password.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if (!empty($errors)): ?>
    <div style="color: red; margin-bottom: 10px;">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success" style="color: green;"><?= $success ?></p>
<?php else: ?>
    <!-- Show form even if there are errors -->
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group">
            <input type="password" class="password-input" name="new_password" placeholder="Enter new password" required>
        </div>
        <div class="form-group">
            <input type="password" class="password-input" name="confirm_password" placeholder="Confirm new password" required>
        </div>
        <button type="submit" class="reset-btn">Reset Password</button>
    </form>
<?php endif; ?>

    </div>
</body>
</html>
