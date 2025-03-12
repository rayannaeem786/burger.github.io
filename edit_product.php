<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the product ID from the URL
$product_id = $_GET['id'];

// Fetch product details from the database
$sql = "SELECT * FROM products WHERE id = '$product_id'";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

if (isset($_POST['edit_product'])) {
    // Sanitize and validate input data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    // Format price to 3 decimal places
    $price = number_format((float)$price, 3, '.', '');

    // Validate input
    if (!is_numeric($price) || $price < 0) {
        $_SESSION['error'] = "Invalid price.";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }
    if (!is_numeric($quantity) || $quantity < 0) {
        $_SESSION['error'] = "Invalid quantity.";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }

    // Update product in database
    $sql = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, quantity = ?, supplier = ?, location = ? WHERE id = ?");
    $sql->bind_param("ssdiiss", $name, $category, $price, $quantity, $supplier, $location, $product_id);

    if ($sql->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: admin.php");
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: edit_product.php?id=$product_id");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Edit Product | Hasna Muscat Trading</title>
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
              <h2>Edit Product</h2>
              <?php
              if (isset($_SESSION['success'])) {
                  echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                  unset($_SESSION['success']);
              }
              if (isset($_SESSION['error'])) {
                  echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                  unset($_SESSION['error']);
              }
              ?>
              <form method="POST" action="">
                <div class="mb-3">
                  <label class="form-label">Product Name:</label>
                  <input type="text" name="name" value="<?php echo $product['name']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Category:</label>
                  <input type="text" name="category" value="<?php echo $product['category']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Price:</label>
                  <input type="number" name="price" value="<?php echo $product['price']; ?>" class="form-control" step="0.001" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Quantity:</label>
                  <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Supplier:</label>
                  <input type="text" name="supplier" value="<?php echo $product['supplier']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Location:</label>
                  <input type="text" name="location" value="<?php echo $product['location']; ?>" class="form-control" required>
                </div>
                <button type="submit" name="edit_product" class="btn btn-primary w-100">Update Product</button>
              </form>
              <a href="admin.php" class="btn btn-secondary w-100 mt-2">Back to Admin Panel</a>
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