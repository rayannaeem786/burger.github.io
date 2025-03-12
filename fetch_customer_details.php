<?php
include 'config.php';

if (isset($_POST['task_id'])) {
    $task_id = (int)$_POST['task_id'];

    // Fetch task details
    $sql = "SELECT * FROM customer_tasks WHERE id = $task_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Generate receipt HTML
        $receipt = "
        <div class='receipt'>
            <h3>Customer Receipt</h3>
            <hr>
            <p><strong>Customer Name:</strong> " . htmlspecialchars($row['customer_name']) . "</p>
            <p><strong>Phone Number:</strong> " . htmlspecialchars($row['phone_number']) . "</p>
            <p><strong>Machine Name:</strong> " . htmlspecialchars($row['machine_name']) . "</p>
            <p><strong>Machine Model:</strong> " . htmlspecialchars($row['machine_model']) . "</p>
            <p><strong>Machine Issue:</strong> " . htmlspecialchars($row['machine_issue']) . "</p>
            <p><strong>Demanded Price:</strong> " . htmlspecialchars($row['demanded_price']) . " OMR</p>
            <p><strong>Received Price:</strong> " . htmlspecialchars($row['received_price']) . " OMR</p>
            <p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>
            <hr>
            <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
        </div>
        ";

        echo $receipt;
    } else {
        echo "<p>No details found for this task.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>