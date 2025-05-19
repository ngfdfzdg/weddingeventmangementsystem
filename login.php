<?php
session_start();
include 'includes/db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = trim($_POST['type'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Debug: Log inputs
    error_log("Login attempt: type=$type, email=$email, email_length=" . strlen($email) . ", password_length=" . strlen($password));

    if ($type == 'user') {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        if ($stmt === false) {
            error_log("User query prepare failed: " . $conn->error);
            echo "<script>alert('Database error. Please try again later.');</script>";
        } else {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("User query execute failed: " . $stmt->error);
                echo "<script>alert('Database error. Please try again later.');</script>";
            } else {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['user_type'] = 'user';
                        error_log("User login success: id={$row['id']}");
                        header("Location: index.php");
                        exit;
                    } else {
                        error_log("User password mismatch: email=$email, stored_hash={$row['password']}");
                        echo "<script>alert('Invalid credentials!');</script>";
                    }
                } else {
                    error_log("User not found: email=$email");
                    echo "<script>alert('User not found!');</script>";
                }
                $stmt->close();
            }
        }
    } elseif ($type == 'vendor') {
        $stmt = $conn->prepare("SELECT id, password FROM vendors WHERE email = ?");
        if ($stmt === false) {
            error_log("Vendor query prepare failed: " . $conn->error);
            echo "<script>alert('Database error. Please try again later.');</script>";
        } else {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Vendor query execute failed: " . $stmt->error);
                echo "<script>alert('Database error. Please try again later.');</script>";
            } else {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['vendor_id'] = $row['id'];
                        $_SESSION['user_type'] = 'vendor';
                        error_log("Vendor login success: id={$row['id']}");
                        header("Location: vendor/function_hall_dashboard.php");
                        exit;
                    } else {
                        error_log("Vendor password mismatch: email=$email, stored_hash={$row['password']}");
                        echo "<script>alert('Invalid credentials!');</script>";
                    }
                } else {
                    error_log("Vendor not found: email=$email");
                    echo "<script>alert('Vendor not found!');</script>";
                }
                $stmt->close();
            }
        }
    } elseif ($type == 'admin') {
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
        if ($stmt === false) {
            error_log("Admin query prepare failed: " . $conn->error);
            echo "<script>alert('Database error. Please try again later.');</script>";
        } else {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Admin query execute failed: " . $stmt->error);
                echo "<script>alert('Database error. Please try again later.');</script>";
            } else {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    error_log("Admin found: email=$email, stored_hash={$row['password']}, hash_length=" . strlen($row['password']));
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['admin_id'] = $row['id'];
                        $_SESSION['user_type'] = 'admin';
                        error_log("Admin login success: id={$row['id']}, session=" . print_r($_SESSION, true));
                        header("Location: admin/dashboard.php");
                        exit;
                    } else {
                        error_log("Admin password mismatch: email=$email");
                        echo "<script>alert('Invalid credentials!');</script>";
                    }
                } else {
                    error_log("Admin not found: email=$email");
                    echo "<script>alert('Admin not found!');</script>";
                }
                $stmt->close();
            }
        }
    } else {
        error_log("Invalid type: $type");
        echo "<script>alert('Invalid login type!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Login Section with Background Image -->
    <section class="login-hero">
        <div class="login-content">
            <h1>Login to Your Account</h1>
            <p>Access Your Wedding Planning Dashboard</p>
            <form method="POST" class="login-form">
                <input type="hidden" name="type" id="type" value="user">
                <select id="login-type" name="login-type" onchange="updateForm()" required>
                    <option value="user">User</option>
                    <option value="vendor">Vendor</option>
                    <option value="admin">Admin</option>
                </select>
                <input type="text" name="email" placeholder="Email (for User/Vendor) or Username (for Admin)" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login <i class="fas fa-sign-in-alt"></i></button>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
    <script>
        function updateForm() {
            const type = document.getElementById('login-type').value;
            document.getElementById('type').value = type;
            console.log("Login type set to: " + type);
        }
    </script>
</body>
</html>