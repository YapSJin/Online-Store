<?php
require_once '../config/db.php';

$sql = "SELECT * FROM products WHERE 1";
$params = [];

// SEARCH
$search = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql .= " AND productname LIKE ?";
    $params[] = "%$search%";
}

// CATEGORY
$category = "";
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = $_GET['category'];
    $sql .= " AND category = ?";
    $params[] = $category;
}

// PRICE 
$price = "";
if (isset($_GET['price']) && $_GET['price'] != "") {
    $price = $_GET['price'];
    if ($price == "250") {
        $sql .= " AND price < 250";
    } elseif ($price == "500") {
        $sql .= " AND price BETWEEN 250 AND 500";
    } elseif ($price == "501") {
        $sql .= " AND price > 500";
    }
}

// PAGINATION
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// TOTAL PRODUCTS (Using PDO)
$stmt_total = $_db->prepare($sql);
$stmt_total->execute($params);
$total_products = $stmt_total->rowCount();
$total_pages = ceil($total_products / $limit);

// ADD LIMIT
$sql .= " LIMIT $start, $limit";
$stmt = $_db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LALA Clothing Store - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fafafa;
        }

        .hero {
            text-align: center;
            padding: 50px;
            background: #f2f2f2;
        }

        .hero h2 {
            font-size: 36px;
        }

        .filter-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 18px;
            background: white;
            padding: 15px 18px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .search-group {
            display: flex;
            flex: 1;
        }

        .search-group input {
            flex: 1;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }

        .search-group button {
            padding: 12px 16px;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }

        .search-group button:hover {
            background: #218838;
        }

        .filter-bar select {
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .filter-bar select:hover {
            border-color: #28a745;
        }

        .products {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            padding: 40px 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        .card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
            transition: 0.3s;
        }

        .card:hover img {
            transform: scale(1.05);
        }

        .tag {
            display: inline-block;
            background: #eee;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 6px;
        }

        .price {
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
            color: #28a745;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 12px;
        }

        .cart-btn {
            background: #007bff;
            padding: 10px 14px;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .cart-btn:hover {
            background: #0069d9;
        }

        .buy-btn {
            background: #28a745;
            padding: 10px 14px;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .buy-btn:hover {
            background: #218838;
        }

        .pagination {
            text-align: center;
            margin: 40px 0;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            margin: 5px;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            background: white;
            border: 1px solid #ddd;
            color: #333;
            font-weight: bold;
        }

        .pagination a:hover {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .pagination .active {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .no-product {
            text-align: center;
            padding: 60px;
            font-size: 18px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="hero">
    <h2>Our Products</h2>
    <p>Find your favourite fashion items</p>
</section>

<div class="filter-container">
    <form method="GET" action="product.php" id="filterForm">
        <div class="filter-bar">
            <div class="search-group">
                <input type="text" name="search" placeholder="Search product..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </div>

            <select name="category" onchange="document.getElementById('filterForm').submit();">
                <option value="">All Category</option>
                <option value="clothes" <?php if($category=="clothes") echo "selected"; ?>>Clothes</option>
                <option value="pants" <?php if($category=="pants") echo "selected"; ?>>Pants</option>
                <option value="hoodie" <?php if($category=="hoodie") echo "selected"; ?>>Hoodie</option>
            </select>

            <select name="price" onchange="document.getElementById('filterForm').submit();">
                <option value="">All Prices</option>
                <option value="250" <?php if($price=="250") echo "selected"; ?>>Under RM250</option>
                <option value="500" <?php if($price=="500") echo "selected"; ?>>RM250 - RM500</option>
                <option value="501" <?php if($price=="501") echo "selected"; ?>>Above RM500</option>
            </select>
        </div>
    </form>
</div>

<section class="products">
    <?php if(count($products) > 0): ?>
        <?php foreach($products as $row): ?>
            <?php 
                // 兼容处理路径：清理首尾空格，并根据页面位置拼接路径
                $img_path = trim($row->image);
                
                // 1. 去掉首部的任何相对路径标识，统一为相对项目根目录的路径
                $img_path = ltrim($img_path, './\\');
                
                // 2. 检查路径是否包含 assets/image/product/，如果不包含则尝试补全
                if (strpos($img_path, 'assets/') === false) {
                    $img_path = 'assets/image/product/' . basename($img_path);
                }
                
                // 3. 最终拼接为相对于当前 public 目录的路径
                $final_img_src = '../' . $img_path;
            ?>
            <div class="card" data-chatbot-product-id="<?php echo (int)$row->id; ?>" data-chatbot-product-name="<?php echo htmlspecialchars($row->productname, ENT_QUOTES); ?>">
                <img src="<?php echo htmlspecialchars($final_img_src); ?>" alt="product image">
                <h3><?php echo htmlspecialchars($row->productname); ?></h3>
                <span class="tag"><?php echo htmlspecialchars($row->category); ?></span>
                <p><?php echo htmlspecialchars($row->description); ?></p>
                <p class="price">RM <?php echo number_format($row->price, 2); ?></p>
                <div class="btn-group">
                    <a href="addcart.php?id=<?php echo $row->id; ?>" class="cart-btn">
                        <i class="fa fa-cart-plus"></i> Add to Cart
                    </a>
                    <a href="checkout.php?id=<?php echo $row->id; ?>" class="buy-btn">
                        Buy Now
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class='no-product'>No products found</div>
    <?php endif; ?>
</section>

<div class="pagination">
    <?php for($i=1; $i<=$total_pages; $i++): ?>
        <?php if($i == $page): ?>
            <span class='active'><?php echo $i; ?></span>
        <?php else: ?>
            <a href='?page=<?php echo $i; ?><?php 
                echo $search ? "&search=".urlencode($search) : ""; 
                echo $category ? "&category=".urlencode($category) : ""; 
                echo $price ? "&price=".urlencode($price) : ""; 
            ?>'><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
