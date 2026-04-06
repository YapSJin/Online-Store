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
require "../helper/html_helper.php";

$statusFilter = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$allowedStatuses = ['Pending', 'Processing', 'Delivered', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? 'Pending';

    if ($orderId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $upd = $_db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $orderId]);
    }

    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $target = 'manageOrders.php' . ($qs !== '' ? ('?' . $qs) : '');
    header("Location: " . $target);
    exit;
}

$where = [];
$params = [];

if ($statusFilter !== 'all' && in_array($statusFilter, $allowedStatuses, true)) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if ($search !== '') {
    $where[] = "(email LIKE ? OR customer_name LIKE ? OR CAST(id AS CHAR) LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$stmt = $_db->prepare("SELECT id, email, order_date, customer_name, total_amount, status FROM orders {$whereSql} ORDER BY order_date DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/manageOrders.css">

<div class="manage-orders-container">
    <h2>Manage Orders</h2>

    <div class="orders-toolbar">
        <div class="filter-group">
            <?php
                $filters = ['all' => 'ALL', 'Pending' => 'Pending', 'Processing' => 'Processing', 'Delivered' => 'Delivered', 'Cancelled' => 'Cancelled'];
                foreach ($filters as $key => $label) {
                    $active = ($statusFilter === $key) ? 'active' : '';
                    $href = 'manageOrders.php?status=' . urlencode($key) . '&search=' . urlencode($search);
                    echo '<a class="' . $active . '" href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a>';
                }
            ?>
        </div>

        <form method="GET" class="search-form">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <input type="text" name="search" class="search-input" placeholder="Search order id / email / name" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn">Search</button>
            <a href="manageUser.php" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Order ID</th>
                <th>Email</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Update</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr><td colspan="9" style="text-align:center;">No orders found.</td></tr>
        <?php else: ?>
            <?php $no = 1; ?>
            <?php foreach ($orders as $o): ?>
                <?php
                    $status = $o['status'] ?? 'Pending';
                    if (!in_array($status, $allowedStatuses, true)) {
                        $status = 'Pending';
                    }
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars(str_pad((string)((int)$o['id']), 3, '0', STR_PAD_LEFT)) ?></td>
                    <td><?= htmlspecialchars($o['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($o['customer_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($o['order_date'] ?? '') ?></td>
                    <td>RM <?= number_format((float)($o['total_amount'] ?? 0), 2) ?></td>
                    <td>
                        <span class="status-badge status-<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" class="actions">
                            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                            <select name="status" class="status-select">
                                <?php foreach ($allowedStatuses as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>" <?= ($s === $status) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-success">Save</button>
                        </form>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="btn" href="orderDetail.php?id=<?= (int)$o['id'] ?>">View</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
