<?php
session_start();
include 'config.php';

// Check if the user is an admin or has permission to make sales
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['make_sale'])) {
    // Get input data
    $product_id = $_POST['product_id'];
    $quantity_sold = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $customer_name = $_POST['customer_name']; // New input
    $mobile_number = $_POST['mobile_number']; // New input

    // Sanitize the input data
    $quantity_sold = mysqli_real_escape_string($conn, $quantity_sold);
    $sale_price = mysqli_real_escape_string($conn, $sale_price);
    $customer_name = mysqli_real_escape_string($conn, $customer_name); // Sanitize customer name
    $mobile_number = mysqli_real_escape_string($conn, $mobile_number); // Sanitize mobile number

    // Fetch product details to check if stock is available
    $sql = "SELECT * FROM products WHERE id = '$product_id'";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    if (!$product) {
      $_SESSION['error_message'] = "Product not found!";
      header("Location: make_sale.php");
      exit();
    }

    // Check if enough stock is available
    if ($quantity_sold > $product['quantity']) {
      $_SESSION['error_message'] = "Not enough stock available!";
      header("Location: make_sale.php");
      exit();
    }

    // Update the product quantity
    $new_quantity = $product['quantity'] - $quantity_sold;
    $update_sql = "UPDATE products SET quantity = '$new_quantity' WHERE id = '$product_id'";

    if ($conn->query($update_sql) === TRUE) {
        // Record the sale in the sales table
        $insert_sql = "INSERT INTO sales (product_id, quantity, sale_price, customer_name, mobile_number) 
                       VALUES ('$product_id', '$quantity_sold', '$sale_price', '$customer_name', '$mobile_number')";

if ($conn->query($insert_sql)) {
  $_SESSION['success_message'] = "Sale recorded successfully!";
  header("Location: make_sale.php");
  exit();
} else {
  $_SESSION['error_message'] = "Error recording sale: " . $conn->error;
  header("Location: make_sale.php");
  exit();
}
    } else {
      $_SESSION['error_message'] = "Error updating product quantity: " . $conn->error;
      header("Location: make_sale.php");
      exit();
    }
}

// Fetch categories for the category dropdown
$category_sql = "SELECT DISTINCT category FROM products";
$category_result = $conn->query($category_sql);
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Make Sale | Hasna Muscat Trading</title>
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
              <h2>Make a Sale</h2>
              <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

   <!-- Display Success or Error Messages -->
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


              <form method="POST" action="make_sale.php">
                <!-- Category Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Category:</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php while ($row = $category_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['category']; ?>"><?php echo $row['category']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Product Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Product:</label>
                    <select id="product" name="product_id" class="form-select" required>
                        <option value="">Select a product</option>
                    </select>
                </div>
                  <!-- Customer Name -->
  <div class="mb-3">
    <label class="form-label">Customer Name:</label>
    <input type="text" name="customer_name" class="form-control" required>
  </div>

  <!-- Mobile Number -->
  <div class="mb-3">
    <label class="form-label">Mobile Number:</label>
    <input type="text" name="mobile_number" class="form-control" required>
  </div>
                <div class="mb-3">
                    <label class="form-label">Quantity Sold:</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sale Price:</label>
                    <input type="number" name="sale_price" class="form-control" step="0.01" required>
                </div>
                
                <button type="submit" name="make_sale" class="btn btn-primary">Record Sale</button>
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

  <script>
$(document).ready(function() {
    $('#category').change(function() {
        var category = $(this).val();

        $('#product').empty();
        $('#product').append('<option value="">Select a product</option>');

        if (category !== '') {
            $.ajax({
                url: 'fetch_products.php',
                method: 'GET',
                data: { category: category },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    var products = response.products;
                    if (products && products.length > 0) {
                        products.forEach(function(product) {
                            $('#product').append(
                                $('<option>', {
                                    value: product.id,
                                    text: product.name + ' (' + product.quantity + ' in stock)'
                                })
                            );
                        });
                    } else {
                        $('#product').append('<option value="">No products found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    $('#product').append('<option value="">Error loading products</option>');
                }
            });
        }
    });
});
</script>
</body>
<!-- [Body] end -->
</html>