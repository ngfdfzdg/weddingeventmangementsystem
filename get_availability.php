<?php
include 'includes/db_connect.php';

$vendor_id = $_GET['vendor_id'];
$stmt = $conn->prepare("SELECT date, status FROM availability WHERE vendor_id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

$availability = [];
while ($row = $result->fetch_assoc()) {
    $availability[] = [
        'date' => $row['date'],
        'status' => $row['status']
    ];
}

echo json_encode($availability);
$stmt->close();
$conn->close();
?>