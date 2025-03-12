<?php
include 'config.php';

$sql = "SELECT username, email FROM users WHERE status = 'pending'";
$result = $conn->query($sql);

$pending_users = [];

while ($row = $result->fetch_assoc()) {
    $pending_users[] = $row;
}

echo json_encode($pending_users);
?>
