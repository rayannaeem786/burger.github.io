<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid
    $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];

        if (isset($_POST['reset'])) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            // Update the password and clear the reset token
            $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_password, $email);
            $stmt->execute();

            echo "Your password has been reset. <a href='login.php'>Login here</a>.";
        }
    } else {
        echo "Invalid or expired token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Password</h1>
    <form method="POST" action="">
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit" name="reset">Reset Password</button>
    </form>
</body>
</html>