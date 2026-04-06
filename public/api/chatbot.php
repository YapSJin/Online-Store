<?php
require_once '../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$message = trim((string)($payload['message'] ?? ''));
if ($message === '') {
    echo json_encode(['ok' => false, 'reply' => 'Please type a message.']);
    exit;
}

$text = mb_strtolower($message);

function app_root_from_script() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
    $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
    if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
        return '/' . $parts[0];
    }
    return '';
}

function reply($text) {
    echo json_encode(['ok' => true, 'reply' => $text], JSON_UNESCAPED_UNICODE);
    exit;
}

$appRoot = app_root_from_script();
$publicBase = $appRoot . '/public';

if (preg_match('/\b(help|what can you do|menu|commands)\b/i', $message)) {
    reply(
        "I can help with:\n" .
        "- Product details (price, stock)\n" .
        "- Search products by name/category\n" .
        "- Order pages info\n\n" .
        "Try:\n" .
        "- \"Show hoodies\"\n" .
        "- \"Price of Ace Graphic Shirt\"\n" .
        "- \"Product id 3\"\n" .
        "- \"How to checkout\""
    );
}

if (preg_match('/\b(checkout|order|orders|my orders|payment)\b/i', $message)) {
    reply(
        "Checkout flow:\n" .
        "1) Go to Products: {$publicBase}/product.php\n" .
        "2) Add to cart\n" .
        "3) Open cart: {$publicBase}/cart.php\n" .
        "4) Proceed to checkout\n\n" .
        "You can view your orders here: {$publicBase}/order_history.php"
    );
}

if (preg_match('/\b(shipping|delivery|deliver|courier)\b/i', $message)) {
    reply(
        "Delivery info:\n" .
        "- Fast delivery is available.\n" .
        "- You can check stock on each product.\n\n" .
        "Browse products: {$publicBase}/product.php"
    );
}

if (preg_match('/\b(return|refund|exchange)\b/i', $message)) {
    reply(
        "Return/Refund:\n" .
        "Please contact us with your order id and email.\n\n" .
        "Contact: {$publicBase}/home.php#contact"
    );
}

if (preg_match('/\b(contact|location|address|map)\b/i', $message)) {
    reply("Contact section: {$publicBase}/home.php#contact\nMap: {$publicBase}/maps.php");
}

if (preg_match('/\b(admin|admin login)\b/i', $message)) {
    reply("Admin login: {$publicBase}/admin_login.php");
}

if (preg_match('/\b(products|product|items|catalog)\b/i', $message)) {
    $category = null;
    if (preg_match('/\b(hoodie|hoodies)\b/i', $message)) $category = 'hoodie';
    if (preg_match('/\b(pants|jeans)\b/i', $message)) $category = 'pants';
    if (preg_match('/\b(shirt|shirts|clothes)\b/i', $message)) $category = 'clothes';

    $id = null;
    if (preg_match('/\b(id|product)\s*#?\s*(\d{1,6})\b/i', $message, $m)) {
        $id = (int)$m[2];
    } elseif (preg_match('/#\s*(\d{1,6})\b/', $message, $m)) {
        $id = (int)$m[1];
    }

    if ($id) {
        $stmt = $_db->prepare("SELECT id, productname, description, price, quantity, category FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            reply("I can't find product id {$id}. Try: {$publicBase}/product.php");
        }
        $stock = (int)($p['quantity'] ?? 0);
        $stockText = ($stock <= 0) ? 'Out of stock' : ("Stock: {$stock}");
        reply(
            "Product #{$p['id']}: {$p['productname']}\n" .
            "Category: {$p['category']}\n" .
            "Price: RM " . number_format((float)$p['price'], 2) . "\n" .
            "{$stockText}\n\n" .
            "Open products page: {$publicBase}/product.php"
        );
    }

    if ($category) {
        $stmt = $_db->prepare("SELECT id, productname, price, quantity FROM products WHERE category = ? ORDER BY id DESC LIMIT 6");
        $stmt->execute([$category]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            reply("No products found in {$category}. Try: {$publicBase}/product.php?category={$category}");
        }
        $lines = [];
        foreach ($rows as $r) {
            $lines[] = "#" . (int)$r['id'] . " " . $r['productname'] . " — RM " . number_format((float)$r['price'], 2) . " (stock " . (int)$r['quantity'] . ")";
        }
        reply("Top {$category}:\n" . implode("\n", $lines) . "\n\nOpen: {$publicBase}/product.php?category={$category}");
    }

    $kw = $text;
    $kw = preg_replace('/[^a-z0-9\s]+/i', ' ', $kw);
    $kw = preg_replace('/\b(show|find|search|price|prices|stock|availability|product|products|item|items|of|for|the|a|an|please)\b/i', ' ', $kw);
    $kw = trim(preg_replace('/\s+/', ' ', $kw));

    if ($kw !== '') {
        $stmt = $_db->prepare("SELECT id, productname, price, quantity FROM products WHERE productname LIKE ? ORDER BY id DESC LIMIT 6");
        $stmt->execute(['%' . $kw . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $lines = [];
            foreach ($rows as $r) {
                $lines[] = "#" . (int)$r['id'] . " " . $r['productname'] . " — RM " . number_format((float)$r['price'], 2) . " (stock " . (int)$r['quantity'] . ")";
            }
            reply("Results for \"{$kw}\":\n" . implode("\n", $lines) . "\n\nOpen: {$publicBase}/product.php");
        }
    }

    $stmt = $_db->query("SELECT id, productname, price FROM products ORDER BY id DESC LIMIT 6");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $lines = [];
    foreach ($rows as $r) {
        $lines[] = "#" . (int)$r['id'] . " " . $r['productname'] . " — RM " . number_format((float)$r['price'], 2);
    }
    reply("Latest products:\n" . implode("\n", $lines) . "\n\nOpen: {$publicBase}/product.php");
}

reply(
    "I can help you find products, prices, and stock.\n" .
    "Try: \"Show hoodies\" or \"Product id 3\".\n\n" .
    "Products: {$publicBase}/product.php"
);
