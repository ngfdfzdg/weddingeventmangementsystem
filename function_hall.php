<?php
session_start();
include 'includes/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to login if vendor_id is set and user is not logged in
if (isset($_GET['vendor_id']) && !isset($_SESSION['user_id'])) {
    echo "<script>alert('Please signup/login!'); window.location='login.php';</script>";
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['message'])) {
        $vendor_id = $_POST['vendor_id'];
        $message = $_POST['message'];
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, sender_type, receiver_type, message, is_read) VALUES (?, ?, 'user', 'vendor', ?, 0)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iis", $_SESSION['user_id'], $vendor_id, $message);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Message sent!');</script>";
    } elseif (isset($_POST['cart'])) {
        $vendor_id = $_POST['vendor_id'];
        $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND vendor_id = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $_SESSION['user_id'], $vendor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<script>alert('Already added to cart!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, vendor_id) VALUES (?, ?)");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $_SESSION['user_id'], $vendor_id);
            $stmt->execute();
            echo "<script>alert('Added to cart!');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['book'])) {
        $vendor_id = $_POST['vendor_id'];
        $booking_date = $_POST['booking_date'];
        $advance_payment = $_POST['advance_payment'];

        $stmt = $conn->prepare("SELECT id FROM bookings WHERE vendor_id = ? AND booking_date = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("is", $vendor_id, $booking_date);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<script>alert('This date is already booked!');</script>";
        } else {
            $_SESSION['booking_data'] = [
                'vendor_id' => $vendor_id,
                'booking_date' => $booking_date,
                'advance_payment' => $advance_payment
            ];
            header("Location: payment.php");
            exit;
        }
        $stmt->close();
    } elseif (isset($_POST['rating'])) {
        $vendor_id = $_POST['vendor_id'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        $stmt = $conn->prepare("INSERT INTO feedback (user_id, vendor_id, rating, comment) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiis", $_SESSION['user_id'], $vendor_id, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Feedback submitted!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Function Hall - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="function-hall">
        <h2>Function Hall Booking</h2>
        <?php if (!isset($_GET['vendor_id'])): ?>
        <form class="filters">
            <input type="text" name="search" placeholder="Search by name" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <input type="number" name="capacity" placeholder="Capacity" value="<?php echo isset($_GET['capacity']) ? htmlspecialchars($_GET['capacity']) : ''; ?>">
            <select name="parking">
                <option value="">Parking</option>
                <option value="yes" <?php echo isset($_GET['parking']) && $_GET['parking'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
                <option value="no" <?php echo isset($_GET['parking']) && $_GET['parking'] == 'no' ? 'selected' : ''; ?>>No</option>
            </select>
            <input type="number" name="budget" placeholder="Budget" value="<?php echo isset($_GET['budget']) ? htmlspecialchars($_GET['budget']) : ''; ?>">
            <input type="number" name="rating" placeholder="Rating (1-5)" value="<?php echo isset($_GET['rating']) ? htmlspecialchars($_GET['rating']) : ''; ?>">
            <input type="text" name="location" placeholder="Location" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
            <button type="submit">Filter</button>
        </form>
        <div class="vendor-grid">
            <?php
            $query = "SELECT v.*, (SELECT AVG(rating) FROM feedback WHERE vendor_id = v.id) AS avg_rating FROM vendors v WHERE category = 'function_hall'";
            $conditions = [];

            // Search by name
            if (isset($_GET['search']) && trim($_GET['search']) !== '') {
                $conditions[] = "business_name LIKE '%" . $conn->real_escape_string(trim($_GET['search'])) . "%'";
            }

            // Capacity filter
            if (isset($_GET['capacity']) && trim($_GET['capacity']) !== '') {
                $conditions[] = "capacity >= " . (int)$_GET['capacity'];
            }

            // Parking filter
            if (isset($_GET['parking']) && trim($_GET['parking']) !== '') {
                $conditions[] = "parking = '" . $conn->real_escape_string($_GET['parking']) . "'";
            }

            // Budget filter
            if (isset($_GET['budget']) && trim($_GET['budget']) !== '') {
                $conditions[] = "price_per_day <= " . (float)$_GET['budget'];
            }

            // Rating filter
            if (isset($_GET['rating']) && trim($_GET['rating']) !== '') {
                $conditions[] = "(SELECT AVG(rating) FROM feedback WHERE vendor_id = v.id) >= " . (int)$_GET['rating'];
            }

            // Location filter
            if (isset($_GET['location']) && trim($_GET['location']) !== '') {
                $conditions[] = "LOWER(location) LIKE '%" . $conn->real_escape_string(strtolower(trim($_GET['location']))) . "%'";
            }

            // Combine conditions
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            $result = $conn->query($query);
            if ($result === false) {
                die("Query failed: " . $conn->error);
            }
            if ($result->num_rows === 0) {
                echo "<p>No function halls found for the specified criteria.</p>";
            } else {
                while ($row = $result->fetch_assoc()) {
                    $images = json_decode($row['images'], true);
                    $image = is_array($images) && !empty($images) ? $images[0] : 'placeholder.jpg';
                    $avg_rating = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 'No ratings';
                    ?>
                    <a href="function_hall.php?vendor_id=<?php echo $row['id']; ?>" class="vendor-box">
                        <img src="images/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($row['business_name']); ?>">
                        <h3><?php echo htmlspecialchars($row['business_name']); ?></h3>
                        <p>Price: $<?php echo htmlspecialchars($row['price_per_day']); ?>/day</p>
                        <p>Capacity: <?php echo htmlspecialchars($row['capacity']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($row['location']); ?></p>
                        <p>Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                        <span class="more-details-btn">More Details</span>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
        <?php else: ?>
        <?php
        // Validate vendor_id
        $vendor_id = filter_input(INPUT_GET, 'vendor_id', FILTER_VALIDATE_INT);
        if ($vendor_id === false || $vendor_id <= 0) {
            die("Invalid vendor ID.");
        }

        // Fetch vendor details
        $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $vendor = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$vendor) {
            die("Vendor not found.");
        }

        // Fetch average rating
        $stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating FROM feedback WHERE vendor_id = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $rating_result = $stmt->get_result()->fetch_assoc();
        $avg_rating = $rating_result['avg_rating'] ? number_format($rating_result['avg_rating'], 1) : 'No ratings';
        $stmt->close();

        // Fetch feedback comments
        $feedback_query = $conn->prepare("SELECT f.comment, f.rating, u.full_name FROM feedback f JOIN users u ON f.user_id = u.id WHERE f.vendor_id = ?");
        if ($feedback_query === false) {
            die("Prepare failed: " . $conn->error);
        }
        $feedback_query->bind_param("i", $vendor_id);
        $feedback_query->execute();
        $feedback_result = $feedback_query->get_result();
        $feedbacks = $feedback_result->fetch_all(MYSQLI_ASSOC);
        $feedback_query->close();
        ?>
        <div class="vendor-details">
            <div class="vendor-details-container">
                <div class="vendor-slider">
                    <?php
                    $images = json_decode($vendor['images'], true);
                    if (is_array($images) && !empty($images)) {
                        foreach ($images as $index => $image) {
                            ?>
                            <div>
                                <img src="images/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Vendor Image" 
                                     class="clickable-image" 
                                     data-fullscreen="images/<?php echo htmlspecialchars($image); ?>">
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div><img src='images/placeholder.jpg' alt='No Image Available'></div>";
                    }
                    if ($vendor['video']) {
                        ?>
                        <div><video src="<?php echo htmlspecialchars($vendor['video']); ?>" controls></video></div>
                        <?php
                    }
                    ?>
                </div>
                <div class="vendor-info">
                    <h3><?php echo htmlspecialchars($vendor['business_name']); ?></h3>
                    <div class="vendor-info-item">
                        <span class="label">Rating:</span>
                        <span class="value"><?php echo $avg_rating; ?> <i class="fas fa-star"></i></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Price per Day:</span>
                        <span class="value">$<?php echo htmlspecialchars($vendor['price_per_day']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Price per Marriage:</span>
                        <span class="value">$<?php echo htmlspecialchars($vendor['price_per_marriage']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Capacity:</span>
                        <span class="value"><?php echo htmlspecialchars($vendor['capacity']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Parking:</span>
                        <span class="value"><?php echo htmlspecialchars($vendor['parking']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Acres:</span>
                        <span class="value"><?php echo htmlspecialchars($vendor['acres']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Advance Payment:</span>
                        <span class="value">$<?php echo htmlspecialchars($vendor['advance_payment']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Location:</span>
                        <span class="value"><?php echo htmlspecialchars($vendor['location']); ?></span>
                    </div>
                    <div class="vendor-info-item">
                        <span class="label">Description:</span>
                        <span class="value"><?php echo htmlspecialchars($vendor['description']); ?></span>
                    </div>
                    <!-- Book Now and Add to Cart Side by Side -->
                    <div class="vendor-info-actions">
                        <form method="POST" class="action-form book-now-form">
                            <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                            <input type="date" name="booking_date" required>
                            <input type="hidden" name="advance_payment" value="<?php echo $vendor['advance_payment']; ?>">
                            <button type="submit" name="book">Book Now</button>
                            <p class="action-rating">Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                        </form>
                        <form method="POST" class="action-form add-to-cart-form">
                            <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                            <button type="submit" name="cart">Add to Cart</button>
                            <p class="action-rating">Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Feedback Section -->
            <?php if (!empty($feedbacks)): ?>
            <div class="feedback-section">
                <h3>Customer Feedback</h3>
                <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-item">
                    <p><strong><?php echo htmlspecialchars($feedback['full_name']); ?> (Rating: <?php echo $feedback['rating']; ?> <i class="fas fa-star"></i>)</strong></p>
                    <p><?php echo htmlspecialchars($feedback['comment']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <!-- Vendor Actions -->
            <div class="vendor-actions">
                <!-- Check Availability -->
                <div class="availability">
                    <button type="button" id="show-calendar">Check Availability</button>
                    <p class="action-rating">Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                    <div id="calendar" class="calendar"></div>
                </div>
                <!-- Message -->
                <form method="POST" class="action-form">
                    <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                    <textarea name="message" placeholder="Send a message" required></textarea>
                    <button type="submit">Message</button>
                    <p class="action-rating">Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                </form>
                <!-- Submit Feedback -->
                <form method="POST" class="action-form">
                    <input type="hidden" name="vendor_id" value="<?php echo $vendor['id']; ?>">
                    <div class="star-rating">
                        <input type="radio" name="rating" value="5" id="star5" required><label for="star5"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" value="4" id="star4"><label for="star4"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" value="3" id="star3"><label for="star3"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" value="2" id="star2"><label for="star2"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" value="1" id="star1"><label for="star1"><i class="fas fa-star"></i></label>
                    </div>
                    <textarea name="comment" placeholder="Your feedback" required></textarea>
                    <button type="submit">Submit Feedback</button>
                    <p class="action-rating">Rating: <?php echo $avg_rating; ?> <i class="fas fa-star"></i></p>
                </form>
            </div>
            <div id="imageModal" class="image-modal">
                <span class="close-modal">×</span>
                <img class="modal-content" id="modalImage">
            </div>
        </div>
        <?php endif; ?>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.vendor-slider').slick({
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 1,
                adaptiveHeight: true,
                arrows: true
            });

            const calendarEl = document.getElementById('calendar');
            const showCalendarBtn = document.getElementById('show-calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: '600px', // Reduced height
                contentHeight: 'auto',
                fixedWeekCount: false,
                selectable: true,
                select: function(info) {
                    const today = new Date('2025-04-24');
                    const selectedDate = new Date(info.startStr);
                    if (selectedDate < today.setHours(0, 0, 0, 0)) {
                        alert('Please choose a future date!');
                        return;
                    }
                    document.querySelector('input[name="booking_date"]').value = info.startStr;
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('get_availability.php?vendor_id=<?php echo isset($vendor_id) ? $vendor_id : 0; ?>')
                        .then(response => response.json())
                        .then(data => {
                            const events = data.map(item => ({
                                title: item.status === 'booked' ? 'Booked' : 
                                       item.status === 'quota_full' ? 'Quota Full' : 
                                       item.status === 'quota_not_released' ? 'Quota Not Released' : 
                                       item.status === 'seva_not_performed' ? 'Seva Not Performed' : 'Available',
                                start: item.date,
                                className: item.status === 'booked' ? 'booked' :
                                           item.status === 'quota_full' ? 'quota-full' :
                                           item.status === 'quota_not_released' ? 'quota-not-released' :
                                           item.status === 'seva_not_performed' ? 'seva-not-performed' : 'available'
                            }));
                            successCallback(events);
                        })
                        .catch(error => failureCallback(error));
                }
            });

            showCalendarBtn.addEventListener('click', () => {
                calendarEl.style.display = calendarEl.style.display === 'block' ? 'none' : 'block';
                if (calendarEl.style.display === 'block') {
                    calendar.render();
                }
            });

            const bookingDateInput = document.querySelector('input[name="booking_date"]');
            const today = new Date('2025-04-24').toISOString().split('T')[0];
            bookingDateInput.setAttribute('min', today);
            bookingDateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const todayDate = new Date('2025-04-24');
                if (selectedDate < todayDate.setHours(0, 0, 0, 0)) {
                    alert('Please choose a future date!');
                    this.value = '';
                }
            });
        });
    </script>
</body>
</html>