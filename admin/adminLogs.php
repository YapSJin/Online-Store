<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/admin_login.php");
    exit;
}

include 'header.php'; 
include '../models/logModels.php';

$logType = $_GET['log'] ?? 'product'; // default to product

// Whitelist allowed logs
$allowedLogs = [
    'product' => 'admin_product_edit_logs',
    'user' => 'admin_user_edit_logs'
];

if (!array_key_exists($logType, $allowedLogs)) {
    echo "<p>❌ Invalid log type.</p>";
    exit;
}

$tableName = $allowedLogs[$logType];

// Fetch logs
$logs = getLogsByTable($tableName);
?>

<link rel="stylesheet" href="../assets/css/logs.css">

<h2>Logs</h2>

<div class="logs">
    <a href="?log=product"><b>Product Logs</b></a> |
    <a href="?log=user"><b>User Logs</b></a> 
</div>

<div class="logs-container">
    <?php if (count($logs) > 0): ?>
        <table class="log-table">
            <thead>
                <tr>
                    <?php foreach (array_keys($logs[0]) as $column): ?>
                        <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $column))) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <?php foreach ($log as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No logs found.</p>
    <?php endif; ?>
</div>

