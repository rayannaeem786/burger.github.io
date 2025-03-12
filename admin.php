<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Initial fetch of active products (for first load) is now handled via AJAX
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Panel - Product Management | Hasna Muscat Trading</title>
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
              <h2>Product Management</h2>
              <div class="d-flex justify-content-between mb-3">
                <a href="add_product.php" class="btn btn-success">Add New Product</a>
                <div class="input-group w-25">
                  <input type="text" id="search-input" class="form-control" placeholder="Search by name..." value="">
                  <button class="btn btn-primary" id="search-btn">Search</button>
                </div>
              </div>
              <table class="table table-bordered table-hover" id="product-table">
                <thead class="table-primary">
                  <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Location</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="product-body">
                  <!-- Populated via AJAX -->
                </tbody>
              </table>
              <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center" id="pagination">
                  <!-- Populated via AJAX -->
                </ul>
              </nav>
              <div id="last-updated" class="text-muted text-center">Last updated: <span></span></div>
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
      let currentPage = 1;
      let pollingInterval;

      // Function to update product table
      function updateProducts(page = currentPage, search = $('#search-input').val()) {
        $.ajax({
          url: 'fetch_products.php',
          type: 'GET',
          data: { page: page, search: search },
          dataType: 'json',
          success: function(data) {
            var tbody = $('#product-body');
            tbody.empty(); // Clear current rows
            if (data.products.length > 0) {
              $.each(data.products, function(index, row) {
                var tr = `<tr id="product-${row.id}">
                            <td>${row.name}</td>
                            <td>${row.category}</td>
                            <td>${row.price}</td>
                            <td>${row.quantity}</td>
                            <td>${row.location}</td>
                            <td>
                              <a href="edit_product.php?id=${row.id}" class="btn btn-warning btn-sm">Edit</a> |
                              <button class="btn btn-danger btn-sm delete-product" data-id="${row.id}">Delete</button>
                            </td>
                          </tr>`;
                tbody.append(tr);
              });
            } else {
              tbody.append("<tr><td colspan='6' class='text-center text-muted'>No products found</td></tr>");
            }

            // Update pagination
            updatePagination(data.total_pages, page);
            currentPage = page;

            // Update last updated time
            $('#last-updated span').text(new Date().toLocaleTimeString());

            // Reattach event handlers
            attachEventHandlers();
          },
          error: function() {
            console.log('Error fetching product data');
          }
        });
      }

      // Function to update pagination
      function updatePagination(totalPages, currentPage) {
        var pagination = $('#pagination');
        pagination.empty();
        
        // Previous button
        pagination.append(`<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                          </li>`);
        
        // Page numbers (simplified: show all pages)
        for (let i = 1; i <= totalPages; i++) {
          pagination.append(`<li class="page-item ${i === currentPage ? 'active' : ''}">
                              <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>`);
        }
        
        // Next button
        pagination.append(`<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                          </li>`);
      }

      // Attach event handlers for delete
      function attachEventHandlers() {
        $('.delete-product').off('click').on('click', function() {
          var productId = $(this).data('id');
          if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
              url: 'delete_product.php',
              type: 'POST',
              data: { id: productId },
              success: function(response) {
                if (response === 'success') {
                  $('#product-' + productId).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#product-body tr').length === 0) {
                      $('#product-body').append("<tr><td colspan='6' class='text-center text-muted'>No products found</td></tr>");
                    }
                  });
                } else {
                  alert('Error deleting product: ' + response);
                }
              },
              error: function() {
                alert('An error occurred while deleting the product.');
              }
            });
          }
        });

        // Pagination click handler
        $('.page-link').off('click').on('click', function(e) {
          e.preventDefault();
          var page = $(this).data('page');
          if (page && !$(this).parent().hasClass('disabled')) {
            updateProducts(page);
          }
        });
      }

      // Search button handler
      $('#search-btn').on('click', function() {
        updateProducts(1); // Reset to page 1 on new search
      });

      // Search on Enter key
      $('#search-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
          updateProducts(1);
        }
      });

      // Start polling
      function startPolling() {
        pollingInterval = setInterval(function() { updateProducts(currentPage); }, 5000);
      }

      // Stop polling when tab is inactive
      function stopPolling() {
        clearInterval(pollingInterval);
      }

      // Initial load
      updateProducts();

      // Polling control based on tab visibility
      $(window).on('focus', startPolling).on('blur', stopPolling);
      startPolling();
    });
  </script>
</body>
</html>