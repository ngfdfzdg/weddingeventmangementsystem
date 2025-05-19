<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please signup/login!'); window.location='login.php';</script>";
    exit;
}

if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="cart">
        <h2>My Cart</h2>
        <?php
        $stmt = $conn->prepare("SELECT c.id, v.id as vendor_id, v.business_name, v.price_per_day FROM cart c JOIN vendors v ON c.vendor_id = v.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo "<p>Your cart is empty.</p>";
        } else {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='cart-item'>";
                echo "<h3><a href='function_hall.php?vendor_id=" . $row['vendor_id'] . "'>" . htmlspecialchars($row['business_name']) . "</a></h3>";
                echo "<p>Price per Day: $" . htmlspecialchars($row['price_per_day']) . "</p>";
                echo "<a href='cart.php?remove=" . $row['id'] . "' class='remove-link'>Remove</a>";
                echo "</div>";
            }
        }
        $stmt->close();
        ?>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>