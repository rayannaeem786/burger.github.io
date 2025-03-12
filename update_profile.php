<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: dashboard.php");
        exit();
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = 'assets/images/user/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old profile picture if it exists and isn't the default
                if (isset($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== 'avatar-2.jpg') {
                    $old_file = 'assets/images/user/' . $_SESSION['profile_picture'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $_SESSION['profile_picture'] = $new_file_name;
            }
        }
    }

    // Update user information in database (without profile_picture column)
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update profile";
    }
    
    $stmt->close();
    header("Location: index.php");
    exit();
}
?>