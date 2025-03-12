<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Initial fetch of trashed products (for first load)
$sql = "SELECT id, name, price, deleted_at FROM products WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Trash - Deleted Products | Hasna Muscat Trading</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
  <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
  <meta name="author" content="CodedThemes">
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
  <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/fonts/feather.css">
  <link rel="stylesheet" href="assets/fonts/fontawesome.css">
  <link rel="stylesheet" href="assets/fonts/material.css">
  <link rel="stylesheet" href="assets/css/style.css" id="main-style-link">
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
              <h2>Trash - Deleted Products</h2>
              <a href="admin.php" class="btn btn-secondary mb-3">Back to Admin</a>
              <table class="table table-bordered table-hover" id="trash-table">
                <thead class="table-primary">
                  <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="trash-body">
                  <?php
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) { ?>
                        <tr id="trash-<?php echo $row['id']; ?>">
                          <td><?php echo htmlspecialchars($row['name']); ?></td>
                          <td><?php echo number_format($row['price'], 2); ?></td>
                          <td><?php echo $row['deleted_at']; ?></td>
                          <td>
                            <button class="btn btn-success btn-sm restore-product" data-id="<?php echo $row['id']; ?>">Restore</button>
                            <button class="btn btn-danger btn-sm permanent-delete" data-id="<?php echo $row['id']; ?>">Delete Permanently</button>
                          </td>
                        </tr>
                      <?php }
                  } else {
                      echo "<tr><td colspan='4' class='text-center text-muted'>No deleted products found</td></tr>";
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
      // Function to update trash table
      function updateTrash() {
        $.ajax({
          url: 'fetch_trash.php',
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            var tbody = $('#trash-body');
            tbody.empty(); // Clear current rows
            if (data.length > 0) {
              $.each(data, function(index, row) {
                var tr = `<tr id="trash-${row.id}">
                            <td>${row.name}</td>
                            <td>${row.price}</td>
                            <td>${row.deleted_at}</td>
                            <td>
                              <button class="btn btn-success btn-sm restore-product" data-id="${row.id}">Restore</button>
                              <button class="btn btn-danger btn-sm permanent-delete" data-id="${row.id}">Delete Permanently</button>
                            </td>
                          </tr>`;
                tbody.append(tr);
              });
            } else {
              tbody.append("<tr><td colspan='4' class='text-center text-muted'>No deleted products found</td></tr>");
            }
            // Reattach event handlers after updating DOM
            attachEventHandlers();
          },
          error: function() {
            console.log('Error fetching trash data');
          }
        });
      }

      // Attach event handlers for restore and permanent delete
      function attachEventHandlers() {
        // Restore product
        $('.restore-product').off('click').on('click', function() {
          var productId = $(this).data('id');
          if (confirm('Are you sure you want to restore this product?')) {
            $.ajax({
              url: 'restore_product.php',
              type: 'POST',
              data: { id: productId },
              success: function(response) {
                if (response === 'success') {
                  $('#trash-' + productId).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#trash-body tr').length === 0) {
                      $('#trash-body').append("<tr><td colspan='4' class='text-center text-muted'>No deleted products found</td></tr>");
                    }
                  });
                } else {
                  alert('Error restoring product: ' + response);
                }
              },
              error: function() {
                alert('An error occurred while restoring the product.');
              }
            });
          }
        });

        // Permanent delete
        $('.permanent-delete').off('click').on('click', function() {
          var productId = $(this).data('id');
          if (confirm('Are you sure? This action cannot be undone!')) {
            $.ajax({
              url: 'permanent_delete.php',
              type: 'POST',
              data: { id: productId },
              success: function(response) {
                if (response === 'success') {
                  $('#trash-' + productId).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#trash-body tr').length === 0) {
                      $('#trash-body').append("<tr><td colspan='4' class='text-center text-muted'>No deleted products found</td></tr>");
                    }
                  });
                } else {
                  alert('Error deleting product permanently: ' + response);
                }
              },
              error: function() {
                alert('An error occurred while deleting the product permanently.');
              }
            });
          }
        });
      }

      // Initial event handlers for the first load
      attachEventHandlers();

      // Poll every 10 seconds (10000 ms)
      setInterval(updateTrash, 1000);

      // Optional: Run immediately on load to ensure freshness
      updateTrash();
    });
  </script>
</body>
</html>