<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Wedding Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
    <section class="messages">
        <div class="messages-container">
            <div class="user-list">
                <div class="filters">
                    <button class="filter-btn" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="read">Read</button>
                    <button class="filter-btn" data-filter="unread">Unread</button>
                </div>
                <ul id="vendor-list">
                    <?php
                    $stmt = $conn->prepare("SELECT v.id, v.business_name, COUNT(m.id) as unread_count 
                                            FROM vendors v 
                                            LEFT JOIN messages m ON m.sender_id = v.id AND m.receiver_id = ? AND m.is_read = 0 
                                            WHERE EXISTS (
                                                SELECT 1 FROM messages 
                                                WHERE (sender_id = v.id AND receiver_id = ?) 
                                                   OR (sender_id = ? AND receiver_id = v.id)
                                            ) 
                                            GROUP BY v.id");
                    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<li data-vendor-id='{$row['id']}' class='user-item'>";
                        echo htmlspecialchars($row['business_name']);
                        if ($row['unread_count'] > 0) {
                            echo "<span class='unread-count'>{$row['unread_count']}</span>";
                        }
                        echo "</li>";
                    }
                    $stmt->close();
                    ?>
                </ul>
            </div>
            <div class="chat-window">
                <div id="chat-header"></div>
                <div id="chat-messages"></div>
                <form id="reply-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="vendor_id" id="chat-vendor-id">
                    <textarea name="message" placeholder="Type a message"></textarea>
                    <input type="file" name="media" accept="image/jpeg,image/png,video/mp4" id="media-upload">
                    <label for="media-upload" class="upload-btn"><i class="fas fa-paperclip"></i></label>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vendorItems = document.querySelectorAll('.user-item');
            const chatHeader = document.getElementById('chat-header');
            const chatMessages = document.getElementById('chat-messages');
            const replyForm = document.getElementById('reply-form');
            const chatVendorId = document.getElementById('chat-vendor-id');

            function loadMessages(vendorId, vendorName) {
                chatHeader.innerHTML = `<h3>${vendorName}</h3>`;
                chatVendorId.value = vendorId;
                $.ajax({
                    url: 'get_messages.php',
                    method: 'GET',
                    data: { user_id: <?php echo $user_id; ?>, vendor_id: vendorId, perspective: 'user' },
                    dataType: 'html',
                    success: function(data) {
                        chatMessages.innerHTML = data;
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                        $.ajax({
                            url: 'mark_read.php',
                            method: 'POST',
                            data: { user_id: <?php echo $user_id; ?>, vendor_id: vendorId, perspective: 'user' },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    document.querySelector(`li[data-vendor-id="${vendorId}"] .unread-count`)?.remove();
                                }
                            },
                            error: function(xhr) {
                                console.error('Error marking messages as read:', xhr.responseText);
                            }
                        });
                    },
                    error: function(xhr) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            alert('Error loading messages: ' + response.error);
                        } catch (e) {
                            alert('Error loading messages: ' + xhr.responseText);
                        }
                    }
                });
            }

            vendorItems.forEach(item => {
                item.addEventListener('click', function() {
                    const vendorId = this.getAttribute('data-vendor-id');
                    const vendorName = this.textContent.replace(/\d+$/, '');
                    loadMessages(vendorId, vendorName);
                    vendorItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            replyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!chatVendorId.value) {
                    alert('Please select a vendor to send a message.');
                    return;
                }
                const formData = new FormData(this);
                $.ajax({
                    url: 'send_message.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            loadMessages(chatVendorId.value, chatHeader.textContent);
                            replyForm.reset();
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function(xhr) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            alert('Error: ' + response.error);
                        } catch (e) {
                            alert('Error: ' + xhr.responseText);
                        }
                    }
                });
            });

            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    $.ajax({
                        url: 'filter_vendors.php',
                        method: 'GET',
                        data: { filter: filter, user_id: <?php echo $user_id; ?> },
                        success: function(data) {
                            document.getElementById('vendor-list').innerHTML = data;
                            const newVendorItems = document.querySelectorAll('.user-item');
                            newVendorItems.forEach(item => {
                                item.addEventListener('click', function() {
                                    const vendorId = this.getAttribute('data-vendor-id');
                                    const vendorName = this.textContent.replace(/\d+$/, '');
                                    loadMessages(vendorId, vendorName);
                                    newVendorItems.forEach(i => i.classList.remove('active'));
                                    this.classList.add('active');
                                });
                            });
                        },
                        error: function(xhr) {
                            alert('Error filtering vendors: ' + xhr.responseText);
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>