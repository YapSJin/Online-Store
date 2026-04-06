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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: report.php");
    exit;
}

$mStmt = $_db->prepare("SELECT id, username, email, phone_num, role, profile_image, created_at FROM users WHERE id = ? LIMIT 1");
$mStmt->execute([$id]);
$member = $mStmt->fetch(PDO::FETCH_ASSOC);
if (!$member) {
    header("Location: report.php");
    exit;
}

$email = $member['email'] ?? '';
$orders = [];
$ordersError = '';

$orderDateCandidates = ['order_date', 'created_at'];
foreach ($orderDateCandidates as $orderDateCol) {
    try {
        $oStmt = $_db->prepare("SELECT * FROM orders WHERE email = ? ORDER BY {$orderDateCol} DESC");
        $oStmt->execute([$email]);
        $orders = $oStmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    } catch (Throwable $e) {
        $ordersError = $e->getMessage();
    }
}

$itemsByOrder = [];
if (!empty($orders)) {
    $ids = array_map(fn($o) => (int)$o['id'], $orders);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    try {
        $iStmt = $_db->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY order_id DESC, id ASC");
        $iStmt->execute($ids);
        foreach ($iStmt->fetchAll(PDO::FETCH_ASSOC) as $it) {
            $oid = (int)$it['order_id'];
            if (!isset($itemsByOrder[$oid])) {
                $itemsByOrder[$oid] = [];
            }
            $itemsByOrder[$oid][] = $it;
        }
    } catch (Throwable $e) {
    }
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}

$photo_src = trim((string)($member['profile_image'] ?? ''));
$photo_src = str_replace('\\', '/', $photo_src);
if ($photo_src === '') {
    $photo_src = $appRoot . "/assets/image/logo/default.png";
} else {
    $photo_src = $appRoot . '/' . ltrim($photo_src, '/');
}

$reg_date = isset($member['created_at']) ? date('Y-m-d', strtotime($member['created_at'])) : "N/A";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Detail - LALA Clothing</title>
    <link rel="stylesheet" href="member.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>
</head>
<body>

<?php include '../header.php'; ?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px; min-height: 60vh; max-width: 1000px;">
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <h2 style="margin:0;">Report Detail</h2>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="detail.php?id=<?php echo (int)$member['id']; ?>" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">Member Details</a>
                <a href="report.php" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">← Back</a>
            </div>
        </div>

        <div style="display:flex; gap:18px; align-items:center; margin-top: 18px; background:#f8f9fa; padding: 16px; border-radius: 10px;">
            <img src="<?php echo $photo_src; ?>" width="90" height="90" style="border-radius:50%; object-fit:cover; border: 3px solid #fff;">
            <div>
                <div style="font-size: 20px; font-weight: 800;"><?php echo htmlspecialchars($member['username'] ?? ''); ?></div>
                <div style="color:#333;"><?php echo htmlspecialchars($member['email'] ?? ''); ?></div>
                <div style="color:#666; font-size: 12px;">Registered: <?php echo htmlspecialchars($reg_date); ?></div>
            </div>
        </div>

        <h3 style="margin-top: 24px;">Orders</h3>

        <?php if ($ordersError !== '' && empty($orders)): ?>
            <p>Orders table is not ready in the current database.</p>
        <?php elseif (empty($orders)): ?>
            <p>No orders found for this member email.</p>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
                <?php $oid = (int)$o['id']; ?>
                <div style="margin-top: 16px; border: 1px solid #eee; border-radius: 10px; overflow:hidden;">
                    <div style="background:#111; color:white; padding: 12px 14px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div><b>Order #<?php echo $oid; ?></b> — <?php echo htmlspecialchars($o['order_date'] ?? ($o['created_at'] ?? '')); ?></div>
                        <div>Status: <?php echo htmlspecialchars($o['status'] ?? ''); ?></div>
                    </div>
                    <div style="padding: 14px;">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div><b>Name:</b> <?php echo htmlspecialchars($o['customer_name'] ?? ''); ?></div>
                            <div><b>Phone:</b> <?php echo htmlspecialchars($o['phone'] ?? ''); ?></div>
                            <div style="grid-column: 1 / -1;"><b>Address:</b> <?php echo htmlspecialchars($o['address'] ?? ''); ?>, <?php echo htmlspecialchars($o['city'] ?? ''); ?>, <?php echo htmlspecialchars($o['state'] ?? ''); ?>, <?php echo htmlspecialchars($o['postcode'] ?? ''); ?></div>
                        </div>

                        <table style="width:100%; border-collapse: collapse; margin-top: 12px;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="padding: 10px; text-align:left;">Item</th>
                                    <th style="padding: 10px; text-align:left;">Price</th>
                                    <th style="padding: 10px; text-align:left;">Qty</th>
                                    <th style="padding: 10px; text-align:left;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($itemsByOrder[$oid] ?? []) as $it): ?>
                                    <tr style="border-top: 1px solid #eee;">
                                        <td style="padding: 10px;"><?php echo htmlspecialchars($it['product_name'] ?? ''); ?></td>
                                        <td style="padding: 10px;">RM <?php echo number_format((float)($it['price'] ?? 0), 2); ?></td>
                                        <td style="padding: 10px;"><?php echo (int)($it['quantity'] ?? 0); ?></td>
                                        <td style="padding: 10px;">RM <?php echo number_format((float)($it['subtotal'] ?? 0), 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($itemsByOrder[$oid] ?? [])): ?>
                                    <tr>
                                        <td colspan="4" style="padding: 12px; text-align:center;">No items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div style="margin-top: 12px; background:#f8f9fa; padding: 12px; border-radius: 8px;">
                            <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Subtotal</span><span>RM <?php echo number_format((float)($o['subtotal'] ?? 0), 2); ?></span></div>
                            <?php if ((float)($o['tax'] ?? 0) > 0): ?>
                                <div style="display:flex; justify-content:space-between; margin:6px 0;"><span>Tax</span><span>RM <?php echo number_format((float)($o['tax'] ?? 0), 2); ?></span></div>
                            <?php endif; ?>
                            <?php if (isset($o['discount_amount']) && (float)$o['discount_amount'] > 0): ?>
                                <div style="display:flex; justify-content:space-between; margin:6px 0; color: #dc3545;"><span>Discount</span><span>- RM <?php echo number_format((float)$o['discount_amount'], 2); ?></span></div>
                            <?php endif; ?>
                            <div style="display:flex; justify-content:space-between; margin:6px 0; font-weight:800;"><span>Total</span><span>RM <?php echo number_format((float)($o['total_amount'] ?? ($o['total'] ?? ($o['grand_total'] ?? 0))), 2); ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
