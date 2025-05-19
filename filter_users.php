<?php
include 'includes/db_connect.php';

$vendor_id = $_GET['vendor_id'] ?? null;
$filter = $_GET['filter'] ?? 'all';

if (!$vendor_id) {
    echo '';
    exit;
}

$query = "SELECT u.id, u.full_name, COUNT(m.id) as unread_count 
          FROM users u 
          LEFT JOIN messages m ON m.sender_id = u.id AND m.receiver_id = ? AND m.is_read = 0 
          WHERE EXISTS (
              SELECT 1 FROM messages 
              WHERE (sender_id = u.id AND receiver_id = ?) 
                 OR (sender_id = ? AND receiver_id = u.id)
          )";
if ($filter === 'read') {
    $query .= " AND NOT EXISTS (
                  SELECT 1 FROM messages 
                  WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0
              )";
} elseif ($filter === 'unread') {
    $query .= " AND EXISTS (
                  SELECT 1 FROM messages 
                  WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0
              )";
}
$query .= " GROUP BY u.id";

$stmt = $conn->prepare($query);
if ($filter === 'read' || $filter === 'unread') {
    $stmt->bind_param("iiii", $vendor_id, $vendor_id, $vendor_id, $vendor_id);
} else {
    $stmt->bind_param("iii", $vendor_id, $vendor_id, $vendor_id);
}
$stmt->execute();
$result = $stmt->get_result();
$users = '';
while ($row = $result->fetch_assoc()) {
    $users .= "<li data-user-id='{$row['id']}' class='user-item'>";
    $users .= htmlspecialchars($row['full_name']);
    if ($row['unread_count'] > 0) {
        $users .= "<span class='unread-count'>{$row['unread_count']}</span>";
    }
    $users .= "</li>";
}
$stmt->close();

echo $users;
$conn->close();
?>