<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please signup/login!'); window.location='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="profile">
        <h2>My Profile</h2>
        <form method="POST">
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            <button type="submit">Update Profile</button>
        </form>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>