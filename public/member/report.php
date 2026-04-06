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

$search = trim($_GET['search'] ?? '');
$params = [];

$rows = [];

$where = "WHERE u.role = 'user'";
if ($search !== '') {
    $where .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

try {
    $sql = "
        SELECT
            u.id,
            u.username,
            u.email,
            u.profile_image,
            u.created_at,
            COALESCE(o.order_count, 0) AS order_count,
            COALESCE(o.total_spent, 0) AS total_spent,
            o.last_order_date
        FROM users u
        LEFT JOIN (
            SELECT email,
                   COUNT(*) AS order_count,
                   SUM(total_amount) AS total_spent,
                   MAX(order_date) AS last_order_date
            FROM orders
            GROUP BY email
        ) o ON o.email = u.email
        {$where}
        ORDER BY u.id DESC
    ";
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $sql = "SELECT u.id, u.username, u.email, u.profile_image, u.created_at FROM users u {$where} ORDER BY u.id DESC";
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['order_count'] = 0;
        $r['total_spent'] = 0;
        $r['last_order_date'] = null;
    }
    unset($r);
}

function report_app_root() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
    $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
    if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
        return '/' . $parts[0];
    }
    return '';
}

function report_profile_img_src($raw) {
    $appRoot = report_app_root();
    $p = trim((string)$raw);
    $p = str_replace('\\', '/', $p);
    if ($p === '') {
        return $appRoot . '/assets/image/logo/default.png';
    }
    $p = ltrim($p, '/');
    return $appRoot . '/' . $p;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Report - LALA Clothing</title>
    <link rel="stylesheet" href="member.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>
</head>
<body>

<?php include '../header.php'; ?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px; min-height: 60vh;">
    <h2>Member Report</h2>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px; flex-wrap: wrap;">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search name/email" value="<?php echo htmlspecialchars($search); ?>"
                   style="padding: 8px; border-radius: 6px; border: 1px solid #ddd; width: 280px;">
            <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Search</button>
        </form>
        <a href="index.php" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">← Back</a>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background: #111; color: white;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Member</th>
                <th style="padding: 12px;">Email</th>
                <th style="padding: 12px;">Orders</th>
                <th style="padding: 12px;">Total Spent</th>
                <th style="padding: 12px;">Last Order</th>
                <th style="padding: 12px;">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($rows) > 0): ?>
            <?php foreach ($rows as $r): ?>
                <?php
                    $photo_src = report_profile_img_src($r['profile_image'] ?? '');
                ?>
                <tr style="text-align: center; border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?php echo (int)$r['id']; ?></td>
                    <td style="padding: 12px; text-align:left;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo $photo_src; ?>" width="42" height="42" style="border-radius:50%; object-fit:cover;">
                            <div>
                                <div style="font-weight:700;"><?php echo htmlspecialchars($r['username']); ?></div>
                                <div style="color:#666; font-size:12px;"><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($r['email']); ?></td>
                    <td style="padding: 12px;"><?php echo (int)($r['order_count'] ?? 0); ?></td>
                    <td style="padding: 12px;">RM <?php echo number_format((float)($r['total_spent'] ?? 0), 2); ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($r['last_order_date'] ?? '-'); ?></td>
                    <td style="padding: 12px;">
                        <a href="report_detail.php?id=<?php echo (int)$r['id']; ?>" style="color: #007bff; text-decoration: none;">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="padding: 20px; text-align: center;">No members found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
