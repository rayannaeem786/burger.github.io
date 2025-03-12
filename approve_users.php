<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); // Redirect to login page if not an admin
    exit();
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}


// Approve user
if (isset($_GET['approve']) && isset($_GET['username'])) {
    $username = sanitizeInput($_GET['username']);

    // Update user status to approved
    $sql = "UPDATE users SET status = 'approved' WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User approved successfully.";
    } else {
        $_SESSION['error_message'] = "Error approving user.";
    }
    $stmt->close();
    header("Location: approve_users.php"); // Redirect to avoid form resubmission
    exit();
}

// Reject user
if (isset($_GET['reject']) && isset($_GET['username'])) {
    $username = sanitizeInput($_GET['username']);

    // Update user status to rejected
    $sql = "UPDATE users SET status = 'rejected' WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User rejected successfully.";
    } else {
        $_SESSION['error_message'] = "Error rejecting user.";
    }
    $stmt->close();
    header("Location: approve_users.php"); // Redirect to avoid form resubmission
    exit();
}

// Fetch users with a pending status
$sql = "SELECT * FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin | Approve Users</title>
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
              <h1>Approve or Reject Users</h1>

              <!-- Display success or error message -->
              <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
              <?php endif; ?>
              <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
              <?php endif; ?>

              <!-- User list table -->
              <table class="table table-bordered table-hover">
                <thead class="table-primary">
                  <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                          <a href="?approve=true&username=<?php echo htmlspecialchars($row['username']); ?>" onclick="return confirm('Are you sure you want to approve this user?')" class="btn btn-success btn-sm">Approve</a>
                          <a href="?reject=true&username=<?php echo htmlspecialchars($row['username']); ?>" onclick="return confirm('Are you sure you want to reject this user?')" class="btn btn-danger btn-sm">Reject</a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted">No users to approve or reject.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
       
    </div>
    <div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h3><i class="ti ti-user"></i> Active Users</h3>
                <ul id="activeUsersList" class="list-group">
                    <!-- Populated via AJAX -->
                </ul>
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
  <script src="assets/js/plugins/popper.min.js"></script>
  <script src="assets/js/plugins/simplebar.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/fonts/custom-font.js"></script>
  <script src="assets/js/pcoded.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function fetchPendingUsers() {
    $.ajax({
        url: "fetch_pending_users.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let userTable = $("tbody");
            userTable.empty(); // Clear previous entries

            if (data.length === 0) {
                userTable.append(`<tr><td colspan="3" class="text-center text-muted">No users to approve or reject.</td></tr>`);
            } else {
                data.forEach(user => {
                    userTable.append(`
                        <tr>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>
                                <a href="?approve=true&username=${user.username}" onclick="return confirm('Approve this user?')" class="btn btn-success btn-sm">Approve</a>
                                <a href="?reject=true&username=${user.username}" onclick="return confirm('Reject this user?')" class="btn btn-danger btn-sm">Reject</a>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Fetch users every 5 seconds
setInterval(fetchPendingUsers, 500);

// Fetch users immediately when the page loads
fetchPendingUsers();
</script>
<script>
function fetchActiveUsers() {
    fetch("fetch_active_users.php")
    .then(response => response.json())
    .then(data => {
        let userList = document.getElementById("activeUsersList");
        userList.innerHTML = "";
        data.forEach(user => {
            let li = document.createElement("li");
            li.textContent = user;
            userList.appendChild(li);
        });
    })
    .catch(error => console.error("Error fetching active users:", error));
}

// Refresh every 5 seconds
setInterval(fetchActiveUsers, 500);
fetchActiveUsers(); // Initial call
</script>
</body>
</html>