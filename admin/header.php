<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}

$publicUrl = $appRoot . '/public';
$adminUrl = $appRoot . '/admin';
$assetsUrl = $appRoot . '/assets';

$faviconFile = realpath(__DIR__ . '/../assets/image/home/logo.png');
$faviconVersion = $faviconFile && file_exists($faviconFile) ? filemtime($faviconFile) : time();
$faviconUrl = $assetsUrl . '/image/home/logo.png?v=' . $faviconVersion;

$sessionUser = $_SESSION['user'] ?? null;
$sessionUserName = '';
if (is_array($sessionUser)) {
    $sessionUserName = $sessionUser['username'] ?? '';
} elseif (is_object($sessionUser)) {
    $sessionUserName = $sessionUser->username ?? '';
}
?>

<link rel="icon" type="image/png" href="<?= $faviconUrl ?>">
<link rel="stylesheet" href="<?= $assetsUrl ?>/css/header.css">
<link rel="stylesheet" href="<?= $assetsUrl ?>/css/footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<header>
    <a href="<?= $adminUrl ?>/adminHome.php" class="logo-link">
        <img src="<?= $assetsUrl ?>/image/home/logo.png" class="logo">
        <span class="brand-name">LALA</span>
    </a>

    <nav class="nav-desktop">
        <div class="nav-links">
            <a href="<?= $adminUrl ?>/adminHome.php">Home</a>
            <a href="<?= $publicUrl ?>/admin/index.php">Products</a>
            <a href="<?= $publicUrl ?>/member/index.php">Members</a>
            <a href="<?= $publicUrl ?>/home.php#contact">Contact</a>

            <a href="<?= $adminUrl ?>/manageUser.php" class="admin-panel-link">
                <i class="fa-solid fa-user-shield"></i> Admin Panel
            </a>
        </div>

        <div class="auth-buttons">
            <?php if ($sessionUser): ?>
                <span class="user-greeting">
                    <a href="<?= $publicUrl ?>/profile.php" class="user-name-link">
                        <?= htmlspecialchars($sessionUserName !== '' ? $sessionUserName : 'User') ?>
                    </a>
                </span>
                <a href="<?= $publicUrl ?>/logout.php" class="login-btn">Logout</a>
            <?php else: ?>
                <a href="<?= $publicUrl ?>/login.php" class="login-btn">Login</a>
                <a href="<?= $publicUrl ?>/register.php" class="register-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
