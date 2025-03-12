<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Define thresholds
$low_stock_threshold = 10; // For low stock alerts
$time_period_days = 90; // Analyze sales over the last 90 days
$slow_moving_threshold = 5; // Less than 5 units sold in 90 days = slow-moving

// Fetch products with low stock
$sql_low_stock = "SELECT * FROM products WHERE quantity <= $low_stock_threshold AND deleted_at IS NULL";
$result_low_stock = $conn->query($sql_low_stock);

// Fetch slow-moving and dead stock
$sql_stock_movement = "
    SELECT p.id, p.name, p.category, p.price, p.quantity, p.location, p.supplier,
           COALESCE(SUM(s.quantity), 0) AS total_sold
    FROM products p
    LEFT JOIN sales s ON p.id = s.product_id 
        AND s.sale_date >= DATE_SUB(NOW(), INTERVAL $time_period_days DAY)
    WHERE p.deleted_at IS NULL
    GROUP BY p.id, p.name, p.category, p.price, p.quantity, p.location, p.supplier
    HAVING total_sold <= $slow_moving_threshold
    ORDER BY total_sold ASC";
$result_stock_movement = $conn->query($sql_stock_movement);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Stock Alerts | Hasna Muscat Trading</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
  <!-- Pre-loader -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <!-- Sidebar Menu -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="pc-container">
    <div class="pc-content">
      <div class="row">
        <!-- Low Stock Alerts -->
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <h2>Low Stock Alerts</h2>
              <p>Products with quantity ≤ <?php echo $low_stock_threshold; ?>:</p>
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Location</th>
                    <th>Supplier</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result_low_stock->num_rows > 0) {
                      while ($row = $result_low_stock->fetch_assoc()) {
                          $formatted_price = number_format((float)$row['price'], 3, '.', '');
                          echo "<tr>
                              <td>" . htmlspecialchars($row['name']) . "</td>
                              <td>" . htmlspecialchars($row['category']) . "</td>
                              <td>$formatted_price</td>
                              <td class='text-danger'><strong>{$row['quantity']}</strong></td>
                              <td>" . htmlspecialchars($row['location']) . "</td>
                              <td>" . htmlspecialchars($row['supplier']) . "</td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='6' class='text-center text-muted'>No low stock products found</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Slow-Moving and Dead Stock -->
        <div class="col-md-12 mt-4">
          <div class="card">
            <div class="card-body">
              <h2>Slow-Moving and Dead Stock</h2>
              <p>Products with ≤ <?php echo $slow_moving_threshold; ?> units sold in the last <?php echo $time_period_days; ?> days:</p>
              <table class="table table-bordered table-hover">
                <thead class="table-warning">
                  <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Location</th>
                    <th>Supplier</th>
                    <th>Units Sold (Last <?php echo $time_period_days; ?> Days)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result_stock_movement->num_rows > 0) {
                      while ($row = $result_stock_movement->fetch_assoc()) {
                          $formatted_price = number_format((float)$row['price'], 3, '.', '');
                          $stock_status = ($row['total_sold'] == 0) ? 'text-danger' : 'text-warning';
                          echo "<tr>
                              <td>" . htmlspecialchars($row['name']) . "</td>
                              <td>" . htmlspecialchars($row['category']) . "</td>
                              <td>$formatted_price</td>
                              <td>" . htmlspecialchars($row['quantity']) . "</td>
                              <td>" . htmlspecialchars($row['location']) . "</td>
                              <td>" . htmlspecialchars($row['supplier']) . "</td>
                              <td class='$stock_status'><strong>{$row['total_sold']}</strong></td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='7' class='text-center text-muted'>No slow-moving or dead stock found</td></tr>";
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
</html>