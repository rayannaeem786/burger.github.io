<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo 'unauthorized';
    exit();
}

// Check if ID is provided via POST
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $product_id = (int)$_POST['id'];

    // Use prepared statement to prevent SQL injection
    $delete_product_sql = "UPDATE products SET deleted_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($delete_product_sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error moving product to trash: ' . $conn->error;
    }
    $stmt->close();
} else {
    echo 'Invalid product ID!';
}
$conn->close();
?>