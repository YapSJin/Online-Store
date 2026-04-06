<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. 权限检查
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

require_once "../config/db.php";
include "header.php";

$message = "";

// 2. 处理图片上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_img'])) {
    $target_key = $_POST['setting_key'];
    $upload_dir = "../assets/image/home/";
    
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if ($_FILES['new_img']['error'] === 0) {
        $ext = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
        $file_name = "bg_" . $target_key . "_" . time() . "." . $ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['new_img']['tmp_name'], $target_path)) {
            $db_path = "../assets/image/home/" . $file_name;
            
            $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = $_db->prepare($sql);
            $stmt->execute([$target_key, $db_path, $db_path]);
            
            $message = "<div style='color:green; padding:10px; border:1px solid green; margin: 10px 0;'>✅ Successfully updated!</div>";
        }
    }
}

// 获取当前图片用于展示
$stmt = $_db->query("SELECT setting_key, setting_value FROM site_settings");
$cms = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$hero_curr = $cms['home_hero_bg'] ?? '../assets/image/home/homepagebackground.png';
$promo_curr = $cms['home_promo_bg'] ?? '../assets/image/home/promotionbackground.png';
?>

<div id="admin-photo-manager" style="max-width: 800px; margin: 50px auto; padding: 20px; background: white; border: 1px solid #ddd; min-height: 500px; position: relative; z-index: 100;">
    <h2 style="color: #333; border-bottom: 2px solid #e67e22; padding-bottom: 10px;">🖼️ Homepage Photo Management</h2>
    
    <?= $message ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        
        <div style="border: 1px solid #eee; padding: 15px;">
            <h4 style="margin-top:0;">Main Hero Image</h4>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 12px; color: #666;">Current:</p>
                <img src="<?= $hero_curr ?>" style="width: 100%; height: 100px; object-fit: cover; border: 1px solid #ccc;">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="setting_key" value="home_hero_bg">
                <input type="file" name="new_img" accept="image/*" required>
                <button type="submit" style="margin-top:10px; padding: 8px 15px; background: #2c3e50; color: white; border: none; cursor: pointer;">Save Hero</button>
            </form>
        </div>

        <div style="border: 1px solid #eee; padding: 15px;">
            <h4 style="margin-top:0;">Promotion Image</h4>
            <div style="margin-bottom: 10px;">
                <p style="font-size: 12px; color: #666;">Current:</p>
                <img src="<?= $promo_curr ?>" style="width: 100%; height: 100px; object-fit: cover; border: 1px solid #ccc;">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="setting_key" value="home_promo_bg">
                <input type="file" name="new_img" accept="image/*" required>
                <button type="submit" style="margin-top:10px; padding: 8px 15px; background: #e67e22; color: white; border: none; cursor: pointer;">Save Promo</button>
            </form>
        </div>

    </div>

    <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
        <a href="manageUser.php" style="color: #3498db; text-decoration: none;">← Return to User Management</a>
    </div>
</div>

<?php include "../public/footer.php"; ?>
