<?php
require_once '../config/db.php';
require_once '../helper/html_helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$userEmail = '';
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $userEmail = $_SESSION['user']['email'] ?? '';
}
if ($userEmail === '') {
    redirect('/public/home.php');
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('/public/order_history.php');
}

$orderStmt = $_db->prepare("SELECT * FROM orders WHERE id = ? AND email = ? LIMIT 1");
$orderStmt->execute([$orderId, $userEmail]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    redirect('/public/order_history.php');
}

$itemsStmt = $_db->prepare("SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = ? ORDER BY id ASC");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">

<div style="max-width: 900px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top:0;">Order #<?= (int)$order['id'] ?></h2>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
        <div><b>Date:</b> <?= htmlspecialchars($order['order_date']) ?></div>
        <div><b>Status:</b> <?= htmlspecialchars($order['status']) ?></div>
        <div><b>Name:</b> <?= htmlspecialchars($order['customer_name']) ?></div>
        <div><b>Email:</b> <?= htmlspecialchars($order['email']) ?></div>
        <div><b>Phone:</b> <?= htmlspecialchars($order['phone']) ?></div>
        <div><b>Postcode:</b> <?= htmlspecialchars($order['postcode']) ?></div>
        <div style="grid-column: 1 / -1;"><b>Address:</b> <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?></div>
    </div>

    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background:#f8f9fa;">
                <th style="padding: 12px; text-align:left;">Item</th>
                <th style="padding: 12px; text-align:left;">Price</th>
                <th style="padding: 12px; text-align:left;">Qty</th>
                <th style="padding: 12px; text-align:left;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?= htmlspecialchars($it['product_name']) ?></td>
                    <td style="padding: 12px;">RM <?= number_format((float)$it['price'], 2) ?></td>
                    <td style="padding: 12px;"><?= (int)$it['quantity'] ?></td>
                    <td style="padding: 12px;">RM <?= number_format((float)$it['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; background:#f8f9fa; padding: 15px; border-radius: 8px;">
        <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Subtotal</span><span>RM <?= number_format((float)$order['subtotal'], 2) ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Tax</span><span>RM <?= number_format((float)$order['tax'], 2) ?></span></div>
        <div style="display:flex; justify-content:space-between; margin:6px 0; font-weight:700;"><span>Total</span><span>RM <?= number_format((float)$order['total_amount'], 2) ?></span></div>
    </div>

    <div style="margin-top: 18px;">
        <a href="order_history.php" style="text-decoration:none;">← Back to Orders</a>
    </div>
</div>

<?php include 'footer.php'; ?>

