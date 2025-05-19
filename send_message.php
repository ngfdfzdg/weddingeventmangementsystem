<?php
session_start();
include 'includes/db_connect.php';

$max_file_size = 10 * 1024 * 1024; // 10MB
$allowed_types = ['image/jpeg', 'image/png', 'video/mp4'];
$upload_dir = 'Uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['vendor_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sender_id = $_SESSION['user_id'] ?? $_SESSION['vendor_id'];
$sender_type = isset($_SESSION['user_id']) ? 'user' : 'vendor';
$receiver_id = $_POST['vendor_id'] ?? $_POST['user_id'] ?? null;
$receiver_type = $sender_type === 'user' ? 'vendor' : 'user';
$message = trim($_POST['message'] ?? '');
$media_type = 'text';
$media_path = null;

if (!$receiver_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Receiver ID is required']);
    exit;
}

// Validate receiver_id exists in the appropriate table
if ($receiver_type === 'vendor') {
    $stmt = $conn->prepare("SELECT id FROM vendors WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
}
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    http_response_code(400);
    echo json_encode(['error' => 'Invalid receiver ID']);
    exit;
}
$stmt->close();

if (isset($_FILES['media']) && $_FILES['media']['size'] > 0) {
    $file = $_FILES['media'];
    if ($file['size'] > $max_file_size) {
        http_response_code(400);
        echo json_encode(['error' => 'File size exceeds 10MB limit']);
        exit;
    }
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Allowed: JPG, PNG, MP4']);
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $destination = $upload_dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
        exit;
    }

    $media_type = strpos($file['type'], 'image') === 0 ? 'image' : 'video';
    $media_path = $destination;
}

if (empty($message) && empty($media_path)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message or media is required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, sender_type, receiver_type, message, media_type, media_path, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("iisssss", $sender_id, $receiver_id, $sender_type, $receiver_type, $message, $media_type, $media_path);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>