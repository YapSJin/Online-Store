<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$parts = array_values(array_filter(explode('/', trim($scriptName, '/')), fn($p) => $p !== ''));
$knownRoots = ['public', 'admin', 'assets', 'component', 'config', 'models', 'helper', 'lib'];
$appRoot = '';
if (count($parts) >= 2 && !in_array($parts[0], $knownRoots, true)) {
    $appRoot = '/' . $parts[0];
}

$publicUrl = $appRoot . '/public';
?>

<footer id="contact">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">LALA.</div>
            <div class="footer-sub">© <?php echo date("Y"); ?> Urban Essentials.</div>
            <div class="footer-sub">Designed for the Streets.</div>
        </div>

        <div class="footer-links">
            <div class="footer-title">Quick Links</div>
            <a href="<?php echo $publicUrl; ?>/maps.php">Stores</a>
            <a href="<?php echo $publicUrl; ?>/about.php">About Us</a>
            <a href="<?php echo $publicUrl; ?>/home.php#contact">Contact</a>
        </div>

        <div class="social">
            <div class="footer-title">Follow Us</div>
            <a href="#"><i class="fab fa-facebook"></i> @LalaOfficial</a>
            <a href="#"><i class="fab fa-instagram"></i> @LalaStreetwear</a>
            <a href="#"><i class="fab fa-youtube"></i> @LalaStudio</a>
        </div>
    </div>
</footer>
