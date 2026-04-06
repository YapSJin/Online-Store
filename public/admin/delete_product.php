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

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Optional: Delete the physical image file if you want
        // $stmt = $_db->prepare("SELECT image FROM products WHERE id = ?");
        // $stmt->execute([$id]);
        // $product = $stmt->fetch();
        // if ($product && file_exists("../" . $product->image)) {
        //     unlink("../" . $product->image);
        // }

        $stmt = $_db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error deleting: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>
