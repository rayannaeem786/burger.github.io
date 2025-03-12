<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Session timeout
$timeout_duration = 5 * 60;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the selected date (default to today)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $selected_date)) {
    $selected_date = date('Y-m-d');
}

// Fetch data with try-catch for error handling
try {
    // Tasks created
    $stmt = $conn->prepare("SELECT * FROM customer_tasks WHERE DATE(created_at) = ? AND status = 'pending'");
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $tasks_created_result = $stmt->get_result();
    $tasks_created = $tasks_created_result->fetch_all(MYSQLI_ASSOC);
    $total_demanded = array_sum(array_column($tasks_created, 'demanded_price'));

    // Tasks completed
    $stmt = $conn->prepare("SELECT ct.*, p.name AS product_name 
                           FROM customer_tasks ct 
                           LEFT JOIN products p ON ct.product_used = p.id 
                           WHERE DATE(ct.completed_at) = ? AND ct.status = 'completed'");
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $tasks_completed_result = $stmt->get_result();
    $tasks_completed = $tasks_completed_result->fetch_all(MYSQLI_ASSOC);
    $total_received = array_sum(array_column($tasks_completed, 'received_price'));

    // Sales made
    $stmt = $conn->prepare("SELECT s.*, p.name AS product_name 
                           FROM sales s 
                           JOIN products p ON s.product_id = p.id 
                           WHERE DATE(s.created_at) = ?");
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $sales_result = $stmt->get_result();
    $sales = $sales_result->fetch_all(MYSQLI_ASSOC);
    $total_sales = array_sum(array_map(fn($sale) => $sale['quantity'] * $sale['sale_price'], $sales));
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    error_log("Error in daily_reports.php: " . $e->getMessage());
    $tasks_created = $tasks_completed = $sales = [];
    $total_demanded = $total_received = $total_sales = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Daily Reports | Hasna Muscat Trading</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/fonts/feather.css">
  <link rel="stylesheet" href="assets/fonts/fontawesome.css">
  <link rel="stylesheet" href="assets/fonts/material.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/style-preset.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php include 'sidebar.php'; ?>

  <div class="pc-container">
    <div class="pc-content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <h2>Daily Reports - <?php echo $selected_date; ?></h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
              <?php endif; ?>

              <!-- Summary Cards -->
              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5>Tasks Created</h5>
                      <p><?php echo count($tasks_created); ?></p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5>Tasks Completed</h5>
                      <p><?php echo count($tasks_completed); ?></p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h5>Total Sales</h5>
                      <p>OMR <?php echo number_format($total_sales, 3); ?></p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Date Filter and Export -->
              <div class="d-flex justify-content-between mb-4">
                <form method="GET" class="d-flex">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <div class="me-2">
                    <label for="date" class="form-label">Select Date:</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div class="d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                  </div>
                </form>
                <button id="export-pdf" class="btn btn-secondary">Export to PDF</button>
              </div>

              <!-- Customer Tasks Created -->
              <h3>Customer Tasks Created (<?php echo count($tasks_created); ?>)</h3>
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Customer Name</th>
                    <th>Machine Name</th>
                    <th>Machine Model</th>
                    <th>Issue</th>
                    <th>Demanded Price (OMR)</th>
                    <th>Created At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($tasks_created)): ?>
                    <tr><td colspan="6" class="text-center">No tasks created on <?php echo $selected_date; ?></td></tr>
                  <?php else: ?>
                    <?php foreach ($tasks_created as $task): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($task['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['machine_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['machine_model']); ?></td>
                        <td><?php echo htmlspecialchars($task['machine_issue']); ?></td>
                        <td><?php echo number_format($task['demanded_price'], 3); ?></td>
                        <td><?php echo $task['created_at']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="4" class="text-right">Total Demanded:</th>
                    <th>OMR <?php echo number_format($total_demanded, 3); ?></th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>

              <!-- Tasks Completed -->
              <h3>Tasks Completed (<?php echo count($tasks_completed); ?>)</h3>
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Customer Name</th>
                    <th>Machine Name</th>
                    <th>Product Used</th>
                    <th>Quantity Used</th>
                    <th>Received Price (OMR)</th>
                    <th>Completed At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($tasks_completed)): ?>
                    <tr><td colspan="6" class="text-center">No tasks completed on <?php echo $selected_date; ?></td></tr>
                  <?php else: ?>
                    <?php foreach ($tasks_completed as $task): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($task['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['machine_name']); ?></td>
                        <td><?php echo $task['product_name'] ? htmlspecialchars($task['product_name']) : 'None'; ?></td>
                        <td><?php echo $task['quantity_used']; ?></td>
                        <td><?php echo number_format($task['received_price'], 3); ?></td>
                        <td><?php echo $task['completed_at']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="4" class="text-right">Total Received:</th>
                    <th>OMR <?php echo number_format($total_received, 3); ?></th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>

              <!-- Sales Made -->
              <h3>Sales Made (<?php echo count($sales); ?>)</h3>
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Sale Price (OMR)</th>
                    <th>Total (OMR)</th>
                    <th>Customer Name</th>
                    <th>Mobile Number</th>
                    <th>Sold At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($sales)): ?>
                    <tr><td colspan="7" class="text-center">No sales made on <?php echo $selected_date; ?></td></tr>
                  <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                        <td><?php echo $sale['quantity']; ?></td>
                        <td><?php echo number_format($sale['sale_price'], 3); ?></td>
                        <td><?php echo number_format($sale['quantity'] * $sale['sale_price'], 3); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['mobile_number']); ?></td>
                        <td><?php echo $sale['created_at']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-right">Total Sales:</th>
                    <th>OMR <?php echo number_format($total_sales, 3); ?></th>
                    <th colspan="3"></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script src="assets/js/plugins/apexcharts.min.js"></script>
  <script src="assets/js/pages/dashboard-default.js"></script>
  <script src="assets/js/plugins/popper.min.js"></script>
  <script src="assets/js/plugins/simplebar.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/fonts/custom-font.js"></script>
  <script src="assets/js/pcoded.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#export-pdf').click(function() {
        const element = document.querySelector('.card-body');
        html2pdf()
          .set({ filename: 'daily_report_<?php echo $selected_date; ?>.pdf', margin: 10 })
          .from(element)
          .save();
      });

      // Optional: AJAX filtering (uncomment to enable)
      /*
      $('#date').change(function() {
        const date = $(this).val();
        window.location.href = '?date=' + date;
      });
      */
    });
  </script>
</body>
</html>