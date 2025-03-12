<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$records_per_page = 5;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Build queries dynamically
$total_query = "SELECT COUNT(*) FROM products WHERE deleted_at IS NULL AND name LIKE ?";
$sql = "SELECT * FROM products WHERE deleted_at IS NULL AND name LIKE ?";

if ($category !== null) {
    $total_query .= " AND category = ?";
    $sql .= " AND category = ?";
}

$sql .= " LIMIT ?, ?";

// Fetch total products
$stmt = $conn->prepare($total_query);
if (!$stmt) {
    die(json_encode(["error" => "Prepare failed: " . $conn->error]));
}
if ($category !== null) {
    $stmt->bind_param("ss", $search, $category);
} else {
    $stmt->bind_param("s", $search);
}
if (!$stmt->execute()) {
    die(json_encode(["error" => "Execute failed: " . $stmt->error]));
}
$total_rows = $stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);
$stmt->close();

// Fetch products
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Prepare failed: " . $conn->error]));
}
if ($category !== null) {
    $stmt->bind_param("ssii", $search, $category, $offset, $records_per_page);
} else {
    $stmt->bind_param("sii", $search, $offset, $records_per_page);
}
if (!$stmt->execute()) {
    die(json_encode(["error" => "Execute failed: " . $stmt->error]));
}
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Return JSON response
echo json_encode(["products" => $products, "total_pages" => $total_pages]);
$conn->close();
?>