<?php
require '../config/db.php';
require '../lib/PHPMailer.php';
require '../lib/SMTP.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!is_exists($email, 'users', 'email')) {
        $error = 'Email address not found.';
    } else {
        $token = bin2hex(random_bytes(16));
        save_reset_token($email, $token);
        $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/public/reset_password.php?token=' . $token;

        $mail = get_mail();
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request';
        $mail->isHTML(true);
        $mail->Body = "Click here to reset your password:<br><a href='$reset_link'>$reset_link</a>";

        try {
            $mail->send();
            $success = 'A password reset link has been sent to your email.';
        } catch (Exception $e) {
            $error = 'Mail sending failed: ' . $mail->ErrorInfo;
        }
    }
}

// Check if email exists in users table
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// Save token into password_resets table
function save_reset_token($email, $token) {
    global $_db;
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
    $stm = $_db->prepare("INSERT INTO password_resets (email, reset_token, created_at, expires_at) VALUES (?, ?, NOW(), ?)");
    $stm->execute([$email, $token, $expiresAt]);
}

// PHPMailer configuration
function get_mail() {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jiaxuan0947@gmail.com';     // ← 改成你的 Gmail
        $mail->Password   = 'adom xuqb vnwz egcl';      // ← 改成你生成的 App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'System Admin');
        $mail->CharSet = 'UTF-8';

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        // 开发调试用（可临时打开）
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        return $mail;
    } catch (Exception $e) {
        die('Mail setup failed: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/forgot_password.css">
</head>
<body>
    <h2 style="position: absolute; top: 80px; font-size: 28px;">Forgot Password</h2>

    <div class="container">
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Send Reset Link">

            <?php if (!empty($error)) : ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>