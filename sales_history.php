<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Define the number of records per page
$records_per_page = 5;

// Get the current page number from the URL (default is 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($page - 1) * $records_per_page;

// Fetch total number of sales for pagination
$total_query = "SELECT COUNT(*) FROM sales";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch paginated sales history
$sql = "SELECT sales.id,sales.customer_name, sales.mobile_number, products.name AS product_name, sales.quantity, sales.sale_price, 
               (sales.quantity * sales.sale_price) AS total_price, sales.sale_date 
        FROM sales 
        JOIN products ON sales.product_id = products.id 
        ORDER BY sales.sale_date DESC
        LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sales History | Hasna Muscat Trading</title>
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
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <h2>Sales History</h2>
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Mobile Number</th>
                    <th>Product Name</th>
                    <th>Quantity Sold</th>
                    <th>Sale Price</th>
                    <th>Total Price</th>
                    <th>Sale Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          echo "<tr>
                              <td>{$row['id']}</td>
                                  <td>" . htmlspecialchars($row['customer_name']) . "</td>
                              <td>" . htmlspecialchars($row['mobile_number']) . "</td>
                              <td>" . htmlspecialchars($row['product_name']) . "</td>
                              <td>{$row['quantity']}</td>
                              <td>{$row['sale_price']}</td>
                              <td>{$row['total_price']}</td>
                              <td>{$row['sale_date']}</td>
                          </tr>";
                      }
                  } else {
                      echo "<tr><td colspan='6' class='text-center text-muted'>No sales found</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
              <nav>
                <ul class="pagination justify-content-center">
                  <?php if ($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                  <?php endif; ?>

                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                      <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                  <?php endif; ?>
                </ul>
              </nav>
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
</html>