<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please signup/login!'); window.location='login.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="history">
        <h2>My History</h2>
        <?php
        $stmt = $conn->prepare("SELECT b.*, v.business_name FROM bookings b JOIN vendors v ON b.vendor_id = v.id WHERE b.user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<div class='booking'>";
            echo "<h3>" . htmlspecialchars($row['business_name']) . "</h3>";
            echo "<p>Date: " . htmlspecialchars($row['booking_date']) . "</p>";
            echo "<p>Payment Status: " . htmlspecialchars($row['payment_status']) . "</p>";
            echo "</div>";
        }
        $stmt->close();
        ?>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>