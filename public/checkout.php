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

$subtotal = cart_total();
$tax = $subtotal * 0.06;
$grandTotal = $subtotal + $tax;

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">
<?php $flash = temp('info'); ?>
<?php if ($flash !== null && $flash !== ''): ?>
    <script>alert("<?php echo addslashes((string)$flash); ?>");</script>
<?php endif; ?>

<div style="max-width: 900px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top:0;">Checkout</h2>

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
        <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Tax (6%)</span><span>RM <?= number_format($tax, 2) ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:6px 0; font-weight:700;"><span>Total</span><span>RM <?= number_format($grandTotal, 2) ?></span></div>
    </div>

    <form action="place_order.php" method="post">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label style="display:block; margin-bottom:6px;">Full Name</label>
                <input type="text" name="fullname" required value="<?= htmlspecialchars($addr['full_name'] ?? $userName) ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($userEmail) ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Phone</label>
                <input type="tel" name="phone" required value="<?= htmlspecialchars($addr['phone'] ?? '') ?>" style="width:100%; padding:10px;" inputmode="tel" maxlength="13" placeholder="+60123456789">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Postcode</label>
                <input type="text" name="postcode" required value="<?= htmlspecialchars($addr['postcode'] ?? '') ?>" style="width:100%; padding:10px;" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" title="Postcode must be exactly 5 digits">
            </div>
        </div>
        <div style="margin-top: 12px;">
            <label style="display:block; margin-bottom:6px;">Address</label>
            <textarea name="address" required rows="3" style="width:100%; padding:10px;"><?= htmlspecialchars($addr['address_line'] ?? '') ?></textarea>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
            <div>
                <label style="display:block; margin-bottom:6px;">City/State</label>
                <input type="text" name="city" required value="<?= htmlspecialchars($addr['city'] ?? '') ?>" style="width:100%; padding:10px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">State</label>
                <input type="text" name="state" required value="<?= htmlspecialchars($addr['city'] ?? '') ?>" style="width:100%; padding:10px;">
            </div>
        </div>

        <input type="hidden" name="subtotal" value="<?= htmlspecialchars((string)$subtotal) ?>">
        <input type="hidden" name="tax" value="<?= htmlspecialchars((string)$tax) ?>">
        <input type="hidden" name="grand_total" value="<?= htmlspecialchars((string)$grandTotal) ?>">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 18px;">
            <a href="cart.php" style="text-decoration:none;">← Back to Cart</a>
            <button type="submit" style="padding: 12px 18px; background:#28a745; color:white; border:none; border-radius:8px; cursor:pointer;">Place Order</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
