<?php
// 确保 session 已经开启
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

$faviconFile = realpath(__DIR__ . '/../assets/image/home/logo.png');
$faviconVersion = $faviconFile && file_exists($faviconFile) ? filemtime($faviconFile) : time();
$faviconHref = $appRoot . '/assets/image/home/logo.png?v=' . $faviconVersion;

// 引入 helper（注意你 helper 文件夹是 singular）和 model
require_once __DIR__ . '/../helper/html_helper.php';
require_once __DIR__ . '/../models/addressModels.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    redirect("/public/login.php");
}

$user_id = $_SESSION['user_id'];

function validate_postcode_5($postcode) {
    $p = trim((string)$postcode);
    return preg_match('/^\d{5}$/', $p) === 1;
}

function normalize_phone_basic($phone) {
    $p = trim((string)$phone);
    $p = str_replace([' ', '-', '(', ')'], '', $p);
    return $p;
}

function validate_phone_my($phone) {
    $p = normalize_phone_basic($phone);
    if (preg_match('/^\+60\d{9,10}$/', $p) === 1) return true;
    if (preg_match('/^60\d{9,10}$/', $p) === 1) return true;
    if (preg_match('/^0\d{9,10}$/', $p) === 1) return true;
    return false;
}

// 处理表单提交
if (is_post()) {
    if (isset($_POST['update_address'])) {
        $postcode = $_POST['postcode'] ?? '';
        $phone = $_POST['phone'] ?? '';
        if (!validate_postcode_5($postcode)) {
            temp("info", "Postcode must be exactly 5 digits.");
            redirect("/component/address.php");
        }
        if (!validate_phone_my($phone)) {
            temp("info", "Invalid phone. Example: +60123456789");
            redirect("/component/address.php");
        }
        updateAddress(
            $_POST['address_id'],
            $_POST['full_name'],
            $_POST['address_line'],
            $_POST['city'],
            $postcode,
            normalize_phone_basic($phone)
        );
        temp("info", "Address Updated ✅");
        redirect("/public/profile.php");
    }

    if (isset($_POST['add_address'])) {
        $postcode = $_POST['postcode'] ?? '';
        $phone = $_POST['phone'] ?? '';
        if (!validate_postcode_5($postcode)) {
            temp("info", "Postcode must be exactly 5 digits.");
            redirect("/component/address.php");
        }
        if (!validate_phone_my($phone)) {
            temp("info", "Invalid phone. Example: +60123456789");
            redirect("/component/address.php");
        }
        addAddress(
            $user_id,
            $_POST['full_name'],
            $_POST['address_line'],
            $_POST['city'],
            $postcode,
            normalize_phone_basic($phone)
        );
        temp("info", "Address Added ✅");
        redirect("/public/profile.php");
    }

    if (isset($_POST['delete_address'])) {
        deleteAddress($_POST['address_id']);
        temp("info", "Address Deleted ✅");
        redirect("/public/profile.php");
    }

    if (isset($_POST['set_default'])) {
        setDefaultAddress($user_id, $_POST['address_id']);
        temp("info", "Set Default Address Successful ✅");
        redirect("/public/profile.php");
    }
}

// 获取用户所有地址
$addresses = getAllUserAddressesById($user_id);

?>

<link rel="icon" type="image/png" href="<?php echo htmlspecialchars($faviconHref); ?>">
<link rel="stylesheet" href="../assets/css/address.css">
<?php $flash = temp('info'); ?>
<?php if ($flash !== null && $flash !== ''): ?>
    <script>alert("<?php echo addslashes((string)$flash); ?>");</script>
<?php endif; ?>

<div class="container">
    <h2>Address</h2>

    <div class="address-list">
        <?php foreach ($addresses as $address): ?>
            <div class="address-card <?= $address['is_default'] ? 'default-address' : '' ?>">
                <p><b><?= htmlspecialchars($address['full_name']) ?></b></p>
                <p><?= htmlspecialchars($address['address_line']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postcode']) ?></p>
                <p>☎️ <?= htmlspecialchars($address['phone']) ?></p>

                <form method="post" class="form-button">
                    <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                    <button type="submit" name="set_default" class="btn-default">Set Default</button>
                </form>

                <button type="button"
                        class="btn-edit"
                        data-address_id="<?= $address['id'] ?>"
                        data-full_name="<?= htmlspecialchars($address['full_name']) ?>"
                        data-address_line="<?= htmlspecialchars($address['address_line']) ?>"
                        data-city="<?= htmlspecialchars($address['city']) ?>"
                        data-postcode="<?= htmlspecialchars($address['postcode']) ?>"
                        data-phone="<?= htmlspecialchars($address['phone']) ?>">
                    Edit
                </button>

                <form method="post" class="form-button">
                    <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                    <button type="submit" name="delete_address" class="btn-delete" data-confirm="Are You Sure Want Delete Address?">Delete</button>
                </form>

            </div>
        <?php endforeach; ?>
    </div>

    <?php include 'addAddress.php'; ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/address.js"></script>
