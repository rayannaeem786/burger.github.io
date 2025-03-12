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

    $conn->begin_transaction();
    try {
        // Step 1: Delete related sales records
        $sales_stmt = $conn->prepare("DELETE FROM sales WHERE product_id = ?");
        $sales_stmt->bind_param("i", $product_id);
        $sales_stmt->execute();

        // Step 2: Permanently delete the product
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $conn->commit();
        echo 'success';
    } catch (Exception $e) {
        $conn->rollback();
        echo 'Error deleting product permanently: ' . $e->getMessage();
    }

    $sales_stmt->close();
    $stmt->close();
} else {
    echo 'Invalid product ID!';
}
$conn->close();
?>