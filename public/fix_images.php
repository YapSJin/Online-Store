<?php
require_once '../config/db.php';

echo "<h2>Fixing Product Image Paths (Diagnostic Mode)...</h2>";

try {
    $stmt = $_db->query("SELECT id, productname, image FROM products");
    $products = $stmt->fetchAll();
    
    $count = 0;
    foreach ($products as $p) {
        $old_path = trim($p->image);
        $new_path = $old_path;
        
        // 1. Remove any leading slashes or dots
        $clean_path = ltrim($old_path, './\\');
        
        // 2. If it was something like 'images/001.png', change to 'assets/image/product/001.png'
        if (strpos($clean_path, 'images/') === 0) {
            $new_path = 'assets/image/product/' . substr($clean_path, 7);
        } else {
            $new_path = $clean_path;
        }

        // 3. Verify if file exists relative to the project root
        // The project root is one level up from this script (public/)
        $abs_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $new_path);
        
        if (file_exists($abs_path)) {
            echo "<span style='color:green;'>✓ FOUND:</span> ID {$p->id} ({$p->productname}) -> $new_path<br>";
        } else {
            echo "<span style='color:red;'>✗ NOT FOUND:</span> ID {$p->id} ({$p->productname}) -> $new_path (looked in $abs_path)<br>";
            
            // Try searching for the filename in the product image folder
            $filename = basename($new_path);
            $search_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . $filename;
            
            if (file_exists($search_path)) {
                $new_path = 'assets/image/product/' . $filename;
                echo "&nbsp;&nbsp;&nbsp;<span style='color:blue;'>→ RECOVERED:</span> found at $new_path<br>";
            }
        }
        
        // 4. Update if changed
        if ($new_path !== $old_path) {
            $update = $_db->prepare("UPDATE products SET image = ? WHERE id = ?");
            $update->execute([$new_path, $p->id]);
            $count++;
        }
    }
    
    echo "<h3>Finished! Updated $count products.</h3>";
    echo "<p><a href='product.php'>Go to Product Page</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>