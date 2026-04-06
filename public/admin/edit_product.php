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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $_db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $productname = trim($_POST['productname'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $category = $_POST['category'] ?? null;

    $image_path = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $img_name = time() . "_" . basename($_FILES['image']['name']);
        $target_dir = "../../assets/image/product/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "assets/image/product/" . $img_name;
        }
    }

    $upd = $_db->prepare("UPDATE products SET productname = ?, description = ?, price = ?, quantity = ?, image = ?, category = ? WHERE id = ?");
    $upd->execute([$productname, $description, $price, $quantity, $image_path, $category, $id]);

    header("Location: index.php");
    exit();
}

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

$img_path = normalize_product_img_src($product['image'] ?? '');
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Product ✏ </title>
<link rel="icon" type="image/png" href="../../assets/image/home/logo.png?v=1">
<style>
body{
    font-family:Arial, sans-serif;
    background-image: url("background.png");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding-top:50px;
    height:100%;
    margin:0;
}

.container{
    background:white;
    padding:30px 40px;
    width:400px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:25px;
}

label{
    font-weight:bold;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-top:6px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

input:focus, textarea:focus{
    border-color:#007bff;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#007bff;
    color:white;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
    transition:0.2s;
}

button:hover{
    background:#0056b3;
}

.back{
    display:block;
    text-align:center;
    margin-top:15px;
    text-decoration:none;
    color:#555;
}

#preview{
    width:100px; 
    height:100px;
    margin-bottom:15px;
    border-radius:8px;
    object-fit:cover;
}

</style>
</head>
<body>

<div class="container">
<h2>✏ Edit Product</h2>

<form method="POST" enctype="multipart/form-data">
<label>Product Name</label>
<input type="text" name="productname" value="<?php echo htmlspecialchars($product['productname'] ?? ''); ?>" required>

<label>Category</label>
<select name="category" required>
    <?php
    $currentCategory = $product['category'] ?? '';
    $categories = ['clothes' => 'Clothes', 'pants' => 'Pants', 'hoodie' => 'Hoodie'];
    echo '<option value="">Select Category</option>';
    foreach ($categories as $value => $label) {
        $selected = ($currentCategory === $value) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($value) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
    }
    ?>
</select>

<label>Description</label>
<textarea name="description" rows="3" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

<label>Price</label>
<input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars((string)($product['price'] ?? '')); ?>" required>

<label>Quantity</label>
<input type="number" name="quantity" value="<?php echo htmlspecialchars((string)($product['quantity'] ?? '')); ?>" required>

<label>Image Upload</label>
<input type="file" name="image" accept="image/*" onchange="previewImage(event)">
<img id="preview" src="<?php echo htmlspecialchars($img_path); ?>">

<button type="submit" name="update">Update Product</button>
</form>

<a href="index.php" class="back">← Back to Product List</a>
</div>

<script>
function previewImage(event){
    const preview = document.getElementById('preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}
</script>

</body>
</html>
