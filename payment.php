<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['booking_data'])) {
    echo "<script>alert('Please signup/login or select a booking!'); window.location='login.php';</script>";
    exit;
}

$booking_data = $_SESSION['booking_data'];
$vendor_id = $booking_data['vendor_id'];
$booking_date = $booking_data['booking_date'];
$advance_payment = $booking_data['advance_payment'];

$stmt = $conn->prepare("SELECT business_name, bank_name, account_holder, account_number, ifsc_code FROM vendors WHERE id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if vendor exists
if (!$vendor) {
    echo "<script>alert('Vendor not found!'); window.location='function_hall.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, vendor_id, booking_date, advance_payment, payment_status) VALUES (?, ?, ?, ?, 'success')");
    $stmt->bind_param("iisd", $_SESSION['user_id'], $vendor_id, $booking_date, $advance_payment);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO availability (vendor_id, date, status) VALUES (?, ?, 'booked') ON DUPLICATE KEY UPDATE status = 'booked'");
    $stmt->bind_param("is", $vendor_id, $booking_date);
    $stmt->execute();
    $stmt->close();

    unset($_SESSION['booking_data']);
    echo "<script>alert('Booked successfully!'); window.location='history.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="payment">
        <h2>Payment Details</h2>
        <div class="payment-details">
            <h3><?php echo htmlspecialchars($vendor['business_name']); ?></h3>
            <p>Advance Payment: $<?php echo htmlspecialchars($advance_payment); ?></p>
            <p>Bank Name: <?php echo htmlspecialchars($vendor['bank_name']); ?></p>
            <p>Account Holder: <?php echo htmlspecialchars($vendor['account_holder']); ?></p>
            <p>Account Number: <?php echo htmlspecialchars($vendor['account_number']); ?></p>
            <p>IFSC Code: <?php echo htmlspecialchars($vendor['ifsc_code']); ?></p>
        </div>
        <form method="POST">
            <button type="submit" name="pay">Pay Now</button>
        </form>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>