<?php
session_start();
require_once "../config/db.php";

$settings = [];
try {
    $st = $_db->query("SELECT setting_key, setting_value FROM site_settings");
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
} catch (Throwable $e) {
}

$background_image = $settings['home_hero_bg'] ?? ($settings['background_image'] ?? '../assets/image/home/homepagebackground.png');
$promo_bg = $settings['home_promo_bg'] ?? ($settings['promotion_background'] ?? '../assets/image/home/promotionbackground.png');
$store_image = $settings['home_store_img'] ?? ($settings['store_image'] ?? '../assets/image/home/store-preview.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LALA Clothing Store</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/home_v2.css">

<!-- 如果需要 Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"></script>

</head>
<body>

<?php include 'header.php'; ?> <!-- 这里 include header.php -->

<!-- HERO -->
<section class="hero"
style="background:
linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),
url('<?php echo htmlspecialchars($background_image); ?>') center/cover no-repeat;">
<h1>WELCOME TO LALA</h1>
<p>STREETWEAR • STYLE • ATTITUDE</p>
<div class="hero-btns">
    <a href="product.php" class="btn">Shop Now</a>
    <a href="product.php" class="btn-outline">Explore</a>
</div>
</section>

<!-- PROMO -->
<section class="promo"
style="background:
linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),
url('<?php echo htmlspecialchars($promo_bg); ?>') center/cover no-repeat;">
<h2>🔥 Mid Season Sale</h2>
<p>Up to 50% OFF on selected items</p>
<a href="product.php" class="btn">Shop Sale</a>
</section>

<!-- CATEGORIES -->
<section class="categories">
<h2 class="section-title">Shop Categories</h2>
<div class="category-container">
    <a href="product.php?category=clothes" class="category-card">
        <div class="emoji">👕</div>
        <h3>Shirts</h3>
        <p>Explore our latest shirts</p>
    </a>

    <a href="product.php?category=pants" class="category-card">
        <div class="emoji">👖</div>
        <h3>Pants</h3>
        <p>Comfortable and stylish pants</p>
    </a>

    <a href="product.php?category=hoodie" class="category-card">
        <div class="emoji">🧥</div>
        <h3>Hoodie</h3>
        <p>Warm and trendy hoodies</p>
    </a>
</div>
</section>

<section class="home-store">
    <div class="store-content">
        <div class="store-info">
            <span class="tag">LALA CLOTHING STORE</span>
            <h2>Our Streetwear Store</h2>
            <p>Bold, modern, and always trending — discover outfits made for your lifestyle.</p>
            <div class="store-details">
                <p><b>📍 Location:</b> Malaysia</p>
                <p><b>🕒 Open Hours:</b> 10:00 AM - 10:00 PM</p>
                <p><b>✨ Special:</b> New arrivals every week!</p>
            </div>
            <a href="maps.php" class="btn">View on Map</a>
        </div>
        <div class="store-preview">
            <img src="<?php echo htmlspecialchars($store_image); ?>" alt="LALA Store">
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features">
<div class="feature-card">
<img src="../assets/image/home/quality.png">
<h3>Premium Quality</h3>
<p>High-quality fabrics and durable materials.</p>
</div>

<div class="feature-card">
<img src="../assets/image/home/fast-delivery.png">
<h3>Fast Delivery</h3>
<p>Quick and reliable delivery service.</p>
</div>

<div class="feature-card">
<img src="../assets/image/home/special-discount.png">
<h3>Special Discounts</h3>
<p>Enjoy seasonal discounts and promotions.</p>
</div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
