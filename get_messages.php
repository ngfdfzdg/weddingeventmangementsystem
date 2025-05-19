<?php
session_start();
include 'includes/db_connect.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$vendor_id = isset($_GET['vendor_id']) ? (int)$_GET['vendor_id'] : 0;
$perspective = isset($_GET['perspective']) ? $_GET['perspective'] : 'user'; // 'user' or 'vendor'

if ($user_id == 0 || $vendor_id == 0) {
    echo json_encode(['error' => 'Invalid user or vendor ID']);
    exit;
}

$stmt = $conn->prepare("
    SELECT m.*, 
           CASE 
               WHEN m.sender_type = 'user' THEN u.full_name 
               WHEN m.sender_type = 'vendor' THEN v.business_name 
               ELSE 'Unknown' 
           END AS sender_name
    FROM messages m 
    LEFT JOIN users u ON m.sender_id = u.id AND m.sender_type = 'user'
    LEFT JOIN vendors v ON m.sender_id = v.id AND m.sender_type = 'vendor'
    WHERE (m.sender_id = ? AND m.receiver_id = ? AND m.sender_type = 'user' AND m.receiver_type = 'vendor') 
       OR (m.sender_id = ? AND m.receiver_id = ? AND m.sender_type = 'vendor' AND m.receiver_type = 'user') 
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiii", $user_id, $vendor_id, $vendor_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
while ($row = $result->fetch_assoc()) {
    // Determine if the message is from the logged-in user/vendor
    $is_sender = ($perspective == 'user' && $row['sender_type'] == 'user' && $row['sender_id'] == $user_id) ||
                 ($perspective == 'vendor' && $row['sender_type'] == 'vendor' && $row['sender_id'] == $vendor_id);
    $class = $is_sender ? 'sender-message' : 'receiver-message';
    $sender = htmlspecialchars($row['sender_name']);
    
    $output .= "<div class='$class'>";
    $output .= "<p><strong>$sender</strong>: " . htmlspecialchars($row['message']) . "</p>";
    
    if (!empty($row['media_path']) && !empty($row['media_type'])) {
        if ($row['media_type'] == 'image') {
            $output .= "<img src='" . htmlspecialchars($row['media_path']) . "' alt='Media' style='max-width: 100%;'>";
        } elseif ($row['media_type'] == 'video') {
            $output .= "<video src='" . htmlspecialchars($row['media_path']) . "' controls style='max-width: 100%;'></video>";
        }
    }
    
    $output .= "<span>" . htmlspecialchars($row['created_at']) . "</span>";
    $output .= "</div>";
}

echo $output;
$stmt->close();
$conn->close();
?>