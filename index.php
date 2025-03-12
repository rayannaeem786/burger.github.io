<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Set inactivity timeout (5 minutes)
$timeout_duration = 5 * 60; // 300 seconds
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Handle success/error messages
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}

// Define the number of records per page
$records_per_page = 5;

// Get the current page number from the URL (default is 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the offset for the query
$offset = ($page - 1) * $records_per_page;

// Fetch total number of active (non-deleted) products for pagination
$total_query = "SELECT COUNT(*) FROM products WHERE deleted_at IS NULL";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch paginated results excluding deleted products
$sql = "SELECT * FROM products WHERE deleted_at IS NULL LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Admin Panel - Product Management | Hasna Muscat Trading</title>
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
  <title>Hasna Muscat Trading</title>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>

<script>
  var app = angular.module('ProductApp', []);
  app.controller('ProductController', function ($scope, $http, $interval) {
    $scope.currentPage = 1;
    $scope.totalPages = 1;
    $scope.products = [];
    $scope.searchText = '';

    // Function to fetch products
    $scope.fetchProducts = function (page) {
      $scope.currentPage = page;
      $http.get('fetch_products.php?page=' + page + '&search=' + $scope.searchText)
        .then(function (response) {
          $scope.products = response.data.products;
          $scope.totalPages = response.data.total_pages;
        });
    };

    // Initialize data when page loads
    $scope.init = function () {
      $scope.fetchProducts(1);
    };

    // Fetch products every 30 seconds
    var fetchInterval = $interval(function () {
      $scope.fetchProducts($scope.currentPage);
    }, 500); // 30 seconds

    // Cancel the interval when the scope is destroyed
    $scope.$on('$destroy', function () {
      if (angular.isDefined(fetchInterval)) {
        $interval.cancel(fetchInterval);
        fetchInterval = undefined;
      }
    });
  });
</script>
<script>
  function checkSession() {
    fetch('check_session.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === "expired") {
          alert("Your session has expired due to inactivity.");
          window.location.href = "login.php";
        }
      });
  }

  // Check session every 30 seconds
  setInterval(checkSession, 1000);
</script>
<style>
    .zero-quantity {
        background-color: #ffcccc !important; /* Light red background */
    }
</style>
</head>



<body ng-app="ProductApp" ng-controller="ProductController" ng-init="init()" data-pc-preset="preset-1"
  data-pc-direction="ltr" data-pc-theme="light">
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
              <input type="text" ng-model="searchText" ng-keyup="fetchProducts(1)" placeholder="Search products..."
                class="form-control mb-3">
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
                <tr ng-repeat="product in products" ng-class="{'zero-quantity': product.quantity == 0}">
        <td>{{ product.name }}</td>
        <td>{{ product.category }}</td>
        <td>{{ product.price | number:3 }}</td>
        <td>{{ product.quantity }}</td>
        <td>{{ product.location }}</td>
        <td>{{ product.supplier }}</td>
    </tr>
                  <tr ng-if="products.length === 0">
                    <td colspan="6" class="text-center text-muted">No products found</td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <nav>
                <ul class="pagination justify-content-center">
                  <li class="page-item" ng-if="currentPage > 1">
                    <a class="page-link" href="#" ng-click="fetchProducts(currentPage - 1)">Previous</a>
                  </li>

                  <li class="page-item" ng-repeat="n in [].constructor(totalPages) track by $index">
                    <a class="page-link" href="#" ng-click="fetchProducts($index + 1)">{{ $index + 1 }}</a>
                  </li>

                  <li class="page-item" ng-if="currentPage < totalPages">
                    <a class="page-link" href="#" ng-click="fetchProducts(currentPage + 1)">Next</a>
                  </li>
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