<?php
ini_set('session.cookie_lifetime', 0);
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Use prepared statements to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Update last activity
        $updateStmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();

        // Check account status
        if ($row['status'] == 'pending') {
            $error_message = "Your account is pending approval. Please contact the administrator.";
        } elseif ($row['status'] == 'rejected') {
            $error_message = "Your account has been rejected. You cannot log in.";
        } elseif (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Check if "Keep me signed in" is selected
            if (isset($_POST['keep_signed_in']) && $_POST['keep_signed_in'] == 'on') {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                // Store token in database
                $tokenStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $tokenStmt->bind_param("si", $token, $row['id']);
                $tokenStmt->execute();

                // Set a cookie with the token (e.g., expires in 30 days)
                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), "/", "", true, true);
            } else {
                // If unchecked, ensure no remember_token or cookie persists
                $tokenStmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                $tokenStmt->bind_param("i", $row['id']);
                $tokenStmt->execute();
                if (isset($_COOKIE['remember_me'])) {
                    setcookie('remember_me', '', time() - 3600, "/", "", true, true); // Expire any existing cookie
                }
            }

            header("Location: index.php"); // Redirect to homepage
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found.";
    }
}

// Check for "remember me" cookie on page load
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Login | Hasna Muscat Trading</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">

  <!-- [Favicon] icon -->
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon">
  <!-- [Google Font] Family -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
  <!-- [Tabler Icons] https://tablericons.com -->
  <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="assets/fonts/feather.css">
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="assets/fonts/fontawesome.css">
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="assets/fonts/material.css">
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="assets/css/style.css" id="main-style-link">
  <link rel="stylesheet" href="assets/css/style-preset.css">
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body>
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <div class="auth-main">
    <div class="auth-wrapper v3">
      <div class="auth-form">
        <div class="auth-header">
          <a href="#"><img src="assets/images/logo-dark.svg" alt="img"></a>
        </div>
        <div class="card my-5">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-4">
              <h3 class="mb-0"><b>Login</b></h3>
              <a href="signup.php" class="link-primary">Don't have an account?</a>
            </div>

            <!-- Display error message if any -->
            <?php if (isset($error_message)): ?>
              <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
              <div class="form-group mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
              </div>
              <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>
              <div class="d-flex mt-1 justify-content-between">
              <div class="form-check">
  <input class="form-check-input input-primary" type="checkbox" id="customCheckc1" name="keep_signed_in" checked="">
  <label class="form-check-label text-muted" for="customCheckc1">Keep me signed in</label>
</div>
                <h5 class="text-secondary f-w-400">Forgot Password?</h5>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" name="login" class="btn btn-primary">Login</button>
              </div>
            </form>

            <div class="saprator mt-3">
              <span>Login with</span>
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
  <!-- [ Main Content ] end -->
  <!-- Required Js -->
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
<!-- [Body] end -->
</html>