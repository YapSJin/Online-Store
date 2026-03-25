<?php
require 'header.php';
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = $_POST['email_or_phone'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $_db->prepare("SELECT id, username, email, phone_num, password, role, profile_image FROM users WHERE email = ? OR phone_num = ?");
    $stmt->execute([$email_or_phone, $email_or_phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user->password)) {
        $_SESSION['user'] = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'profile_image' => $user->profile_image
        ];
        $_SESSION['role'] = $user->role;
        $_SESSION['user_id'] = $user->id;
        header("Location: home.php");
        exit;
    } else {
        $message = "Invalid email/phone or password.";
    }
}
?>

    <link rel="stylesheet" href="../assets/css/login.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>
<body>
    <div class="container">
        <div class="rowcenter">
        <form id="loginForm" action="login.php" method="POST">
            <h1 class="form-title">Login</h1>

            <i class="fas fa-user"></i>
            <input type="text" id="email_or_phone" name="email_or_phone" class="form-control" placeholder="Enter Email or Phone Number" required><br>

            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter Password" required><br>

            <p class="Password"><a href="../public/forgot_password.php">Forgot Your Password?</a></p>

            <div class="links">
                <a href="../public/register.php">Don't have an account yet?</a>
            </div>

            <input type="submit" value="Login" class="submit-btn">
    </form>

        </div>
    </div>

<script>
$(document).ready(function() {
    $("#loginForm").submit(function(event) {
        event.preventDefault(); 

        let errors = [];
        const email_or_phone = $.trim($("#email_or_phone").val());
        const password = $.trim($("#password").val());

        if (email_or_phone === "") {
            errors.push("Please enter your Email or Phone number!");
        }
        if (password === "") {
            errors.push("Please enter your password!");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
        } else {
            this.submit();
        }
    });

    <?php if (!empty($message)) { ?>
        alert("<?= addslashes($message); ?>");
    <?php } ?>
});

</script>

</body>
</html>
<?php include "../public/footer.php"; ?>
