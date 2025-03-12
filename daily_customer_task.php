<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $customer_name = $_POST['customer_name'];
    $phone_number = $_POST['phone_number'];
    $machine_name = $_POST['machine_name'];
    $machine_model = $_POST['machine_model'];
    $machine_issue = $_POST['machine_issue'];
    $demanded_price = $_POST['demanded_price']; // Get the demanded price

    // Prepare the SQL query to insert the data
    $stmt = $conn->prepare("INSERT INTO customer_tasks (customer_name, phone_number, machine_name, machine_model, machine_issue, demanded_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $customer_name, $phone_number, $machine_name, $machine_model, $machine_issue, $demanded_price); // Added 'd' for the double (float) value

    if ($stmt->execute()) {
        // Store success message in session
        $_SESSION['success_message'] = "Customer task added successfully!";
        // Redirect to avoid resubmitting form on refresh
        header("Location: daily_customer_task.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Daily Customer Task | Hasna Muscat Trading</title>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Function to hide the success message after 3 seconds
    function hideSuccessMessage() {
        setTimeout(function() {
            var successAlert = document.getElementById('successMessage');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 3000);
    }
  </script>
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <!-- Sidebar Menu -->

  <?php include 'sidebar.php'; ?>


  <!-- Main Content -->
  <div class="pc-container">
    <div class="pc-content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <h2>Daily Customer Task</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <?php if (isset($_SESSION['success_message'])): ?>
                <div id="successMessage" class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <script>hideSuccessMessage();</script>
                <?php unset($_SESSION['success_message']); // Clear the message after displaying ?>
              <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; ?></div>
                <?php unset($_SESSION['error_message']); // Clear the error message ?>
              <?php endif; ?>

              <form method="POST">
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="number" class="form-control" id="phone_number" name="phone_number"  required>
                </div>

                <div class="mb-3">
                    <label for="machine_name" class="form-label">Machine Name</label>
                    <input type="text" class="form-control" id="machine_name" name="machine_name" required>
                </div>

                <div class="mb-3">
                    <label for="machine_model" class="form-label">Machine Model</label>
                    <input type="text" class="form-control" id="machine_model" name="machine_model" required>
                </div>

                <div class="mb-3">
                    <label for="machine_issue" class="form-label">Machine Issue Description</label>
                    <textarea class="form-control" id="machine_issue" name="machine_issue" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="demanded_price" class="form-label">Demanded Price</label>
                    <input type="number" class="form-control" id="demanded_price" name="demanded_price" step="0.01" required>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'footer.php'; ?>
  <!-- Footer -->

  <!-- Scripts -->
  <script src="assets/js/plugins/apexcharts.min.js"></script>
  <script src="assets/js/pages/dashboard-default.js"></script>
  <script src="assets/js/plugins/popper.min.js"></script>
  <script src="assets/js/plugins/simplebar.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/fonts/custom-font.js"></script>
  <script src="assets/js/pcoded.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>
</body>
<!-- [Body] end -->
</html>