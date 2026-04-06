<?php
require_once '../config/db.php';
require_once '../helper/html_helper.php';
require_once '../helper/cart_helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    if ($product_id > 0) {
        if ($action === 'update') {
            $quantity = (int)($_POST['quantity'] ?? 1);
            cart_update_quantity($product_id, $quantity);
        } elseif ($action === 'remove') {
            cart_update_quantity($product_id, 0);
        } elseif ($action === 'clear') {
            cart_clear();
        }
    } elseif ($action === 'clear') {
        cart_clear();
    }
    redirect('/public/cart.php');
}

$cart = cart_get();
$stockById = [];
if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stm = $_db->prepare("SELECT id, quantity FROM products WHERE id IN ($placeholders)");
    $stm->execute($ids);
    foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $stockById[(int)$row['id']] = (int)$row['quantity'];
    }
}

$subtotal = cart_total();
$grandTotal = $subtotal;

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">

<div style="max-width: 1100px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top: 0;">Your Shopping Cart</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty. <a href="product.php">Shop now</a></p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 12px; text-align:left;">Product</th>
                    <th style="padding: 12px; text-align:left;">Price</th>
                    <th style="padding: 12px; text-align:left;">Qty</th>
                    <th style="padding: 12px; text-align:left;">Total</th>
                    <th style="padding: 12px; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $pid => $item): ?>
                    <?php
                    $pid = (int)$pid;
                    $price = (float)$item['price'];
                    $qty = (int)$item['quantity'];
                    $line = $price * $qty;
                    $available = $stockById[$pid] ?? 0;
                    $img_src = product_image_src($item['image'] ?? '');
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="<?= htmlspecialchars($img_src) ?>" alt="product" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
                                <div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($item['name']) ?></div>
                                    <div style="color:#666; font-size: 12px;">Stock: <?= (int)$available ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">RM <?= number_format($price, 2) ?></td>
                        <td style="padding: 12px;">
                            <form method="post" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <input type="number" name="quantity" min="1" value="<?= $qty ?>" style="width: 70px; padding:8px;">
                                <button type="submit" style="padding: 8px 12px;">Update</button>
                            </form>
                        </td>
                        <td style="padding: 12px;">RM <?= number_format($line, 2) ?></td>
                        <td style="padding: 12px;">
                            <form method="post">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <button type="submit" style="padding: 8px 12px; background:#dc3545; color:white; border:none; border-radius:6px; cursor:pointer;">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; background:#f8f9fa; padding: 15px; border-radius: 8px;">
            <div style="display:flex; justify-content:space-between; margin:8px 0; font-weight:700;"><span>Total</span><span>RM <?= number_format($grandTotal, 2) ?></span></div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 20px;">
            <a href="product.php" style="text-decoration:none;">← Back to Products</a>
            <div style="display:flex; gap:10px;">
                <form method="post" onsubmit="return confirm('Clear cart?')">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" style="padding: 10px 14px;">Clear Cart</button>
                </form>
                <a href="checkout.php" style="padding: 10px 14px; background:#28a745; color:white; border-radius:8px; text-decoration:none;">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
