<?php 
// 1. 确保 Session 开启
if (session_status() === PHP_SESSION_NONE) session_start(); 

// 2. 引入必要文件 (注意路径)
include "header.php";
require_once "../config/db.php";
require_once "../models/userModels.php";    // 关键修复：必须引入用户模型
require_once "../models/addressModels.php";
require_once "../models/logModels.php";

// Step 1: 获取要编辑的用户 ID
$user_id_to_edit = $_GET['user_id'] ?? null;
if (!$user_id_to_edit) {
    die("User ID is required.");
}

// Step 2: 获取当前登录的管理员 ID
$admin_user_id = $_SESSION['user_id'] ?? null;
if (!$admin_user_id) {
    die("Admin is not logged in.");
}

// Step 3: 获取被编辑用户的详细信息
// 注意：如果你的模型返回的是对象，请统一使用 -> 访问
$user = getUserById($user_id_to_edit);
if (!$user) {
    die("User not found.");
}

// Step 4: 权限检查 (确保当前操作者是管理员)
// 这里直接检查 session 里的角色最安全高效
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

// Step 5: 获取地址数据
$addr = getDefaultAddress($user_id_to_edit);
// 兼容数组或对象访问（根据你 addressModels 的返回类型调整）
$address_id = $addr['id'] ?? ($addr->id ?? null);
$address_line = $addr['address_line'] ?? ($addr->address_line ?? '');
$city = $addr['city'] ?? ($addr->city ?? '');
$postcode = $addr['postcode'] ?? ($addr->postcode ?? '');
$full_name_addr = $addr['full_name'] ?? ($addr->full_name ?? '');
$phone_addr = $addr['phone'] ?? ($addr->phone ?? '');

// 州属列表
$states = ["Perlis", "Kedah", "Kelantan", "Terrengganu", "Pahang", "Johor", "Melaka", "Negeri Sembilan", "Putrajaya", "Selangor", "Perak", "Pulau Pinang", "Sarawak", "Sabah"];

// Step 6: 处理 POST 提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_phone = trim($_POST['phone_num']);
    $new_address_line = trim($_POST['address_line']);
    $new_city = trim($_POST['city']);
    $new_postcode = trim($_POST['postcode']);
    $password_raw = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $errors = [];

    // Step 7: 密码验证
    $password_hashed = null;
    if (!empty($password_raw)) {
        if ($password_raw !== $confirm_password) {
            $errors[] = "❌ Passwords do not match.";
        } elseif (strlen($password_raw) < 6) {
            $errors[] = "❌ Password must be at least 6 characters.";
        } else {
            $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
        }
    }

    // Step 8: 头像上传处理
    // 兼容对象和数组取值
    $current_profile_image = is_object($user) ? $user->profile_image : $user['profile_image'];
    $profile_image = $current_profile_image;

    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../assets/image/uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $file_name = "user_" . $user_id_to_edit . "_" . time() . "." . $file_ext;
        $target_path = $upload_dir . $file_name;

        $allowed_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Invalid file type.";
        } elseif (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $profile_image = "../assets/image/uploads/" . $file_name;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    // Step 9: 执行更新
    if (empty($errors)) {
        // 记录日志逻辑 (示例：对比旧值)
        // ... (此处可根据需要对比 $user 和 $username 等变量)

        // 更新用户基本资料
        updateUserProfile($user_id_to_edit, $username, $email, $new_phone, $profile_image, $password_hashed);

        // Step 10: 更新或添加地址
        if ($address_id) {
            updateAddress($address_id, $username, $new_address_line, $new_city, $new_postcode, $new_phone);
        } else {
            addAddress($user_id_to_edit, $username, $new_address_line, $new_city, $new_postcode, $new_phone);
        }

        // 使用你的全局函数进行跳转（假设你有这些函数）
        if (function_exists('temp')) temp("info", "✅ User Profile Updated");
        header("Location: manageUser.php");
        exit;
    } else {
        // 显示第一个错误
        if (function_exists('temp')) temp("info", $errors[0]);
    }
}
?>

<link rel="stylesheet" href="../assets/css/edit_profile.css">
<?php 
if (file_exists("../component/backButton.php")) {
    include "../component/backButton.php"; 
}
?>

<div class="edit-container">
    <h2>Edit User Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        <?php 
        // 确保模板路径正确
        if (file_exists('../component/editUserProfileTemplate.php')) {
            include '../component/editUserProfileTemplate.php'; 
        } else {
            echo "Template not found.";
        }
        ?>
        <div class="form-group" style="margin-top: 20px;">
            <input type="submit" value="Update Profile" class="btn" style="background-color: #2c3e50; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/imagePreview.js"></script>

