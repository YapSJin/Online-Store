<?php
require_once "../config/db.php";
require_once "../helper/html_helper.php";
require_once "../models/userModels.php";

// ✅ 必须先检查 session，再 output
if (!isset($_SESSION['user_id'])) {
    redirect("/public/login.php");
}

$user_id = $_SESSION['user_id'];

// ✅ 处理 POST（验证密码）
if (is_post() && isset($_POST['verify'])) {
    $stored_hash = getUserPasswordById($user_id); // 返回对象 -> $stored_hash->password

    $verifyPassword = $_POST['verify_password'] ?? '';
    $passwordOk = false;
    if ($stored_hash && isset($stored_hash->password)) {
        if (password_verify($verifyPassword, $stored_hash->password)) {
            $passwordOk = true;
        } elseif (hash_equals($stored_hash->password, sha1($verifyPassword))) {
            $passwordOk = true;
        }
    }

    if ($passwordOk) {
        temp("info","✅ Password Correct!");
        redirect("edit_profile.php");
    } else {
        temp("info","❌ Password Incorrect");
    }
}

// 获取用户基本信息
$query = $_db->prepare("SELECT * FROM users WHERE id = :user_id");
$query->execute([':user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// 获取地址
$address_query = $_db->prepare("SELECT * FROM address WHERE user_id = :user_id ORDER BY is_default DESC, id DESC LIMIT 1");
$address_query->execute([':user_id' => $user_id]);
$address = $address_query->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    $user_address = 'No address provided.';
} else {
    $user_address = ($address['full_name'] ?? '') . ' — ' . ($address['address_line'] ?? '') . ', ' . ($address['city'] ?? '') . ' ' . ($address['postcode'] ?? '');
    $user_address = trim($user_address, " \t\n\r\0\x0B-—");
}

// 头像
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}

$publicUrl = $appRoot . '/public';
$componentUrl = $appRoot . '/component';

$rawProfileImage = trim((string)($user['profile_image'] ?? ''));
$rawProfileImage = str_replace('\\', '/', $rawProfileImage);
if ($rawProfileImage !== '' && (str_starts_with($rawProfileImage, 'http://') || str_starts_with($rawProfileImage, 'https://'))) {
    $profile_image = $rawProfileImage;
} elseif ($rawProfileImage === '') {
    $profile_image = $appRoot . "/assets/image/logo/default.png";
} else {
    $profile_image = $appRoot . '/' . ltrim($rawProfileImage, '/');
}

// ✅ 最后才 include header（避免 header already sent）
include "header.php"; 
?>

<link rel="stylesheet" href="../assets/css/profile.css">

<body>
<div class="profile-container">
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <div class="profile-card">
        <img src="<?php echo $profile_image; ?>" class="profile-pic">

        <div class="profile-info">
            <div class="info-item">
                <strong>Email:</strong>
                <span class="field-text"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>

            <div class="info-item">
                <strong>Phone:</strong>
                <span class="field-text"><?php echo htmlspecialchars(($address['phone'] ?? '') !== '' ? $address['phone'] : ($user['phone_num'] ?? '')); ?></span>
            </div>

            <div class="info-item">
                <strong>Address:</strong> 
                <span class="address-text"><?php echo htmlspecialchars($user_address); ?></span>
                <a href="<?php echo htmlspecialchars($componentUrl . '/address.php'); ?>" class="manage-address btn-manage-address">Manage Address</a>
            </div>

            <div class="buttons">
                <?php if ($_SESSION['role'] === 'user'): ?>
                    <button type="button" class="btn btn-edit-profile" id="edit">Edit Profile</button>
                <?php else: ?>
                    <a href="edit_profile.php" class="btn btn-edit-profile">Edit Profile</a>
                <?php endif; ?>

                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>
    </div>
</div>
</body>

<!-- Password Popup -->
<div id="password-popup" style="display:none;">
    <div id="popup-box">
        <h3>Enter Password to Edit Profile</h3>

        <form method="POST">
            <input type="password" name="verify_password" required>
            <br><br>
            <button name="verify">Verify</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/profile.js"></script>

<?php include "../public/footer.php"; ?>
