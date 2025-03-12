<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Pagination setup
$per_page = 10; // Number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Fetch completed customer tasks and calculate total received price
$sql = "SELECT id, customer_name, phone_number, machine_name, machine_model, machine_issue, demanded_price, received_price, status FROM customer_tasks WHERE status = 'completed' LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

// Fetch total received price directly from the database
$sql_total = "SELECT SUM(received_price) AS total_received_price FROM customer_tasks WHERE status = 'completed'";
$total_result = $conn->query($sql_total);
$total_received_price = $total_result->fetch_assoc()['total_received_price'] ?? 0;

// Calculate total pages for pagination
$sql_count = "SELECT COUNT(*) AS total_tasks FROM customer_tasks WHERE status = 'completed'";
$count_result = $conn->query($sql_count);
$total_tasks = $count_result->fetch_assoc()['total_tasks'];
$total_pages = ceil($total_tasks / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->
<head>
  <title>Completed Customer Tasks | Hasna Muscat Trading</title>
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
  <style>
    tr:hover {
        background-color: #f1f1f1;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    /* Receipt Styling */
    .receipt {
        font-family: Arial, sans-serif;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        max-width: 500px;
        margin: 0 auto;
    }
    .receipt h3 {
        text-align: center;
        margin-bottom: 20px;
    }
    .receipt p {
        margin: 10px 0;
    }
    .receipt hr {
        border: 0;
        height: 1px;
        background: #ddd;
        margin: 20px 0;
    }
  </style>
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
              <h2>Completed Customer Tasks</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <!-- Search bar -->
              <div class="mb-3">
                  <input type="text" id="search" class="form-control" placeholder="Search Tasks...">
              </div>

              <table class="table table-bordered table-hover">
                <thead class="table-success">
                  <tr>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Machine Name</th>
                    <th>Machine Model</th>
                    <th>Machine Issue Description</th>
                    <th>Demanded Price</th>
                    <th>Received Price</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="taskTable">
                  <?php
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          echo "<tr>
                              <td class='cursor-pointer' onclick='fetchCustomerDetails({$row['id']})'>" . htmlspecialchars($row['customer_name']) . "</td>
                              <td>" . htmlspecialchars($row['phone_number']) . "</td>
                              <td>" . htmlspecialchars($row['machine_name']) . "</td>
                              <td>" . htmlspecialchars($row['machine_model']) . "</td>
                              <td>" . htmlspecialchars($row['machine_issue']) . "</td>
                              <td>" . htmlspecialchars($row['demanded_price']) . "</td>
                              <td>" . htmlspecialchars($row['received_price']) . "</td>
                              <td>" . htmlspecialchars($row['status']) . "</td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='8' class='text-center text-muted'>No completed tasks</td></tr>";
                  }
                  ?>
                </tbody>
              </table>

              <!-- Total Received Price -->
              <div class="mt-3">
                  <label for="total_received_price" class="form-label">Total Received Amount</label>
                  <input type="text" id="total_received_price" class="form-control" value="<?php echo number_format($total_received_price, 3); ?>" readonly>
              </div>

              <!-- Pagination -->
              <nav aria-label="Page navigation example">
                  <ul class="pagination">
                      <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                          <a class="page-link" href="?page=1">First</a>
                      </li>
                      <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Previous</a>
                      </li>
                      <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                      </li>
                      <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                      </li>
                  </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Receipt -->
  <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="receiptModalLabel">Customer Receipt</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="receiptDetails">
          <!-- Receipt details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="window.print()">Print Receipt</button>
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

  <script>
    // Function to fetch customer details
    function fetchCustomerDetails(taskId) {
        $.ajax({
            url: 'fetch_customer_details.php',
            method: 'POST',
            data: { task_id: taskId },
            success: function(response) {
                $('#receiptDetails').html(response);
                $('#receiptModal').modal('show');
            }
        });
    }

    // Simple Search functionality
    document.getElementById('search').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#taskTable tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
  </script>
</body>
<!-- [Body] end -->
</html>