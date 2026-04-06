<?php
require_once '../../config/db.php';

$sessionRole = $_SESSION['role'] ?? null;
$sessionUser = $_SESSION['user'] ?? null;
if ($sessionRole !== 'admin') {
    if (is_array($sessionUser) && (($sessionUser['role'] ?? null) === 'admin')) {
        $sessionRole = 'admin';
    } elseif (is_object($sessionUser) && (($sessionUser->role ?? null) === 'admin')) {
        $sessionRole = 'admin';
    }
}

if ($sessionRole !== 'admin') {
    header("Location: ../admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $_db->prepare("SELECT id, username, email, phone_num, role, profile_image, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details - LALA Clothing</title>
    <link rel="stylesheet" href="member.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>
</head>
<body>

<?php include '../header.php'; ?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px; min-height: 60vh; max-width: 600px;">
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); text-align: center;">
        <h2 style="margin-bottom: 30px;">Member Details</h2>

        <?php 
            // 检查照片是否存在，不存在则显示默认占位图
            $photo_src = $row->profile_image ?? '';
            $photo_src = str_replace('\\', '/', $photo_src);
            if ($photo_src === '' || $photo_src === null) {
                $photo_src = "../../assets/image/logo/default.png";
            } else {
                $photo_src = ltrim($photo_src, '/');
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
                $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
                $appRoot = '';
                if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
                    $appRoot = '/' . $parts[0];
                }
                $photo_src = $appRoot . '/' . $photo_src;
            }
            
            // 检查注册时间字段是否存在，不存在则使用当前时间作为占位符
            $reg_date = isset($row->created_at) ? date('Y-m-d', strtotime($row->created_at)) : "N/A";
        ?>
        <img src="<?php echo $photo_src; ?>" width="180" height="180" 
             style="border-radius: 50%; object-fit: cover; border: 4px solid #f8f9fa; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
        
        <div style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 10px 0; font-size: 18px;"><b><i class="fa-solid fa-user" style="width: 25px;"></i> Username:</b> <?php echo htmlspecialchars($row->username); ?></p>
            <p style="margin: 10px 0; font-size: 18px;"><b><i class="fa-solid fa-envelope" style="width: 25px;"></i> Email:</b> <?php echo htmlspecialchars($row->email); ?></p>
            <p style="margin: 10px 0; font-size: 18px;"><b><i class="fa-solid fa-phone" style="width: 25px;"></i> Phone:</b> <?php echo htmlspecialchars($row->phone_num ?? ''); ?></p>
            <p style="margin: 10px 0; font-size: 18px;"><b><i class="fa-solid fa-id-badge" style="width: 25px;"></i> Role:</b> <?php echo htmlspecialchars($row->role ?? ''); ?></p>
            <p style="margin: 10px 0; font-size: 18px;"><b><i class="fa-solid fa-calendar" style="width: 25px;"></i> Registered:</b> <?php echo $reg_date; ?></p>
        </div>

        <div style="margin-top: 40px;">
            <a href="index.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">← Back to Member List</a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
