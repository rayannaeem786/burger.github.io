<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Clear the remember_token and set last_activity to NULL
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, last_activity = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Expire the remember_me cookie
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, "/", "", true, true); // Expire the cookie
    }
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>