<?php session_start(); ?>
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Slider Section -->
    <section class="hero-slider">
        <div class="slider-item" style="background-image: url('https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');">
        <div class="slider-content">
    <h1>Your Wedding, Your Way</h1>
    <p>Find the Best Wedding Vendors with Thousands of Trusted Reviews</p>
    <form action="function_hall.php" method="GET" class="search-bar">
    <select name="category" required aria-label="Select Vendor Type">
        <option value="" disabled selected>Select Vendor Type</option>
        <option value="function_hall">Function Hall</option>
        <!-- Optionally keep other options for future expansion, but they won't redirect -->
        <option value="invitation" disabled>Invitation</option>
        <option value="photography" disabled>Photography & Videography</option>
        <option value="beauty" disabled>Beauty & Makeup</option>
        <option value="catering" disabled>Catering</option>
        <option value="dance" disabled>Dance</option>
        <option value="decoration" disabled>Decoration</option>
        <option value="music" disabled>Music Band</option>
        <option value="transport" disabled>Transport Vehicle</option>
        <option value="cake" disabled>Wedding Cake</option>
    </select>
    <input type="text" name="location" placeholder="City" required aria-label="City">
    <button type="submit">Search</button>
</form>
</div>
        
    </section>

    <!-- Services Section -->
    <section class="services">
        <h2>Our Services</h2>
        <div class="service-grid">
            <a href="function_hall.php" class="service-box">
                <i class="fas fa-building"></i>
                <h3>Function Hall</h3>
            </a>
            <a href="invitation.php" class="service-box">
                <i class="fas fa-envelope"></i>
                <h3>Invitation</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-camera"></i>
                <h3>Photography & Videography</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-paint-brush"></i>
                <h3>Beauty & Makeup</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-utensils"></i>
                <h3>Catering</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-music"></i>
                <h3>Dance</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-flower"></i>
                <h3>Decoration</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-guitar"></i>
                <h3>Music Band</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-car"></i>
                <h3>Transport Vehicle</h3>
            </a>
            <a href="#" class="service-box">
                <i class="fas fa-cake-candles"></i>
                <h3>Wedding Cake</h3>
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(document).ready(function(){
            $('.hero-slider').slick({
                dots: true,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                adaptiveHeight: true,
                arrows: true,
                autoplay: true,
                autoplaySpeed: 5000,
                fade: true,
                cssEase: 'linear'
            });
        });
    </script>
</body>
</html>