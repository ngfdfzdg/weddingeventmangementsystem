<?php
include 'includes/db_connect.php';

$user_id = $_GET['user_id'] ?? null;
$filter = $_GET['filter'] ?? 'all';

if (!$user_id) {
    echo '';
    exit;
}

$query = "SELECT v.id, v.business_name, COUNT(m.id) as unread_count 
          FROM vendors v 
          LEFT JOIN messages m ON m.sender_id = v.id AND m.receiver_id = ? AND m.is_read = 0 
          WHERE EXISTS (
              SELECT 1 FROM messages 
              WHERE (sender_id = v.id AND receiver_id = ?) 
                 OR (sender_id = ? AND receiver_id = v.id)
          )";
if ($filter === 'read') {
    $query .= " AND NOT EXISTS (
                  SELECT 1 FROM messages 
                  WHERE sender_id = v.id AND receiver_id = ? AND is_read = 0
              )";
} elseif ($filter === 'unread') {
    $query .= " AND EXISTS (
                  SELECT 1 FROM messages 
                  WHERE sender_id = v.id AND receiver_id = ? AND is_read = 0
              )";
}
$query .= " GROUP BY v.id";

$stmt = $conn->prepare($query);
if ($filter === 'read' || $filter === 'unread') {
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
} else {
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$vendors = '';
while ($row = $result->fetch_assoc()) {
    $vendors .= "<li data-vendor-id='{$row['id']}' class='user-item'>";
    $vendors .= htmlspecialchars($row['business_name']);
    if ($row['unread_count'] > 0) {
        $vendors .= "<span class='unread-count'>{$row['unread_count']}</span>";
    }
    $vendors .= "</li>";
}
$stmt->close();

echo $vendors;
$conn->close();
?>