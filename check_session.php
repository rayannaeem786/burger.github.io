<?php
session_start();

$timeout_duration = 1800; // 58 seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'expired']);
    exit();
} else {
    echo json_encode(['status' => 'active']);
}
?>
