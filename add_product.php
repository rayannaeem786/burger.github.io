<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    header('Content-Type: application/json'); // Set response type to JSON
    
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $supplier = trim($_POST['supplier']);
    $location = trim($_POST['location']);

    $response = ['success' => false, 'message' => ''];

    // Validation
    if (empty($name) || empty($category) || empty($supplier) || empty($location)) {
        $response['message'] = "All fields are required.";
    } elseif ($price <= 0 || !is_numeric($price)) {
        $response['message'] = "Price must be a positive number.";
    } elseif ($quantity < 0 || !is_numeric($quantity)) {
        $response['message'] = "Quantity must be a non-negative number.";
    } else {
        // Check for duplicate product name and category
        $stmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND category = ? LIMIT 1");
        $stmt->bind_param("ss", $name, $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['message'] = "Product with this name and category already exists.";
        } else {
            // Insert new product
            $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity, supplier, location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiss", $name, $category, $price, $quantity, $supplier, $location);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Product added successfully!";
            } else {
                $response['message'] = "Error adding product: " . $conn->error;
            }
        }
        $stmt->close();
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Hasna Muscat Trading</title>
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
  <style>
    /* Custom styles to replace Bootstrap */
    .form-label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    .form-control {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    .btn-primary {
      background-color: #007bff;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      width: 100%;
    }
    .btn-primary:hover {
      background-color: #0056b3;
    }
    .message-success {
      color: #155724;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 4px;
    }
    .message-error {
      color: #721c24;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 4px;
    }
    .mb-3 {
      margin-bottom: 15px;
    }
  </style>
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
              <h2>Add New Product</h2>

              <!-- Message container -->
              <div id="message-container" class="mb-3"></div>

              <form id="addProductForm" method="POST" action="">
                <div class="mb-3">
                  <label class="form-label">Product Name:</label>
                  <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Category:</label>
                  <input type="text" name="category" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Price:</label>
                  <input type="number" name="price" class="form-control" step="0.001" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Quantity:</label>
                  <input type="number" name="quantity" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Supplier:</label>
                  <input type="text" name="supplier" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Location:</label>
                  <input type="text" name="location" class="form-control" required>
                </div>
                
                <button type="submit" name="add_product" class="btn-primary">Add Product</button>
              </form>
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
  <script src="assets/js/fonts/custom-font.js"></script>
  <script src="assets/js/pcoded.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>

  <!-- AJAX Submission Script -->
  <script>
    $(document).ready(function() {
      $('#addProductForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Clear previous messages
        $('#message-container').empty();
        
        $.ajax({
          url: 'add_product.php',
          type: 'POST',
          data: $(this).serialize() + '&add_product=1', // Include the add_product flag
          dataType: 'json',
          success: function(response) {
            // Display message
            let messageClass = response.success ? 'message-success' : 'message-error';
            $('#message-container').html(`<div class="${messageClass}">${response.message}</div>`);
            
            // Make the message disappear after 3 seconds
            setTimeout(function() {
              $('#message-container').empty();
            }, 3000);
            
            // If successful, clear the form
            if (response.success) {
              $('#addProductForm')[0].reset();
            }
          },
          error: function(xhr, status, error) {
            $('#message-container').html('<div class="message-error">An error occurred: ' + error + '</div>');
            
            // Make the error message disappear after 3 seconds
            setTimeout(function() {
              $('#message-container').empty();
            }, 3000);
          }
        });
      });
    });
  </script>
</body>
</html>