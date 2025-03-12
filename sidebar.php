<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="index.php" class="b-brand text-primary">
        <img src="https://themewagon.github.io/Mantis-Bootstrap/assets/images/logo-dark.svg" class="img-fluid logo-lg"
          alt="logo">
      </a>
    </div>
    <div class="navbar-content">
      <ul class="pc-navbar">
        <li class="pc-item">
          <a href="index.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
            <span class="pc-mtext">Dashboard</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="admin.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-package"></i></span>
            <span class="pc-mtext">Product Management</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="daily_customer_task.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-plus"></i></span>
            <span class="pc-mtext">Add Customer Task</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="pending_customer_tasks.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-clock"></i></span>
            <span class="pc-mtext">Pending Tasks</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="completed_customer_tasks.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-check"></i></span>
            <span class="pc-mtext">Completed Tasks</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="make_sale.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-shopping-cart-plus"></i></span>
            <span class="pc-mtext">Make A Sale</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="sales_history.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-history"></i></span>
            <span class="pc-mtext">Sales History</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="stock_alerts.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-alert-circle"></i></span>
            <span class="pc-mtext">Stock Alerts</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="daily_reports.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-report"></i></span>
            <span class="pc-mtext">Daily Reports</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="approve_users.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-users"></i></span>
            <span class="pc-mtext">Approve Users</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="trash.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-trash"></i></span>
            <span class="pc-mtext">Trash</span>
          </a>
        </li>
        <li class="pc-item">
          <a href="logout.php" class="pc-link">
            <span class="pc-micon"><i class="ti ti-power"></i></span>
            <span class="pc-mtext">Logout</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- Header -->
<header class="pc-header">
  <div class="header-wrapper">
    <div class="me-auto pc-mob-drp">
      <ul class="list-unstyled">
        <li class="pc-h-item pc-sidebar-collapse">
          <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
      </ul>
    </div>
    <div class="ms-auto">
      <ul class="list-unstyled">
        <li class="dropdown pc-h-item header-user-profile">
          <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
            <img src="<?php echo isset($_SESSION['profile_picture']) ? 'assets/images/user/' . htmlspecialchars($_SESSION['profile_picture']) : 'assets/images/user/avatar-2.jpg'; ?>" alt="user-image" class="user-avtar">
            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
          </a>
          <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
            <div class="dropdown-header">
              <div class="d-flex mb-1">
                <div class="flex-shrink-0">
                  <img src="<?php echo isset($_SESSION['profile_picture']) ? 'assets/images/user/' . htmlspecialchars($_SESSION['profile_picture']) : 'assets/images/user/avatar-2.jpg'; ?>" alt="user-image" class="user-avtar wid-35">
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-1"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                  <span>UI/UX Designer</span>
                </div>
              </div>
            </div>
            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profileModal">
              <i class="ti ti-user"></i>
              <span>Edit Profile</span>
            </a>
            <a href="logout.php" class="dropdown-item">
              <i class="ti ti-power"></i>
              <span>Logout</span>
            </a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</header>

<!-- Profile Edit Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label for="profile_picture" class="form-label">Profile Picture</label>
            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>