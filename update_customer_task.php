<?php
session_start();
include 'config.php';

// Check if the user is logged in and handle timeout
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
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

// Validate task ID
if (!isset($_GET['id'])) {
    header("Location: pending_customer_tasks.php");
    exit();
}
$task_id = intval($_GET['id']);

// Fetch the task details
$sql = "SELECT * FROM customer_tasks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Task not found.";
    header("Location: pending_customer_tasks.php");
    exit();
}
$task = $result->fetch_assoc();

// Define slow-moving stock thresholds
$time_period_days = 90;
$slow_moving_threshold = 5;

// Fetch products from stock with slow-moving/dead stock info
$products_sql = "
    SELECT p.id, p.name, p.quantity, 
           COALESCE(SUM(s.quantity), 0) AS total_sold
    FROM products p
    LEFT JOIN sales s ON p.id = s.product_id 
        AND s.sale_date >= DATE_SUB(NOW(), INTERVAL $time_period_days DAY)
    WHERE p.deleted_at IS NULL
    GROUP BY p.id, p.name, p.quantity";
$products_result = $conn->query($products_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: update_customer_task.php?id=" . $task_id);
        exit();
    }

    $received_price = floatval($_POST['received_price']);
    $product_used = $_POST['product_used'] == '0' ? NULL : intval($_POST['product_used']);
    $quantity_used = $product_used === NULL ? 0 : intval($_POST['quantity_used']);

    if (empty($received_price) || $received_price < 0) {
        $_SESSION['error_message'] = "Received price must be a positive number.";
    } elseif ($product_used !== NULL && (empty($quantity_used) || $quantity_used <= 0)) {
        $_SESSION['error_message'] = "Please enter a valid quantity.";
    } else {
        $status = 'completed';
        $conn->begin_transaction();
        try {
            if ($product_used === NULL) {
                // Case: No product used
                $update_sql = "UPDATE customer_tasks SET status = ?, received_price = ?, product_used = ?, quantity_used = ?, completed_at = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sdiii", $status, $received_price, $product_used, $quantity_used, $task_id);
                $update_stmt->execute();
            } else {
                // Case: Product used - check stock
                $check_stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
                $check_stmt->bind_param("i", $product_used);
                $check_stmt->execute();
                $product = $check_stmt->get_result()->fetch_assoc();

                if ($product['quantity'] < $quantity_used) {
                    throw new Exception("Insufficient stock for the selected product.");
                }

                // Update task
                $update_sql = "UPDATE customer_tasks SET status = ?, received_price = ?, product_used = ?, quantity_used = ?, completed_at = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sdiii", $status, $received_price, $product_used, $quantity_used, $task_id);
                $update_stmt->execute();

                // Deduct stock
                $stock_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stock_stmt->bind_param("ii", $quantity_used, $product_used);
                $stock_stmt->execute();

                // Log usage in sales table (mimicking a sale)
                $sale_stmt = $conn->prepare("
                    INSERT INTO sales (product_id, quantity, sale_price, sale_date, customer_name, mobile_number) 
                    VALUES (?, ?, ?, NOW(), ?, ?)
                ");
                $sale_stmt->bind_param("iidsi", $product_used, $quantity_used, $received_price, $task['customer_name'], $task['phone_number']);
                $sale_stmt->execute();
            }

            $conn->commit();
            $_SESSION['success_message'] = "Task marked as completed successfully!";
            header("Location: invoice.php?id=" . $task_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in update_customer_task.php: " . $e->getMessage());
        }
    }
    header("Location: update_customer_task.php?id=" . $task_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Update Customer Task | Hasna Muscat Trading</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/fonts/feather.css">
  <link rel="stylesheet" href="assets/fonts/fontawesome.css">
  <link rel="stylesheet" href="assets/fonts/material.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/style-preset.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
              <h2>Update Customer Task</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
              <?php endif; ?>
              <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
              <?php endif; ?>

              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                  <label for="customer_name" class="form-label">Customer Name</label>
                  <input type="text" class="form-control" id="customer_name" value="<?php echo htmlspecialchars($task['customer_name']); ?>" disabled>
                </div>
                <div class="mb-3">
                  <label for="phone_number" class="form-label">Phone Number</label>
                  <input type="text" class="form-control" id="phone_number" value="<?php echo htmlspecialchars($task['phone_number']); ?>" disabled>
                </div>
                <div class="mb-3">
                  <label for="machine_name" class="form-label">Machine Name</label>
                  <input type="text" class="form-control" id="machine_name" value="<?php echo htmlspecialchars($task['machine_name']); ?>" disabled>
                </div>
                <div class="mb-3">
                  <label for="machine_model" class="form-label">Machine Model</label>
                  <input type="text" class="form-control" id="machine_model" value="<?php echo htmlspecialchars($task['machine_model']); ?>" disabled>
                </div>
                <div class="mb-3">
                  <label for="machine_issue" class="form-label">Machine Issue Description</label>
                  <textarea class="form-control" id="machine_issue" rows="3" disabled><?php echo htmlspecialchars($task['machine_issue']); ?></textarea>
                </div>
                <div class="mb-3">
                  <label for="product_used" class="form-label">Product Used from Our Stock</label>
                  <select class="form-control" id="product_used" name="product_used" required>
                    <option value="">Select a product</option>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                      <?php
                      $stock_status = '';
                      if ($product['total_sold'] == 0) {
                          $stock_status = ' [Dead Stock]';
                      } elseif ($product['total_sold'] <= $slow_moving_threshold) {
                          $stock_status = ' [Slow-Moving]';
                      }
                      ?>
                      <option value="<?php echo $product['id']; ?>" data-quantity="<?php echo $product['quantity']; ?>">
                        <?php echo htmlspecialchars($product['name']) . $stock_status; ?> (Available: <?php echo $product['quantity']; ?>)
                      </option>
                    <?php endwhile; ?>
                    <option value="0">None</option>
                  </select>
                </div>
                <div id="quantity_used_container" class="mb-3">
                  <label for="quantity_used" class="form-label">Quantity Used</label>
                  <input type="number" class="form-control" id="quantity_used" name="quantity_used" min="1" required>
                </div>
                <div class="mb-3">
                  <label for="received_price" class="form-label">Received Price</label>
                  <input type="number" step="0.01" class="form-control" id="received_price" name="received_price" min="0" required>
                </div>
                <button type="submit" class="btn btn-success">Mark as Completed</button>
              </form>
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
    $('#product_used').on('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const quantityInput = $('#quantity_used');
      const quantityContainer = $('#quantity_used_container');
      
      if (selectedOption.value === "0") {
        quantityContainer.hide();
        quantityInput.prop('disabled', true).val('');
      } else {
        quantityContainer.show();
        quantityInput.prop('disabled', false).attr('max', selectedOption.getAttribute('data-quantity'));
      }
    }).trigger('change');
  </script>
</body>
</html>