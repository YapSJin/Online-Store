<?php
require_once '../config/db.php';
$stmt = $_db->query("SELECT id, productname, image FROM products");
$products = $stmt->fetchAll();
header('Content-Type: application/json');
echo json_encode($products, JSON_PRETTY_PRINT);
?>