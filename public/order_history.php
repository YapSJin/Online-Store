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

$stmt = $_db->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE email = ? ORDER BY order_date DESC");
$stmt->execute([$userEmail]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<link rel="stylesheet" href="../assets/css/home.css">

<div style="max-width: 1100px; margin: 40px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
    <h2 style="margin-top:0;">My Orders</h2>

    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background:#f8f9fa;">
                    <th style="padding: 12px; text-align:left;">Order ID</th>
                    <th style="padding: 12px; text-align:left;">Date</th>
                    <th style="padding: 12px; text-align:left;">Total</th>
                    <th style="padding: 12px; text-align:left;">Status</th>
                    <th style="padding: 12px; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?= (int)$o['id'] ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($o['order_date']) ?></td>
                        <td style="padding: 12px;">RM <?= number_format((float)$o['total_amount'], 2) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($o['status']) ?></td>
                        <td style="padding: 12px;"><a href="order_detail.php?id=<?= (int)$o['id'] ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

