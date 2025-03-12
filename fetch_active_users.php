<?php
include 'config.php';

// Get only approved users who are active in the last 5 minutes
$query = "SELECT username FROM users WHERE status = 'approved' AND last_activity >= NOW() - INTERVAL 60 SECOND";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$activeUsers = [];
while ($row = $result->fetch_assoc()) {
    $activeUsers[] = $row['username'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($activeUsers);
?>