<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="about">
        <h2>About Us</h2>
        <div class="about-content">
            <p>We are a premier wedding event management company dedicated to making your special day unforgettable. Our team of experienced professionals handles everything from venue booking to catering and entertainment, ensuring a seamless and stress-free experience.</p>
            <p>With a passion for creating magical moments, we tailor our services to meet your unique vision and budget. Let us bring your dream wedding to life!</p>
        </div>
        <h3>Our Team</h3>
        <div class="team-grid">
            <div class="team-member">
                <img src="images/somalatha.jpg" alt="John Doe">
                <h4>P. SOMA LATHA</h4>
                <p>Event Coordinator</p>
            </div>
            <div class="team-member">
                <img src="images/ganesh.jpg" alt="Jane Smith">
                <h4>K. GANESH</h4>
                <p>Creative Director</p>
            </div>
            <div class="team-member">
                <img src="images/photo.jpg" alt="Mike Johnson">
                <h4>M. RAGHAVENDRA</h4>
                <p>Logistics Manager</p>
            </div>
            <div class="team-member">
                <img src="images/sai.jpg" alt="Emily Davis">
                <h4>P. SAI KRISHNA</h4>
                <p>Customer Relations</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>