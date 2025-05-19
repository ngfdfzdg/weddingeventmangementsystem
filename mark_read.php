<?php
include 'includes/db_connect.php';

$user_id = $_POST['user_id'] ?? null;
$vendor_id = $_POST['vendor_id'] ?? null;
$perspective = $_POST['perspective'] ?? 'user'; // 'user' or 'vendor'

if (!$user_id || !$vendor_id) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID and Vendor ID are required']);
    exit;
}

if ($perspective == 'user') {
    // User interface: mark vendor-to-user messages as read
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND sender_type = 'vendor' AND receiver_type = 'user' AND is_read = 0");
    $stmt->bind_param("ii", $vendor_id, $user_id);
} else {
    // Vendor interface: mark user-to-vendor messages as read
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND sender_type = 'user' AND receiver_type = 'vendor' AND is_read = 0");
    $stmt->bind_param("ii", $user_id, $vendor_id);
}

$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode(['success' => true]);
?>