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

$errors = [];
$code = '';
$amount = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $amount = trim($_POST['amount'] ?? '');

    if (empty($code)) {
        $errors[] = "Discount code is required.";
    } elseif (strlen($code) < 3) {
        $errors[] = "Discount code must be at least 3 characters.";
    }

    if ($amount === '') {
        $errors[] = "Discount amount is required.";
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $errors[] = "Discount amount must be a positive number.";
    }

    if (empty($errors)) {
        // Check if code already exists
        $stmt = $_db->prepare("SELECT id FROM discount_codes WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "This discount code already exists.";
        }
    }

    if (empty($errors)) {
        $stmt = $_db->prepare("INSERT INTO discount_codes (code, discount_amount) VALUES (?, ?)");
        $stmt->execute([$code, (float)$amount]);
        header("Location: manageDiscount.php");
        exit;
    }
}
?>

<link rel="stylesheet" href="../assets/css/manageOrders.css">

<div class="manage-orders-container" style="max-width: 600px; margin: 40px auto;">
    <div class="orders-toolbar">
        <h2 style="margin:0;">Create New Discount Code</h2>
        <div class="actions">
            <a class="btn btn-secondary" href="manageDiscount.php">Back</a>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <?php if (!empty($errors)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="margin:0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Discount Code</label>
                <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" placeholder="e.g. SAVE50" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; text-transform: uppercase;">
                <small style="color:#666; display:block; margin-top:5px;">Letters and numbers only, e.g. NEWUSER10</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600;">Discount Amount (RM)</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="<?= htmlspecialchars($amount) ?>" placeholder="0.00" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-success" style="width:100%; padding: 14px; font-size: 16px; font-weight:700;">Create Code</button>
            </div>
        </form>
    </div>
</div>
