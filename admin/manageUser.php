<?php
// 1. 权限检查
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

// 2. 引入必要文件
require "../config/db.php";
require "../models/userModels.php"; // 引入你刚才提供的那个包含 getAllUser 的文件
require "header.php";
require "../helper/html_helper.php";

// 3. 处理查询逻辑
$roles = $_GET['role'] ?? 'all';
$search = $_GET['search'] ?? '';

// 获取用户列表
$users = getAllUser($roles, $search);

// 4. 处理 POST 操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    if (isset($_POST['edit_user'])) {
        header("Location: adminEditUser.php?user_id=$user_id");
        exit;
    }

    if (isset($_POST['delete_user'])) {
        deleteUserById($user_id);
        header("Location: manageUser.php");
        exit;
    }
}
?>

<link rel="stylesheet" href="../assets/css/manageUser.css">

<div class="manage-container">
    <h2>Manage Users</h2>

    <div class="role-filter">
        <a href="?role=all" class="<?= $roles === 'all' ? 'active' : '' ?>"><b>ALL</b></a> |
        <a href="?role=user" class="<?= $roles === 'user' ? 'active' : '' ?>"><b>User</b></a> |
        <a href="?role=admin" class="<?= $roles === 'admin' ? 'active' : '' ?>"><b>Admin</b></a>
    </div>

    <div class="role-container">
        <div class="top-bar">
            <div class="add-user">
                <a href="adminAddUser.php" class="btn-create-user">
                    <b>➕ Create New User / Admin</b>
                </a>
            </div>
            <div class="add-user">
                <a href="manageOrders.php" class="btn-create-user" style="background:#111;">
                    <b>📄 Manage Orders</b>
                </a>
            </div>

            <form method="GET" class="search-form">
                <input type="hidden" name="role" value="<?= htmlspecialchars($roles) ?>">
                <?php 
                if (function_exists('html_search')) {
                    html_search('search', 'Username / Email / Phone', "class='search-input'"); 
                } else {
                    echo '<input type="text" name="search" placeholder="Search..." class="search-input" value="'.htmlspecialchars($search).'">';
                }
                ?>
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->id) ?></td>
                            <td><?= htmlspecialchars($user->username) ?></td>
                            <td><?= htmlspecialchars($user->email) ?></td>
                            <td><?= htmlspecialchars($user->phone_num) ?></td>
                            <td>
                                <span class="badge-<?= $user->role ?>">
                                    <?= ucfirst($user->role) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirmDelete(this);">
                                    <input type="hidden" name="user_id" value="<?= $user->id ?>">
                                    <button type="submit" name="edit_user" class="btn-edit">Edit</button>
                                    <button type="submit" name="delete_user" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(form) {
    if (event.submitter && event.submitter.name === 'delete_user') {
        return confirm("Are you sure you want to delete this user account?");
    }
    return true;
}
</script>

