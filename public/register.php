<?php
include "header.php";
require_once "../config/db.php";

if (!isset($is_admin_form)) $is_admin_form = false;

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
    $role = $is_admin_form ? $_SESSION['role'] : 'user';
    $address_part = trim($_POST['address']);
    $postcode = trim($_POST['postcode']);
    $state = trim($_POST['state']);
    $full_address = "$address_part, $postcode, $state";

    // 上传头像
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
    if (empty($phone_num)) $errors[] = "Phone Number is required.";
    elseif (!preg_match('/^\d{10,11}$/', $phone_num)) $errors[] = "Phone number must be 10 or 11 digits.";
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

    // 插入用户
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

        // 跳转
        header("Location: ../public/login.php");
        exit;
    }
}
?>




<link rel="stylesheet" href="../assets/css/register.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<?php if ($is_admin_form): ?>
<?php include "../component/backButton.php"; ?>
<?php endif ?>
<body>
<div class="container" id="signup">

    <!-- Change the title based on admin form -->
    <h1 class="form-title"><?= $is_admin_form ? 'Create User' : 'Register' ?></h1>

    <form method="post" action="" enctype="multipart/form-data" id="registerForm">

        <div id="errorMessages" style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p style="margin-bottom: 8px;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
        </div>

        <div class="input-group1">
            <i class="fas fa-user"></i>
            <input type="text" name="username" id="username" placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="input-group2">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="input-group3">
            <i class="fas fa-home"></i>
            <input type="text" name="address" id="address" placeholder="Address" value="<?= htmlspecialchars($address_part) ?>" required>
        </div>

        <div class="input-group3">
            <input type="text" name="postcode" id="postcode" placeholder="Postcode" value="<?= htmlspecialchars($postcode) ?>" required maxlength="5">
        </div>

        <div class="input-group10">
            <i class="fas fa-map-marker-alt"></i>
            <select name="state" id="state" required>
                <option value="">Select State</option>
                <?php
                $states = ["Perlis", "Kedah", "Kelantan", "Terrengganu", "Pahang", "Johor", "Melaka", "Negeri Sembilan", "Putrajaya", "Selangor", "Perak", "Pulau Pinang", "Sarawak", "Sabah"];
                foreach ($states as $s) {
                    $selected = ($state === $s) ? "selected" : "";
                    echo "<option value=\"$s\" $selected>$s</option>";
                }
                ?>
            </select>
        </div>

        <div class="input-group4">
            <i class="fas fa-phone"></i>
            <input type="text" name="phone_num" id="phone_num" placeholder="Phone Number" value="<?= htmlspecialchars($phone_num) ?>" required maxlength="11">
        </div>

        <!-- Show the role field only if admin form -->
        <?php if ($is_admin_form): ?>
            <div class="input-group10">
                <i class="fas fa-user-shield"></i>
                <select name="role" id="role" required>
                    <option value="user" <?= ($role === 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
        <?php endif; ?>

        <div class="input-group5">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Password" required>
        </div>

        <div class="input-group5">
            <i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        </div>
        
        <br><br>

        <div class="form-group">
            <label class="upload">
                Click to Upload New Profile Image:
                <input type="file" name="profile_image" accept="image/*" hidden><br><br>
                <img src="/assets/image/logo/default.png" alt="Upload Your Image" width="150">
            </label>
        </div>
        <?php if (!$is_admin_form): ?>
            <div class="g-recaptcha" data-sitekey="6LfyhCMrAAAAAHH8E66ZICo6-5j-37JXAl_CZfd9"></div>
            <?php endif; ?>

        <!-- Change the button text based on admin form -->
        <input type="submit" value="<?= $is_admin_form ? 'Create User' : 'Sign Up' ?>" class="submit-btn">

        <!-- Hide "Already have an account?" if admin form -->
        <?php if (!$is_admin_form): ?>
            <div class="links">
                <p>Already have an account?</p>
            </div>
            <div class="signIn">
                <a href="../public/login.php" class="btn">Sign In</a>
            </div>
        <?php endif; ?>

    </form>

</div>

<script>
$(document).ready(function () {
    $("#registerForm").submit(function (event) {
        event.preventDefault();

        // Directly submit the form without any JS validation
        this.submit();
    });
});

</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/imagePreview.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</body>

<?php include "../public/footer.php"; ?>
