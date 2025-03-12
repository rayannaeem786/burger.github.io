<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data) || !isset($data['id'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid or missing data']);
  exit();
}

$id = (int)$data['id'];
$name = trim($data['name'] ?? '');
$category = trim($data['category'] ?? '');
$price = (float)($data['price'] ?? 0);
$quantity = (int)($data['quantity'] ?? 0);
$location = trim($data['location'] ?? '');

if ($id <= 0 || empty($name) || $price < 0 || $quantity < 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid input values']);
  exit();
}

$sql = "UPDATE products SET name = ?, category = ?, price = ?, quantity = ?, location = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
  echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
  exit();
}

$stmt->bind_param("ssdisi", $name, $category, $price, $quantity, $location, $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'No product found with the given ID']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>