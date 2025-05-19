<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

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
    <script src="js/script.js"></script>
</body>
</html>