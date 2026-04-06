<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

require "../config/db.php";
require "header.php";

$allowedStatuses = ['Pending', 'Processing', 'Delivered', 'Cancelled'];

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header("Location: manageOrders.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? 'Pending';
    if (in_array($newStatus, $allowedStatuses, true)) {
        $upd = $_db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $orderId]);
    }
    header("Location: orderDetail.php?id=" . (int)$orderId);
    exit;
}

$orderStmt = $_db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    header("Location: manageOrders.php");
    exit;
}

$itemsStmt = $_db->prepare("SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = ? ORDER BY id ASC");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$status = $order['status'] ?? 'Pending';
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'Pending';
}
?>

<link rel="stylesheet" href="../assets/css/manageOrders.css">

<div class="manage-orders-container">
    <div class="orders-toolbar">
        <h2 style="margin:0;">Order #<?= (int)$orderId ?></h2>
        <div class="actions">
            <a class="btn btn-secondary" href="manageOrders.php">Back</a>
        </div>
    </div>

    <div style="background:#f8f9fa; padding: 16px; border-radius: 10px; margin-bottom: 18px;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div><b>Date:</b> <?= htmlspecialchars($order['order_date'] ?? '') ?></div>
            <div><b>Status:</b> <span class="status-badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></span></div>
            <div><b>Customer:</b> <?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
            <div><b>Email:</b> <?= htmlspecialchars($order['email'] ?? '') ?></div>
            <div><b>Phone:</b> <?= htmlspecialchars($order['phone'] ?? '') ?></div>
            <div><b>Postcode:</b> <?= htmlspecialchars($order['postcode'] ?? '') ?></div>
            <div style="grid-column: 1 / -1;"><b>Address:</b> <?= htmlspecialchars($order['address'] ?? '') ?>, <?= htmlspecialchars($order['city'] ?? '') ?>, <?= htmlspecialchars($order['state'] ?? '') ?></div>
        </div>

        <form method="post" style="margin-top: 14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <select name="status" class="status-select">
                <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= ($s === $status) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="4" style="text-align:center;">No items found.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['product_name'] ?? '') ?></td>
                        <td>RM <?= number_format((float)($it['price'] ?? 0), 2) ?></td>
                        <td><?= (int)($it['quantity'] ?? 0) ?></td>
                        <td>RM <?= number_format((float)($it['subtotal'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 18px; background:#f8f9fa; padding: 16px; border-radius: 10px;">
        <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Subtotal</span><span>RM <?= number_format((float)($order['subtotal'] ?? 0), 2) ?></span></div>
        <?php if ((float)($order['tax'] ?? 0) > 0): ?>
            <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Tax</span><span>RM <?= number_format((float)($order['tax'] ?? 0), 2) ?></span></div>
        <?php endif; ?>
        <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
            <div style="display:flex; justify-content:space-between; margin:6px 0; color: #dc3545;"><span>Discount</span><span>- RM <?= number_format((float)($order['discount_amount'] ?? 0), 2) ?></span></div>
        <?php endif; ?>
        <div style="display:flex; justify-content:space-between; margin:6px 0; font-weight:800;"><span>Total</span><span>RM <?= number_format((float)($order['total_amount'] ?? 0), 2) ?></span></div>
    </div>
</div>
