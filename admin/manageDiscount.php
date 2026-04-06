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

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_code'])) {
    $codeId = (int)($_POST['code_id'] ?? 0);
    if ($codeId > 0) {
        $stmt = $_db->prepare("DELETE FROM discount_codes WHERE id = ?");
        $stmt->execute([$codeId]);
    }
    header("Location: manageDiscount.php");
    exit;
}

// Fetch codes from database
$stmt = $_db->query("SELECT * FROM discount_codes ORDER BY created_at DESC");
$discountCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../assets/css/manageOrders.css">

<div class="manage-orders-container">
    <div class="orders-toolbar">
        <h2 style="margin:0;">Manage Discount Codes</h2>
        <div class="actions">
            <a class="btn btn-secondary" href="manageUser.php">Back</a>
            <a href="adminAddDiscount.php" class="btn btn-success" style="text-decoration:none;">➕ Create New Code</a>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Discount (RM)</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($discountCodes)): ?>
                <tr><td colspan="5" style="text-align:center;">No discount codes found.</td></tr>
            <?php else: ?>
                <?php foreach ($discountCodes as $dc): ?>
                    <tr>
                        <td><?= htmlspecialchars($dc['id']) ?></td>
                        <td><b><?= htmlspecialchars($dc['code']) ?></b></td>
                        <td>RM <?= number_format((float)$dc['discount_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($dc['created_at']) ?></td>
                        <td>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this code?');">
                                <input type="hidden" name="code_id" value="<?= $dc['id'] ?>">
                                <button type="submit" name="delete_code" class="btn btn-delete" style="background:#dc3545; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.btn-delete:hover {
    background: #c82333 !important;
}
</style>
