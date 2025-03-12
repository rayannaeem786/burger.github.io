<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch pending customer tasks
$sql = "SELECT * FROM customer_tasks WHERE status = 'pending'";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $task_id = $_POST['task_id'];

    // Delete task
    $delete_sql = "DELETE FROM customer_tasks WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Task deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }

    // Redirect to refresh the page and remove deleted task
    header("Location: pending_customer_tasks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Pending Customer Tasks | Hasna Muscat Trading</title>
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
              <h2>Pending Customer Tasks</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
                <div id="alert-message" class="alert 
                    <?php echo isset($_SESSION['success_message']) ? 'alert-success' : 'alert-danger'; ?>">
                    <?php 
                        echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : $_SESSION['error_message']; 
                        unset($_SESSION['success_message'], $_SESSION['error_message']);
                    ?>
                </div>
                
                <script>
                    setTimeout(function() {
                        var alertBox = document.getElementById('alert-message');
                        if (alertBox) {
                            alertBox.style.transition = "opacity 0.5s ease";
                            alertBox.style.opacity = "0";
                            setTimeout(() => alertBox.remove(), 500);
                        }
                    }, 3000);
                </script>
              <?php endif; ?>

              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Machine Name</th>
                    <th>Machine Model</th>
                    <th>Machine Issue Description</th>
                    <th>Demanded Price</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          echo "<tr>
                              <td>" . htmlspecialchars($row['customer_name']) . "</td>
                              <td>" . htmlspecialchars($row['phone_number']) . "</td>
                              <td>" . htmlspecialchars($row['machine_name']) . "</td>
                              <td>" . htmlspecialchars($row['machine_model']) . "</td>
                              <td>" . htmlspecialchars($row['machine_issue']) . "</td>
                              <td>" . htmlspecialchars($row['demanded_price']) . "</td>
                              <td>
                                  <a href='update_customer_task.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Update</a>
                                  <form method='POST' style='display:inline-block'>
                                      <input type='hidden' name='task_id' value='" . $row['id'] . "'>
                                      <button type='submit' name='delete' class='btn btn-danger btn-sm'>Delete</button>
                                  </form>
                              </td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='7' class='text-center text-muted'>No pending tasks</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
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