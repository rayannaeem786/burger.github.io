<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch products for the product dropdown
$product_sql = "SELECT id, name, price FROM products";
$product_result = $conn->query($product_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $products = $_POST['products'];
    $quantities = $_POST['quantities'];

    // Validate inputs
    if (empty($customer_name) || empty($customer_phone) || empty($products) || empty($quantities)) {
        $error_message = "All fields are required!";
    } else {
        // Prepare receipt data
        $receipt_data = [
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'items' => [],
            'total_amount' => 0,
        ];

        // Calculate total amount and prepare items
        foreach ($products as $index => $product_id) {
            $quantity = $quantities[$index];
            $product_sql = "SELECT name, price FROM products WHERE id = '$product_id'";
            $product_result = $conn->query($product_sql);
            $product = $product_result->fetch_assoc();

            if ($product) {
                $item_total = $product['price'] * $quantity;
                $receipt_data['items'][] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'total' => $item_total,
                ];
                $receipt_data['total_amount'] += $item_total;
            }
        }

        // Store receipt data in session for display
        $_SESSION['receipt_data'] = $receipt_data;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Make Receipt | Hasna Muscat Trading</title>
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
              <h2>Make Receipt</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

              <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
              <?php endif; ?>

              <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Customer Name:</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Customer Phone:</label>
                    <input type="text" name="customer_phone" class="form-control" required>
                </div>

                <!-- Product Selection -->
                <div class="mb-3">
                    <label class="form-label">Products:</label>
                    <div id="product-fields">
                        <div class="row mb-2">
                            <div class="col">
                                <select name="products[]" class="form-select" required>
                                    <option value="">Select a product</option>
                                    <?php while ($row = $product_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?> - R.O.<?php echo $row['price']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col">
                                <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" min="1" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-product" class="btn btn-secondary btn-sm">Add Another Product</button>
                </div>

                <button type="submit" class="btn btn-primary">Generate Receipt</button>
              </form>

              <!-- Display Receipt -->
              <?php if (isset($_SESSION['receipt_data'])): ?>
                <div class="mt-4">
                    <h3>Receipt</h3>
                    <p><strong>Customer Name:</strong> <?php echo $_SESSION['receipt_data']['customer_name']; ?></p>
                    <p><strong>Customer Phone:</strong> <?php echo $_SESSION['receipt_data']['customer_phone']; ?></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['receipt_data']['items'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?></td>
                                    <td>$<?php echo $item['price']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo $item['total']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total Amount</th>
                                <th>$<?php echo $_SESSION['receipt_data']['total_amount']; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php unset($_SESSION['receipt_data']); // Clear receipt data after displaying ?>
              <?php endif; ?>
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

  <script>
    // Add another product field
    document.getElementById('add-product').addEventListener('click', function() {
        const productFields = document.getElementById('product-fields');
        const newField = document.createElement('div');
        newField.classList.add('row', 'mb-2');
        newField.innerHTML = `
            <div class="col">
                <select name="products[]" class="form-select" required>
                    <option value="">Select a product</option>
                    <?php
                    $product_result->data_seek(0); // Reset pointer to the start
                    while ($row = $product_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?> - $<?php echo $row['price']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col">
                <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" min="1" required>
            </div>
        `;
        productFields.appendChild(newField);
    });
  </script>
</body>
<!-- [Body] end -->
</html>