<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode([]);
    exit();
}

// Fetch trashed products
$sql = "SELECT id, name, price, deleted_at FROM products WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']), // Escape output
            'price' => number_format($row['price'], 2), // Format price
            'deleted_at' => $row['deleted_at']
        ];
    }
}

// Set content type to JSON and output data
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>