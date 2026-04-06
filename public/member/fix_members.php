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

echo "<h2>Fixing Member Database...</h2>";

try {
    // 1. Check if 'created_at' exists in 'members' table
    $stmt = $_db->query("SHOW COLUMNS FROM members LIKE 'created_at'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $_db->exec("ALTER TABLE members ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<span style='color:green;'>✓ ADDED:</span> 'created_at' column to members table.<br>";
    } else {
        echo "<span style='color:blue;'>ℹ INFO:</span> 'created_at' column already exists.<br>";
    }

    // 2. Check images
    $stmt = $_db->query("SELECT id, name, photo FROM members");
    $members = $stmt->fetchAll();
    
    foreach ($members as $m) {
        $img_path = "uploads/" . $m->photo;
        $abs_path = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $m->photo;
        
        if (file_exists($abs_path)) {
            echo "<span style='color:green;'>✓ FOUND:</span> ID {$m->id} ({$m->name}) -> Photo exists.<br>";
        } else {
            echo "<span style='color:red;'>✗ MISSING:</span> ID {$m->id} ({$m->name}) -> Photo '{$m->photo}' not found in uploads folder.<br>";
        }
    }
    
    echo "<h3>Finished!</h3>";
    echo "<p><a href='index.php'>Go to Member List</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
