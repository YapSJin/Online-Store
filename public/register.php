<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../config/db.php";

$is_admin_form = $is_admin_form ?? (($_SESSION['role'] ?? null) === 'admin');

$role = 'user';
$username = $email = $phone_num = $password = $confirm_password = '';
$address_part = $postcode = $state = '';
$profile_image = '/assets/image/logo/default.png';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone_num = trim($_POST['phone_num']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $is_admin_form ? $_POST['role'] ?? 'user' : 'user';
    $address_part = trim($_POST['address']);
    $postcode = trim($_POST['postcode']);
    $state = trim($_POST['state']);

    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../assets/image/uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_path = $upload_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $errors[] = "Invalid file type! Only JPG, JPEG, PNG, and WEBP are allowed.";
        }

        if (empty($errors) && move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $profile_image = "/assets/image/uploads/" . $file_name;
        } else if (empty($errors)) {
            $errors[] = "Failed to upload image.";
        }
    }

    // 表单验证
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    $phone_num_norm = str_replace([' ', '-', '(', ')'], '', $phone_num);
    if (empty($phone_num_norm)) $errors[] = "Phone Number is required.";
    elseif (!preg_match('/^\+60\d{9,10}$/', $phone_num_norm) && !preg_match('/^60\d{9,10}$/', $phone_num_norm) && !preg_match('/^0\d{9,10}$/', $phone_num_norm)) {
        $errors[] = "Invalid phone. Example: +60123456789";
    } else {
        $phone_num = $phone_num_norm;
    }
    if (empty($postcode)) $errors[] = "Postcode is required.";
    elseif (!preg_match('/^\d{5}$/', $postcode)) $errors[] = "Postcode must be exactly 5 digits.";
    if (empty($address_part)) $errors[] = "Address is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()]).{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    }
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // 检查邮箱/手机号重复
    if (empty($errors)) {
        $check = $_db->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->rowCount() > 0) $errors[] = "This email is already registered.";

        $checkPhone = $_db->prepare("SELECT id FROM users WHERE phone_num = :phone_num");
        $checkPhone->execute([':phone_num' => $phone_num]);
        if ($checkPhone->rowCount() > 0) $errors[] = "This phone number is already registered.";
    }

    // 插入用户和地址
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, phone_num, password, role, profile_image) 
                VALUES (:username, :email, :phone_num, :password, :role, :profile_image)";
        $stmt = $_db->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':phone_num' => $phone_num,
            ':password' => $hashed_password,
            ':role' => $role,
            ':profile_image' => $profile_image
        ]);

        $user_id = $_db->lastInsertId();

        $address_sql = "INSERT INTO address (user_id, full_name, address_line, city, postcode, phone, is_default) 
                        VALUES (:user_id, :full_name, :address_line, :city, :postcode, :phone, 1)";
        $address_stmt = $_db->prepare($address_sql);
        $address_stmt->execute([
            ':user_id' => $user_id,
            ':full_name' => $username,
            ':address_line' => $address_part,
            ':city' => $state,
            ':postcode' => $postcode,
            ':phone' => $phone_num
        ]);

        if ($is_admin_form) {
            header("Location: /admin/manageUser.php");
        } else {
            header("Location: login.php");
        }
        exit;
    }
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $is_admin_form ? 'Create User' : 'Register' ?> - LALA Clothing Store</title>
<link rel="stylesheet" href="../assets/css/register.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="register-page">

<?php include "header.php"; ?>

<div class="container">

    <h1 class="form-title"><?= $is_admin_form ? 'Create User' : 'Register' ?></h1>

    <form method="post" action="" enctype="multipart/form-data">

        <div id="errorMessages">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>

        <div class="input-group"><i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="input-group"><i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="input-group"><i class="fas fa-map-marker-alt"></i>
            <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($address_part) ?>" required>
        </div>

        <div class="input-group"><i class="fas fa-map-pin"></i>
            <input type="text" name="postcode" placeholder="Postcode" value="<?= htmlspecialchars($postcode) ?>" maxlength="5" required inputmode="numeric" pattern="[0-9]{5}" title="Postcode must be exactly 5 digits">
        </div>

        <div class="input-group"><i class="fas fa-flag"></i>
            <select name="state" required>
                <option value="">Select State</option>
                <?php
                $states = ["Perlis","Kedah","Kelantan","Terrengganu","Pahang","Johor","Melaka","Negeri Sembilan","Putrajaya","Selangor","Perak","Pulau Pinang","Sarawak","Sabah"];
                foreach ($states as $s) {
                    $selected = ($state === $s) ? "selected" : "";
                    echo "<option value=\"$s\" $selected>$s</option>";
                }
                ?>
            </select>
        </div>

        <div class="input-group"><i class="fas fa-phone"></i>
            <input type="tel" name="phone_num" placeholder="Phone Number" value="<?= htmlspecialchars($phone_num) ?>" maxlength="13" required inputmode="tel" placeholder="+60123456789">
        </div>

        <div class="input-group">
            <?php if ($is_admin_form): ?>
                <select name="role" required>
                    <option value="user" <?= ($role==='user')?'selected':''?>>User</option>
                    <option value="admin" <?= ($role==='admin')?'selected':''?>>Admin</option>
                </select>
            <?php else: ?>
                <select name="role" disabled>
                    <option value="user" selected>User</option>
                </select>
                <input type="hidden" name="role" value="user">
            <?php endif; ?>
        </div>

        <div class="input-group"><i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-group"><i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <label class="upload">
            Click to Upload New Profile Image:
            <input type="file" name="profile_image" accept="image/*" hidden id="profile_image_input">
            <img src="<?= htmlspecialchars($appRoot . '/assets/image/logo/default.png') ?>" alt="Upload Your Image" width="150" id="profile_image_preview">
        </label>

        <?php if (!$is_admin_form): ?>
            <div class="links"><a href="login.php">Already have an account?</a></div>
        <?php endif; ?>

        <input type="submit" value="<?= $is_admin_form ? 'Create User' : 'Sign Up' ?>" class="submit-btn">
    </form>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('profile_image_input');
    var preview = document.getElementById('profile_image_preview');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        if (!file.type || !file.type.startsWith('image/')) return;
        var url = URL.createObjectURL(file);
        preview.src = url;
    });
});
</script>
