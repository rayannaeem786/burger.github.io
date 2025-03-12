<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo 'unauthorized';
    exit();
}

if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $product_id = (int)$_POST['id'];

    // Restore product by setting deleted_at to NULL
    $restore_sql = "UPDATE products SET deleted_at = NULL WHERE id = ?";
    $stmt = $conn->prepare($restore_sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error restoring product: ' . $conn->error;
    }
    $stmt->close();
} else {
    echo 'Invalid product ID!';
}
$conn->close();
?>