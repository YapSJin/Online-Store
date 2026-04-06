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

$search = "";
$params = [];

    if (isset($_GET['search']) && $_GET['search'] !== "") {
    $search = $_GET['search'];
    $sql = "SELECT id, username, email, role, profile_image, created_at FROM users WHERE (username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
} else {
    $sql = "SELECT id, username, email, role, profile_image, created_at FROM users";
}

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member List - LALA Clothing</title>
    <link rel="stylesheet" href="member.css">
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>
</head>
<body>

<?php include '../header.php'; ?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px; min-height: 60vh;">
    <h2>Member List</h2>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search name" value="<?php echo htmlspecialchars($search); ?>" 
                   style="padding: 8px; border-radius: 6px; border: 1px solid #ddd; width: 250px;">
            <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Search</button>
        </form>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="report.php" class="btn-link" style="padding: 10px 15px; background: #111; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">Report</a>
            <a href="../register.php" class="btn-link" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">+ Register New Member</a>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <thead>
    <tr style="background: #111; color: white;">
        <th style="padding: 12px;">ID</th>
        <th style="padding: 12px;">Name</th>
        <th style="padding: 12px;">Email</th>
        <th style="padding: 12px;">Role</th>
        <th style="padding: 12px;">Photo</th>
        <th style="padding: 12px;">Action</th>
    </tr>
    </thead>
    <tbody>
    <?php if (count($members) > 0): ?>
        <?php foreach ($members as $row): ?>
        <tr style="text-align: center; border-bottom: 1px solid #eee;">
            <td style="padding: 12px;"><?php echo $row->id; ?></td>
            <td style="padding: 12px;"><?php echo htmlspecialchars($row->username); ?></td>
            <td style="padding: 12px;"><?php echo htmlspecialchars($row->email); ?></td>
            <td style="padding: 12px;">
                <?php
                    $role = $row->role ?? '';
                    $badgeBg = ($role === 'admin') ? '#ff9800' : '#6c757d';
                ?>
                <span style="display:inline-block; padding:4px 10px; border-radius:999px; background: <?php echo $badgeBg; ?>; color: white; font-size: 12px; font-weight: 700;">
                    <?php echo htmlspecialchars(ucfirst($role)); ?>
                </span>
            </td>
            <td style="padding: 12px;">
                <?php 
                    $photo_src = $row->profile_image ?? '';
                    $photo_src = str_replace('\\', '/', $photo_src);
                    if ($photo_src === '' || $photo_src === null) {
                        $photo_src = "../../assets/image/logo/default.png";
                    } else {
                        $photo_src = ltrim($photo_src, '/');
                        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                        $parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
                        $knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
                        $appRoot = '';
                        if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
                            $appRoot = '/' . $parts[0];
                        }
                        $photo_src = $appRoot . '/' . $photo_src;
                    }
                ?>
                <img src="<?php echo $photo_src; ?>" class="member-photo" width="50" height="50" style="border-radius: 50%; object-fit: cover;">
            </td>
            <td style="padding: 12px;">
                <a href="detail.php?id=<?php echo $row->id; ?>" style="color: #007bff; text-decoration: none;">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="padding: 20px; text-align: center;">No members found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
