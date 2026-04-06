<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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

$conn = mysqli_connect("localhost", "root", "", "member_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
