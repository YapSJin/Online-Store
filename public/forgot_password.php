<?php
include "header.php"; 
require '../config/db.php';
require '../lib/PHPMailer.php';
require '../lib/SMTP.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // 检查邮箱是否存在
    $stmt = $_db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 生成 token 和过期时间
        $token = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 一小时过期

        // 删除旧的记录
        $_db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // 插入新的 token
        $insert = $_db->prepare("INSERT INTO password_resets (email, reset_token, expires_at) VALUES (?, ?, ?)");
        $insert->execute([$email, $token, $expiresAt]);

        // 重置链接 (替换为你的路径)
        $resetLink = "http://localhost:8000/public/reset_password.php?token=" . $token;

        // 使用 PHPMailer 发送邮件
        $mail = new PHPMailer(true);
        try {
            // 配置 SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jiaxuan0947@gmail.com';
            $mail->addEmbeddedImage("..$user->profile_image",'photo');
            $mail->Password = 'adom xuqb vnwz egcl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // 发件人和收件人
            $mail->setFrom('jiaxuan0947@gmail.com', 'System Administrator'); // 你的名字
            $mail->addAddress($email);

            // 邮件内容
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
                        <img src='cid:photo'
                 style='width: 200px; height: 200px;
                        border: 1px solid #333'>
                    <h3><p>Dear $user->username,<p></h3>

                <br><h4>Please click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a></h4>";

                        // if using PHPMailer:
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true
                ]
            ];

            // 发送邮件
            $mail->send();
            $success = "Please check your email for the password reset link.";
        } catch (Exception $e) {
            $error = "Failed to send email: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email address not found.";
    }
}
?>

<!-- HTML 部分 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/forgot_password.css">
</head>
<body>
    <div class="container">
        <form method="POST">
            <label class="title">Forgot Password</label>
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Send Reset Link">

            <!-- 错误信息显示 -->
            <?php if (!empty($error)) : ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- 成功信息显示 -->
            <?php if (!empty($success)) : ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
