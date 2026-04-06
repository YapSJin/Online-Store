<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

try {
    $_db = new PDO(
        'mysql:host=localhost;dbname=phpassignment;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // 安全启动 session，避免重复调用 Notice
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>