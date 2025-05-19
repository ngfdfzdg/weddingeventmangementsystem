<?php
$host = 'localhost'; // Try '127.0.0.1' or 'localhost:3306' if needed
$username = 'root';
$password = ''; // Update if password is set
$database = 'wedding_management';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>