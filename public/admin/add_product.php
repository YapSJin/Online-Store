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

if (isset($_POST['submit'])) {
    $productname = $_POST['productname'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $quantity    = $_POST['quantity'];
    $category    = $_POST['category'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        // Target directory relative to this file
        $target_dir = "../../assets/image/product/";
        
        // Ensure directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($img_name);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        
        // Path stored in database (relative to project root)
        $image_path = "assets/image/product/" . $img_name;
    } else {
        $image_path = "";
    }

    try {
        $sql = "INSERT INTO products (productname, description, price, quantity, image, category)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $_db->prepare($sql);
        $stmt->execute([$productname, $description, $price, $quantity, $image_path, $category]);
        
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <link rel="icon" type="image/png" href="../../assets/image/home/logo.png?v=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            background: white;
            padding: 30px 40px;
            width: 450px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.2s;
            margin-top: 10px;
        }

        button:hover {
            background: #218838;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #555;
        }

        #preview {
            width: 100px; 
            height: 100px;
            margin-bottom: 15px;
            border-radius: 8px;
            object-fit: cover;
            display: none;
            border: 1px solid #ddd;
        }
        
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>+ Add Product</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="productname" required placeholder="e.g. Hype Text T-Shirt">

        <label>Category</label>
        <select name="category" required>
            <option value="">Select Category</option>
            <option value="clothes">Clothes</option>
            <option value="pants">Pants</option>
            <option value="hoodie">Hoodie</option>
        </select>

        <label>Description</label>
        <textarea name="description" rows="3" required placeholder="Describe the product..."></textarea>

        <label>Price (RM)</label>
        <input type="number" step="0.01" name="price" required placeholder="0.00">

        <label>Quantity</label>
        <input type="number" name="quantity" required placeholder="0">

        <label>Image Upload</label>
        <input type="file" name="image" accept="image/*" onchange="previewImage(event)" required>
        <img id="preview" alt="image preview">

        <button type="submit" name="submit">Add Product</button>
    </form>

    <a href="index.php" class="back">← Back to Product List</a>
</div>

<script>
function previewImage(event){
    const preview = document.getElementById('preview');
    if (event.target.files && event.target.files[0]) {
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    }
}
</script>

</body>
</html>
