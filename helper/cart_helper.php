<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cart_get() {
    return $_SESSION['cart'] ?? [];
}

function cart_set($cart) {
    $_SESSION['cart'] = $cart;
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

function cart_add($product_id, $name, $price, $image_src, $quantity = 1) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    if ($quantity < 1) {
        $quantity = 1;
    }

    $cart = cart_get();
    if (isset($cart[$product_id])) {
        $cart[$product_id]['quantity'] += $quantity;
    } else {
        $cart[$product_id] = [
            'name' => $name,
            'price' => (float)$price,
            'image' => $image_src,
            'quantity' => $quantity
        ];
    }
    cart_set($cart);
}

function cart_update_quantity($product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    $cart = cart_get();
    if (!isset($cart[$product_id])) {
        return;
    }
    if ($quantity <= 0) {
        unset($cart[$product_id]);
    } else {
        $cart[$product_id]['quantity'] = $quantity;
    }
    cart_set($cart);
}

function cart_total() {
    $total = 0.0;
    foreach (cart_get() as $item) {
        $total += ((float)$item['price']) * ((int)$item['quantity']);
    }
    return $total;
}

function cart_item_count() {
    $count = 0;
    foreach (cart_get() as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

function cart_app_root() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
    $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
    if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
        return '/' . $parts[0];
    }
    return '';
}

function product_image_src($raw) {
    $img_path = trim((string)$raw);
    $img_path = str_replace('\\', '/', $img_path);
    if ($img_path !== '' && (str_starts_with($img_path, 'http://') || str_starts_with($img_path, 'https://'))) {
        return $img_path;
    }
    $img_path = preg_replace('#/+#', '/', $img_path);
    $appRoot = cart_app_root();
    if ($appRoot !== '' && str_starts_with($img_path, $appRoot . '/')) {
        $img_path = substr($img_path, strlen($appRoot) + 1);
    }
    while (str_starts_with($img_path, '../')) {
        $img_path = substr($img_path, 3);
    }
    while (str_starts_with($img_path, './')) {
        $img_path = substr($img_path, 2);
    }
    $img_path = ltrim($img_path, '/');
    if ($img_path === '') {
        return ($appRoot !== '' ? $appRoot : '') . '/assets/image/logo/default.png';
    }
    if (strpos($img_path, 'assets/') === false) {
        $img_path = 'assets/image/product/' . basename($img_path);
    }
    return ($appRoot !== '' ? $appRoot : '') . '/' . $img_path;
}

?>
