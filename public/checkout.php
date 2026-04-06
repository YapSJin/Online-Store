<?php
require_once '../config/db.php';
require_once '../helper/html_helper.php';
require_once '../helper/cart_helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$cart = cart_get();
if (empty($cart)) {
    redirect('/public/product.php');
}

$userId = (int)$_SESSION['user_id'];
$userEmail = '';
$userName = '';
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $userEmail = $_SESSION['user']['email'] ?? '';
    $userName = $_SESSION['user']['username'] ?? '';
}

$stmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$userId]);
$addr = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$discountCode = '';
$discountAmount = 0.0;
$discountError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_discount'])) {
    $discountCode = strtoupper(trim($_POST['discount_code'] ?? ''));
    if ($discountCode === '') {
        $discountError = 'Please enter a discount code.';
    } else {
        $codeStmt = $_db->prepare("SELECT discount_amount FROM discount_codes WHERE code = ?");
        $codeStmt->execute([$discountCode]);
        $codeRow = $codeStmt->fetch(PDO::FETCH_ASSOC);
        if ($codeRow) {
            $discountAmount = (float)$codeRow['discount_amount'];
        } else {
            $discountError = 'Invalid discount code.';
        }
    }
}

$subtotal = cart_total();
$tax = 0;
$grandTotal = max(0.00, $subtotal - $discountAmount);

$fullname = $_POST['fullname'] ?? $addr['full_name'] ?? $userName;
$email = $_POST['email'] ?? $userEmail;
$phone = $_POST['phone'] ?? $addr['phone'] ?? '';
$postcode = $_POST['postcode'] ?? $addr['postcode'] ?? '';
$address = $_POST['address'] ?? $addr['address_line'] ?? '';
$city = $_POST['city'] ?? $addr['city'] ?? '';
$state = $_POST['state'] ?? $addr['city'] ?? '';

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">
<?php $flash = temp('info'); ?>
<?php if ($flash !== null && $flash !== ''): ?>
    <script>alert("<?php echo addslashes((string)$flash); ?>");</script>
<?php endif; ?>

<div style="max-width: 900px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top:0;">Checkout</h2>

    <form action="checkout.php" method="post">
    <div style="background:#f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <div style="font-weight:700; margin-bottom: 10px;">Order Summary</div>
        <?php foreach ($cart as $item): ?>
            <?php $line = ((float)$item['price']) * ((int)$item['quantity']); ?>
            <div style="display:flex; justify-content:space-between; margin:6px 0;">
                <span><?= htmlspecialchars($item['name']) ?> x <?= (int)$item['quantity'] ?></span>
                <span>RM <?= number_format($line, 2) ?></span>
            </div>
        <?php endforeach; ?>
        <hr>
        <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Subtotal</span><span>RM <?= number_format($subtotal, 2) ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:6px 0; color:#dc3545;"><span>Discount</span><span>- RM <?= number_format($discountAmount, 2) ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:6px 0; font-weight:700;"><span>Total</span><span>RM <?= number_format($grandTotal, 2) ?></span></div>
        <hr>
        <div style="display:flex; gap:10px; margin-top:10px;">
            <input type="text" id="discount_code" name="discount_code" value="<?= htmlspecialchars($discountCode) ?>" placeholder="Enter discount code" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:6px;">
            <button type="submit" name="apply_discount" style="padding:10px 15px; background:#111; color:white; border:none; border-radius:6px; cursor:pointer;">Apply</button>
        </div>
        <?php if ($discountError !== ''): ?>
            <div style="font-size:12px; margin-top:8px; color:#c82333;"><?= htmlspecialchars($discountError) ?></div>
        <?php elseif ($discountAmount > 0): ?>
            <div style="font-size:12px; margin-top:8px; color:#28a745;">Code applied: RM <?= number_format($discountAmount, 2) ?> off</div>
        <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label style="display:block; margin-bottom:6px;">Full Name</label>
                <input type="text" name="fullname" required value="<?= htmlspecialchars($fullname) ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Phone</label>
                <input type="tel" name="phone" required value="<?= htmlspecialchars($phone) ?>" style="width:100%; padding:10px;" inputmode="tel" maxlength="13" placeholder="+60123456789">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Postcode</label>
                <input type="text" name="postcode" required value="<?= htmlspecialchars($postcode) ?>" style="width:100%; padding:10px;" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="Postcode must be exactly 5 digits">
            </div>
        </div>
        <div style="margin-top: 12px;">
            <label style="display:block; margin-bottom:6px;">Address</label>
            <textarea name="address" required rows="3" style="width:100%; padding:10px;"><?= htmlspecialchars($address) ?></textarea>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
            <div>
                <label style="display:block; margin-bottom:6px;">City/State</label>
                <input type="text" name="city" required value="<?= htmlspecialchars($city) ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">State</label>
                <input type="text" name="state" required value="<?= htmlspecialchars($state) ?>" style="width:100%; padding:10px;">
        <input type="hidden" name="discount_amount" value="<?= htmlspecialchars((string)$discountAmount) ?>">
        <input type="hidden" name="subtotal" value="<?= htmlspecialchars((string)$subtotal) ?>">
        <input type="hidden" name="grand_total" value="<?= htmlspecialchars((string)$grandTotal) ?>">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 18px;">
            <a href="cart.php" style="text-decoration:none;">← Back to Cart</a>
            <button type="submit" formaction="place_order.php" style="padding: 12px 18px; background:#28a745; color:white; border:none; border-radius:8px; cursor:pointer;">Place Order</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
