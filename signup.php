<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if ($role === 'user') {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $password);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        $_SESSION['user_id'] = $user_id;
        header("Location: index.php");
        exit;
    } else {
        $business_name = $_POST['business_name'];
        $category = $_POST['category'];
        $price_per_day = $_POST['price_per_day'];
        $price_per_marriage = $_POST['price_per_marriage'];
        $capacity = $_POST['capacity'];
        $parking = $_POST['parking'];
        $acres = $_POST['acres'];
        $advance_payment = $_POST['advance_payment'];
        $description = $_POST['description'];
        $location = $_POST['location'];
        $bank_name = $_POST['bank_name'];
        $account_holder = $_POST['account_holder'];
        $account_number = $_POST['account_number'];
        $ifsc_code = $_POST['ifsc_code'];

        $images = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_FILES["image$i"]) && $_FILES["image$i"]['name']) {
                $target = "images/" . basename($_FILES["image$i"]['name']);
                move_uploaded_file($_FILES["image$i"]['tmp_name'], $target);
                $images[] = basename($_FILES["image$i"]['name']);
            }
        }
        $images_json = json_encode($images);

        $video = '';
        if (isset($_FILES['video']) && $_FILES['video']['name']) {
            $target = "images/" . basename($_FILES['video']['name']);
            move_uploaded_file($_FILES['video']['tmp_name'], $target);
            $video = basename($_FILES['video']['name']);
        }

        $stmt = $conn->prepare("INSERT INTO vendors (business_name, category, price_per_day, price_per_marriage, capacity, parking, acres, advance_payment, description, location, images, video, bank_name, account_holder, account_number, ifsc_code, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddisddssssssssssss", $business_name, $category, $price_per_day, $price_per_marriage, $capacity, $parking, $acres, $advance_payment, $description, $location, $images_json, $video, $bank_name, $account_holder, $account_number, $ifsc_code, $full_name, $email, $phone, $password);
        $stmt->execute();
        $vendor_id = $stmt->insert_id;
        $stmt->close();

        $_SESSION['vendor_id'] = $vendor_id;
        header("Location: vendor/function_hall_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="signup">
        <h2>Join Our Platform</h2>
        <p>Sign up as a user or vendor to start planning or offering wedding services.</p>
        <form method="POST" enctype="multipart/form-data" class="signup-form">
            <div class="form-step">
                <h3>Step 1: Account Type</h3>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="vendor">Vendor</option>
                </select>
            </div>
            <div class="form-step">
                <h3>Step 2: Personal Details</h3>
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div id="vendor-fields" style="display: none;" class="form-step">
                <h3>Step 3: Vendor Details</h3>
                <input type="text" name="business_name" placeholder="Business Name">
                <select name="category">
                    <option value="function_hall">Function Hall</option>
                    <option value="invitation">Invitation</option>
                    <option value="photography">Photography & Videography</option>
                    <option value="beauty">Beauty & Makeup</option>
                    <option value="catering">Catering</option>
                    <option value="dance">Dance</option>
                    <option value="decoration">Decoration</option>
                    <option value="music">Music Band</option>
                    <option value="transport">Transport Vehicle</option>
                    <option value="cake">Wedding Cake</option>
                </select>
                <input type="number" name="price_per_day" placeholder="Price per Day" step="0.01">
                <input type="number" name="price_per_marriage" placeholder="Price per Marriage" step="0.01">
                <input type="number" name="capacity" placeholder="Capacity">
                <select name="parking">
                    <option value="yes">Parking: Yes</option>
                    <option value="no">Parking: No</option>
                </select>
                <input type="number" name="acres" placeholder="Acres" step="0.01">
                <input type="number" name="advance_payment" placeholder="Advance Payment" step="0.01">
                <input type="text" name="location" placeholder="Location">
                <textarea name="description" placeholder="Description"></textarea>
                <input type="text" name="bank_name" placeholder="Bank Name">
                <input type="text" name="account_holder" placeholder="Account Holder">
                <input type="text" name="account_number" placeholder="Account Number">
                <input type="text" name="ifsc_code" placeholder="IFSC Code">
                <h4>Upload Media</h4>
                <input type="file" name="image1" accept="image/*">
                <input type="file" name="image2" accept="image/*">
                <input type="file" name="image3" accept="image/*">
                <input type="file" name="image4" accept="image/*">
                <input type="file" name="image5" accept="image/*">
                <input type="file" name="video" accept="video/*">
                <div class="signup-slider"></div>
            </div>
            <div class="form-step">
                <h3>Step 4: Submit</h3>
                <button type="submit"><i class="fas fa-user-plus"></i> Sign Up</button>
            </div>
        </form>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        document.querySelector('select[name="role"]').addEventListener('change', function() {
            document.getElementById('vendor-fields').style.display = this.value === 'vendor' ? 'block' : 'none';
        });

        const inputs = document.querySelectorAll('input[type="file"]');
        const slider = document.querySelector('.signup-slider');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                slider.innerHTML = '';
                inputs.forEach(inp => {
                    if (inp.files[0]) {
                        const file = inp.files[0];
                        const url = URL.createObjectURL(file);
                        if (file.type.startsWith('image/')) {
                            slider.innerHTML += `<div><img src="${url}" alt="Preview"></div>`;
                        } else if (file.type.startsWith('video/')) {
                            slider.innerHTML += `<div><video src="${url}" controls></video></div>`;
                        }
                    }
                });
                if (slider.innerHTML) {
                    $(slider).slick({
                        dots: true,
                        infinite: true,
                        speed: 300,
                        slidesToShow: 1,
                        adaptiveHeight: true,
                        arrows: true
                    });
                }
            });
        });
    </script>
</body>
</html>