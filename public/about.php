<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once "header.php";
?>

<link rel="stylesheet" href="../assets/css/about.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="about-wrapper">
    <section class="about-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="fade-in">STREET FASHION</h1>
            <p class="fade-in-delay">QUALITY MATERIALS. URBAN SOUL.</p>
        </div>
    </section>

    <section class="container section-padding">
        <div class="grid-2col">
            <div class="story-image">
                <img src="../assets/image/home/store-preview.jpg" alt="Street Culture" class="img-responsive">
            </div>
            <div class="story-text">
                <span class="tagline">OUR STORY</span>
                <h2>Born in the Streets, Crafted for You.</h2>
                <p>
                    It started under the neon lights, amidst the rhythm of the city. We noticed that streetwear often sacrificed quality for "hype." We wanted to change that.
                </p>
                <p>
                    <strong>Our Mission:</strong> To redefine modern essentials by combining bold street aesthetics with premium, long-lasting materials. We believe everyone deserves to feel stylish and comfortable, without compromise.
                </p>
            </div>
        </div>
    </section>

    <section class="values-bg">
        <div class="container section-padding">
            <div class="section-header text-center">
                <h2>WHY WE DO IT</h2>
            </div>
            <div class="grid-3col">
                <div class="value-card">
                    <i class="fas fa-tshirt"></i>
                    <h3>Premium Quality</h3>
                    <p>We use high-gram weight cotton and technical fabrics engineered to last through every wash.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-users"></i>
                    <h3>For Everyone</h3>
                    <p>Our silhouettes are designed to be inclusive, blurring the lines of gender and size.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-fist-raised"></i>
                    <h3>Urban Authenticity</h3>
                    <p>Rooted in real street culture, we don't follow trends—we build timeless style.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-cta text-center">
        <div class="container">
            <h2>READY TO LEVEL UP YOUR WARDROBE?</h2>
            <p>Join the movement and explore our latest drops.</p>
            <a href="product.php" class="btn-primary">SHOP COLLECTION</a>
        </div>
    </section>
</main>

<?php include_once "footer.php"; ?>
