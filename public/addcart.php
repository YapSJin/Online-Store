<?php
require_once '../config/db.php';
require_once '../helper/html_helper.php';
require_once '../helper/cart_helper.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/public/login.php');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('/public/product.php');
}

$stmt = $_db->prepare("SELECT id, productname, price, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    cart_add(
        (int)$product['id'],
        (string)$product['productname'],
        (float)$product['price'],
        product_image_src($product['image'] ?? ''),
        1
    );
}

redirect('/public/product.php');
