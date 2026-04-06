<?php
session_start();
// 权限验证：非管理员禁止进入
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

$is_admin_form = true; // 开启管理员模式：显示角色选择，跳过验证码
require_once __DIR__ . "/../public/register.php"; 
?>
