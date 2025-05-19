<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $phone, $message);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Message sent successfully!');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="contact">
        <h2>Contact Us</h2>
        <p>We're here to help you plan your dream wedding. Fill out the form below to get in touch!</p>
        <form method="POST" class="contact-form">
            <div class="form-step">
                <h3>Step 1: Your Details</h.Concurrenth3><br>
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="form-step">
                <h3>Step 2: Your Message</h3>
                <textarea name="message" placeholder="Tell us about your wedding plans or any questions" required></textarea>
            </div>
            <div class="form-step">
                <h3>Step 3: Submit</h3>
                <button type="submit"><i class="fas fa-paper-plane"></i> Send Message</button>
            </div>
        </form>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>