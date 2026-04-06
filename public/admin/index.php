<?php
require_once '../../config/db.php';

$sessionRole = $_SESSION['role'] ?? null;
$sessionUser = $_SESSION['user'] ?? null;
if ($sessionRole !== 'admin') {
    if (is_array($sessionUser) && (($sessionUser['role'] ?? null) === 'admin')) {
        $sessionRole = 'admin';
    } elseif (is_object($sessionUser) && (($sessionUser->role ?? null) === 'admin')) {
        $sessionRole = 'admin';
    }
}

if ($sessionRole !== 'admin') {
    header("Location: ../admin_login.php");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$params = [];

if ($search != '') {
    $sql = "SELECT * FROM products WHERE productname LIKE ?";
    $params[] = "%$search%";
} else {
    $sql = "SELECT * FROM products";
}

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

function admin_app_root() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
    $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
    if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
        return '/' . $parts[0];
    }
    return '';
}

function normalize_product_img_src($raw) {
    $appRoot = admin_app_root();
    $img_path = trim((string)$raw);
    $img_path = str_replace('\\', '/', $img_path);
    if ($img_path !== '' && (str_starts_with($img_path, 'http://') || str_starts_with($img_path, 'https://'))) {
        return $img_path;
    }
    $img_path = preg_replace('#/+#', '/', $img_path);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Product List</title>
    <link rel="icon" type="image/png" href="../../assets/image/home/logo.png?v=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 30px;
            margin: 0;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .button {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            transition: 0.2s;
            display: inline-block;
        }

        .add {
            background: #28a745;
        }

        .add:hover {
            background: #218838;
        }

        .back {
            background: #6c757d;
        }

        .back:hover {
            background: #5a6268;
        }

        .search-form {
            display: flex;
            gap: 8px;
            flex: 1;
        }

        .search-form input[type="text"] {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            flex: 1;
        }

        .search-form button {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            background: #007bff;
            color: white;
            cursor: pointer;
            transition: 0.2s;
        }

        .search-form button:hover {
            background: #0056b3;
        }

        .clear-search {
            margin-left: 5px;
            text-decoration: none;
            color: #555;
            font-weight: bold;
            line-height: 32px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #111;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #e8f0fe;
        }

        img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .edit {
            background: #ff9800;
        }

        .edit:hover {
            background: #e68900;
        }

        .delete {
            background: #f44336;
        }

        .delete:hover {
            background: #d32f2f;
        }

        .low-stock {
            color: red;
            font-weight: bold;
        }

        .out-stock {
            color: gray;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Admin - Product List</h2>
    <?php $back_admin_href = (admin_app_root() !== '' ? admin_app_root() : '') . '/admin/manageUser.php'; ?>
    <a href="<?php echo htmlspecialchars($back_admin_href); ?>" class="button back">← Back to Admin</a>
</div>

<div class="toolbar">
    <a href="add_product.php" class="button add">+ Add Product</a>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search product..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">🔍 Search</button>
        <?php if ($search != ''): ?>
            <a href="index.php" class="clear-search">❌ Clear</a>
        <?php endif; ?>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $row): ?>
            <?php 
                $img_src = normalize_product_img_src($row->image ?? '');
            ?>
            <tr>
                <td><?php echo $row->id; ?></td>
                <td><img src="<?php echo htmlspecialchars($img_src); ?>" alt="product image"></td>
                <td><?php echo htmlspecialchars($row->productname); ?></td>
                <td><?php echo number_format($row->price, 2); ?></td>
                <td>
                    <?php
                    if ($row->quantity == 0) {
                        echo '<span class="out-stock">❌ Out of Stock</span>';
                    } elseif ($row->quantity <= 10) {
                        echo '<span class="low-stock">⚠️ Low Stock (' . $row->quantity . ')</span>';
                    } else {
                        echo $row->quantity;
                    }
                    ?>
                </td>
                <td>
                    <a href="edit_product.php?id=<?php echo $row->id; ?>" class="button edit">✏ Edit</a>
                    <a href="delete_product.php?id=<?php echo $row->id; ?>" 
                       class="button delete"
                       onclick="return confirm('Are you sure you want to delete this product?')">
                       🗑 Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No products found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
