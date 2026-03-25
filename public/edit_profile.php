<?php
include __DIR__ . "/header.php"; 
require_once __DIR__ . "/../config/db.php";

// 确认登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取用户信息
$stmt = $_db->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// 获取默认地址
$addrStmt = $_db->prepare("SELECT * FROM address WHERE user_id = :user_id AND is_default = 1 LIMIT 1");
$addrStmt->execute([':user_id' => $user_id]);
$addr = $addrStmt->fetch(PDO::FETCH_ASSOC);

$address_line = $addr['address_line'] ?? '';
$city         = $addr['city'] ?? '';
$postcode     = $addr['postcode'] ?? '';
$full_name    = $addr['full_name'] ?? '';
$phone        = $addr['phone'] ?? '';

$states = ["Perlis", "Kedah", "Kelantan", "Terrengganu", "Pahang", "Johor", "Melaka", "Negeri Sembilan", "Putrajaya", "Selangor", "Perak", "Pulau Pinang", "Sarawak", "Sabah"];

// 密码验证弹窗提交处理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verify'])) {
    // 假设你有 getUserPasswordById() 返回对象 -> password
    $stored_hash_stmt = $_db->prepare("SELECT password FROM users WHERE id = :user_id");
    $stored_hash_stmt->execute([':user_id' => $user_id]);
    $stored_hash = $stored_hash_stmt->fetch(PDO::FETCH_OBJ);

    if ($stored_hash && password_verify($_POST['verify_password'], $stored_hash->password)) {
        temp("info","✅ Password Correct!");
        redirect("edit_profile.php");
    } else {
        temp("info","❌ Password Incorrect");
    }
}

// 更新用户信息
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $new_phone = trim($_POST['phone_num']);
    $full_name = trim($_POST['username']);
    $address_line = trim($_POST['address_line']);
    $city         = trim($_POST['city']);
    $postcode     = trim($_POST['postcode']);
    $password_raw = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // 密码更新
    if (!empty($password_raw)) {
        if ($password_raw !== $confirm_password) {
            temp("info","❌ Passwords do not match.");
            redirect();
        } else {
            $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
        }
    }

    // 头像上传
    $profile_image = $user['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = __DIR__ . "/../assets/image/uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_name  = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_path = $upload_dir . $file_name;
        $allowed_types = ['image/jpg', 'image/jpeg', 'image/png'];

        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG allowed.";
        } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $errors[] = "Failed to upload image.";
        } else {
            $profile_image = "/assets/image/uploads/" . $file_name;
        }
    }

    // 邮箱重复检查
    $emailCheck = $_db->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
    $emailCheck->execute([':email' => $email, ':user_id' => $user_id]);
    if ($emailCheck->fetch()) {
        temp("info","⚠️ Email already exists");
        redirect();
    }

    // 电话重复检查
    if ($new_phone !== $user['phone_num']) {
        $phoneCheck = $_db->prepare("SELECT id FROM users WHERE phone_num = :phone_num AND id != :user_id");
        $phoneCheck->execute([':phone_num' => $new_phone, ':user_id' => $user_id]);
        if ($phoneCheck->fetch()) {
            temp("info","⚠️ Phone number already exists");
            redirect();    
        }
    }

    // 更新用户表
    if (empty($errors)) {
        $sql = "UPDATE users SET username = :username, email = :email, profile_image = :profile_image";
        $params = [
            ':username' => $username,
            ':email'    => $email,
            ':profile_image' => $profile_image,
            ':user_id' => $user_id
        ];

        if ($new_phone !== $user['phone_num']) {
            $sql .= ", phone_num = :phone_num";
            $params[':phone_num'] = $new_phone;
        }

        if (!empty($password_raw)) {
            $sql .= ", password = :password";
            $params[':password'] = $password_hashed;
        }

        $sql .= " WHERE id = :user_id";
        $update = $_db->prepare($sql);
        $update->execute($params);

        // 更新地址表
        $checkAddr = $_db->prepare("SELECT * FROM address WHERE user_id = :user_id AND is_default = 1");
        $checkAddr->execute([':user_id' => $user_id]);
        if ($checkAddr->fetch()) {
            $updateAddr = $_db->prepare("UPDATE address SET full_name = :full_name, address_line = :address_line, city = :city, postcode = :postcode, phone = :phone WHERE user_id = :user_id AND is_default = 1");
            $updateAddr->execute([
                ':full_name'    => $full_name,
                ':address_line' => $address_line,
                ':city'         => $city,
                ':postcode'     => $postcode,
                ':phone'        => $phone,
                ':user_id'      => $user_id
            ]);
        } else {
            $insertAddr = $_db->prepare("INSERT INTO address (user_id, full_name, address_line, city, postcode, phone, is_default) VALUES (:user_id, :full_name, :address_line, :city, :postcode, :phone, 1)");
            $insertAddr->execute([
                ':user_id'      => $user_id,
                ':full_name'    => $full_name,
                ':address_line' => $address_line,
                ':city'         => $city,
                ':postcode'     => $postcode,
                ':phone'        => $phone
            ]);
        }

        temp("info", "✅ Profile Updated");
        redirect("profile.php");
    }
}
?>

<link rel="stylesheet" href="../assets/css/edit_profile.css">

<?php include __DIR__ . "/../component/backButton.php"; ?>

<body>
    <div class="edit-container">
        <h2>Edit User Profile</h2>
        <form method="POST" enctype="multipart/form-data">
            <?php include __DIR__ . "/../component/editUserProfileTemplate.php"; ?>
            <input type="hidden" name="update_profile" value="1">
            <div class="form-group">
                <input type="submit" value="Update Profile" class="btn">
            </div>
        </form>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/imagePreview.js"></script>

<?php include __DIR__ . "/footer.php"; ?>