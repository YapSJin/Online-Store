<?php
require_once '../config/db.php';
require_once '../helper/html_helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('/public/home.php');
}

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">

<div style="max-width: 900px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top:0;">Order Confirmation</h2>
    <p>Your order has been placed successfully.</p>
    <p><b>Order ID:</b> <?= (int)$orderId ?></p>
    <div style="margin-top: 20px; display:flex; gap: 10px;">
        <a href="order_detail.php?id=<?= (int)$orderId ?>" style="padding: 10px 14px; background:#007bff; color:white; border-radius:8px; text-decoration:none;">View Order</a>
        <a href="product.php" style="padding: 10px 14px; background:#28a745; color:white; border-radius:8px; text-decoration:none;">Continue Shopping</a>
    </div>
</div>

<?php include 'footer.php'; ?>

