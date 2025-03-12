<?php
session_start();
include 'config.php'; // Include database configuration
include 'email_config.php'; // Include email configuration (if needed)
require 'vendor/autoload.php'; // Include PHPMailer autoload (if needed)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize error and success messages
$error_message = '';
$success_message = '';

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']); // Trim whitespace
    $email = trim($_POST['email']); // Trim whitespace
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the user already exists in the database (including rejected status)
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // If the user is rejected, prevent re-registration
            if ($row['status'] == 'rejected') {
                $error_message = "Your account was rejected, you cannot register again.";
            } else {
                $error_message = "Username or Email already exists.";
            }
        } else {
            // Insert the new user as pending
            $sql = "INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Successful registration
                $success_message = "Registration successful. Please wait for approval.";

                // Send email notification to admin (optional)
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USER;
                    $mail->Password   = SMTP_PASS;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = SMTP_PORT;

                    // Recipients
                    $mail->setFrom(SMTP_USER, 'Your Application');
                    $mail->addAddress(ADMIN_EMAIL); // Admin email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'New User Registration - Pending Approval';
                    $mail->Body    = "A new user has registered and is pending approval.<br><br>"
                                   . "Username: $username<br>"
                                   . "Email: $email";

                    $mail->send();
                } catch (Exception $e) {
                    // Log email sending error (optional)
                    error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
                }
            } else {
                // Database error
                $error_message = "Error registering user: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up | Hasna Muscat Trading</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sign up page for user registration.">
    <meta name="keywords" content="signup, register, user registration">
    <meta name="author" content="CodedThemes">

    <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/fonts/feather.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome.css">
    <link rel="stylesheet" href="assets/fonts/material.css">
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="assets/css/style-preset.css">
</head>
<body>
    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="auth-header">
                    <a href="#"><img src="assets/images/logo-dark.svg" alt="img"></a>
                </div>
                <div class="card my-5">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-end mb-4">
                            <h3 class="mb-0"><b>Sign Up</b></h3>
                            <a href="login.php" class="link-primary">Already have an account?</a>
                        </div>

                        <!-- Display error message if any -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <!-- Display success message if any -->
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <!-- Sign Up Form -->
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
                            </div>
                        </form>

                        <div class="saprator mt-3">
                            <span>Sign up with</span>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="d-grid">
                                    <button type="button" class="btn mt-2 btn-light-primary bg-light text-muted">
                                        <img src="assets/images/authentication/google.svg" alt="img"> <span class="d-none d-sm-inline-block"> Google</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid">
                                    <button type="button" class="btn mt-2 btn-light-primary bg-light text-muted">
                                        <img src="assets/images/authentication/twitter.svg" alt="img"> <span class="d-none d-sm-inline-block"> Twitter</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-grid">
                                    <button type="button" class="btn mt-2 btn-light-primary bg-light text-muted">
                                        <img src="assets/images/authentication/facebook.svg" alt="img"> <span class="d-none d-sm-inline-block"> Facebook</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="auth-footer row">
                    <div class="col my-1">
                        <!-- <p class="m-0">Copyright © <a href="#">Codedthemes</a> Distributed by <a href="https://themewagon.com/">ThemeWagon</a></p> -->
                    </div>
                    <div class="col-auto my-1">
                        <ul class="list-inline footer-link mb-0">
                            <li class="list-inline-item"><a href="#">Home</a></li>
                            <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                            <li class="list-inline-item"><a href="#">Contact us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/plugins/popper.min.js"></script>
    <script src="assets/js/plugins/simplebar.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/fonts/custom-font.js"></script>
    <script src="assets/js/pcoded.js"></script>
    <script src="assets/js/plugins/feather.min.js"></script>

    <script>layout_change('light');</script>
    <script>change_box_container('false');</script>
    <script>layout_rtl_change('false');</script>
    <script>preset_change("preset-1");</script>
    <script>font_change("Public-Sans");</script>
</body>
</html>