<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LALA Clothing Store</title>

<link rel="stylesheet" href="../assets/css/home.css">


</head>

<body>

<header>

<a href="index.php" class="logo-link">
<img src="../assets/image/home/logo.png" class="logo">
<span class="brand-name">LALA</span>
</a>

<nav class="nav-desktop">

<div class="nav-links">
<a href="#">Home</a>
<a href="product.php">Products</a>
<a href="#contact">Contact</a>
<a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
</div>

<div class="auth-buttons">
<a href="login.php" class="login-btn">Login</a>
<a href="signup.php" class="signup-btn">Sign Up</a>
</div>

</nav>

</header>

<section class="hero">

<h1>Welcome to LALA</h1>
<p>Discover the latest streetwear and fashion trends</p>

<a href="product.php" class="btn">Shop Now</a>

</section>

<section class="promo">

<h2>🔥 Mid Season Sale</h2>
<p>Up to 50% OFF on selected items</p>

<a href="product.php" class="btn">Shop Sale</a>

</section>

<section class="categories">

<h2>Shop Categories</h2>

<div class="category-container">

<a href="product.php?category=clothes" class="category-card">

<i class="fa-solid fa-shirt"></i>
<h3>Shirts</h3>
<p>Explore our latest shirts</p>

</a>

<a href="product.php?category=pants" class="category-card">

<i class="fa-solid fa-user"></i>
<h3>Pants</h3>
<p>Comfortable and stylish pants</p>

</a>

<a href="product.php?category=hoodie" class="category-card">

<i class="fa-solid fa-user-astronaut"></i>
<h3>Hoodie</h3>
<p>Warm and trendy hoodies</p>

</a>

</div>

</section>

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

<section class="testimonials">

<h2>What Our Customers Say</h2>

<div class="testimonial-container">

<div class="testimonial">
<p>"Great quality clothing and fast delivery!"</p>
<h4>- Alex</h4>
</div>

<div class="testimonial">
<p>"Very stylish designs. I love this store!"</p>
<h4>- Sarah</h4>
</div>

<div class="testimonial">
<p>"Affordable price and excellent service."</p>
<h4>- Daniel</h4>
</div>

</div>

</section>

<section class="about">

<h2>About Us</h2>

<p>
We are a modern clothing brand focusing on street fashion and quality materials.
Our mission is to deliver stylish and comfortable outfits for everyone.
</p>

<a href="about.php" class="btn">Read More</a>

</section>

<footer id="contact">

<p>© <?php echo date("Y"); ?> LALA Clothing Store</p>

<p>Email: lala2026@email.com</p>

<div class="social">

<a href="#"><i class="fab fa-facebook"></i></a>
<a href="#"><i class="fab fa-instagram"></i></a>
<a href="#"><i class="fab fa-youtube"></i></a>

</div>

</footer>

</body>
</html>