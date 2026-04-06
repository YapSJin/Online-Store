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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/checkout.php');
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$postcode = trim($_POST['postcode'] ?? '');
$discountCode = strtoupper(trim($_POST['discount_code'] ?? ''));
$discountAmount = 0.0;

if ($fullname === '' || $email === '' || $phone === '' || $address === '' || $city === '' || $state === '' || $postcode === '') {
    redirect('/public/checkout.php');
}

$phoneNorm = str_replace([' ', '-', '(', ')'], '', $phone);
if (!preg_match('/^\d{5}$/', $postcode)) {
    temp('info', 'Postcode must be exactly 5 digits.');
    redirect('/public/checkout.php');
}
if (!preg_match('/^\+60\d{9,10}$/', $phoneNorm) && !preg_match('/^60\d{9,10}$/', $phoneNorm) && !preg_match('/^0\d{9,10}$/', $phoneNorm)) {
    temp('info', 'Invalid phone. Example: +60123456789');
    redirect('/public/checkout.php');
}

if ($discountCode !== '') {
    $codeStmt = $_db->prepare("SELECT discount_amount FROM discount_codes WHERE code = ?");
    $codeStmt->execute([$discountCode]);
    $codeRow = $codeStmt->fetch(PDO::FETCH_ASSOC);
    if ($codeRow) {
        $discountAmount = (float)$codeRow['discount_amount'];
    }
}

$subtotal = cart_total();
$tax = 0;
$grandTotal = max(0.00, $subtotal - $discountAmount);

try {
    $_db->beginTransaction();

    $hasDiscountColumn = false;
    try {
        $columnStmt = $_db->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'");
        $hasDiscountColumn = (bool)$columnStmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $hasDiscountColumn = false;
    }

    if ($hasDiscountColumn) {
        $orderStmt = $_db->prepare("INSERT INTO orders (email, customer_name, phone, address, city, state, postcode, subtotal, tax, discount_amount, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $orderStmt->execute([$email, $fullname, $phoneNorm, $address, $city, $state, $postcode, $subtotal, $tax, $discountAmount, $grandTotal]);
    } else {
        $orderStmt = $_db->prepare("INSERT INTO orders (email, customer_name, phone, address, city, state, postcode, subtotal, tax, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $orderStmt->execute([$email, $fullname, $phoneNorm, $address, $city, $state, $postcode, $subtotal, $tax, $grandTotal]);
    }
    $orderId = (int)$_db->lastInsertId();

    $stockStmt = $_db->prepare("SELECT id, productname, price, quantity FROM products WHERE id = ?");
    $updateStockStmt = $_db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
    $itemStmt = $_db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($cart as $pid => $item) {
        $pid = (int)$pid;
        $qty = (int)$item['quantity'];
        if ($qty < 1) {
            continue;
        }

        $stockStmt->execute([$pid]);
        $p = $stockStmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            throw new Exception('Product not found.');
        }

        $unitPrice = (float)$p['price'];
        $lineSubtotal = $unitPrice * $qty;

        $updateStockStmt->execute([$qty, $pid, $qty]);
        if ($updateStockStmt->rowCount() !== 1) {
            throw new Exception('Insufficient stock.');
        }

        $itemStmt->execute([$orderId, $pid, $p['productname'], $unitPrice, $qty, $lineSubtotal]);
    }

    $_db->commit();
    cart_clear();
    redirect('/public/order_confirmation.php?order_id=' . $orderId);
} catch (Throwable $e) {
    if ($_db->inTransaction()) {
        $_db->rollBack();
    }
    redirect('/public/checkout.php');
}
