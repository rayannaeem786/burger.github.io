<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the task ID from the URL
if (!isset($_GET['id'])) {
    header("Location: pending_customer_tasks.php");
    exit();
}

$task_id = $_GET['id'];

// Fetch the task details
$sql = "SELECT * FROM customer_tasks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Task not found.";
    header("Location: pending_customer_tasks.php");
    exit();
}

$task = $result->fetch_assoc();

// Fetch the product used
$product_sql = "SELECT name FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $task['product_used']);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Memo - HASNA'A MUSCAT TRADING</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .invoice-container {
            width: 700px;
            padding: 20px;
            background: #fff;
            border: 2px solid black;
            position: relative;
            font-size: 14px;
        }
        .header {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .sub-header {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: center;
            font-size: 14px;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
        }
        .footer {
            font-size: 12px;
            text-align: center;
            margin-top: 10px;
        }
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">حسناء مسقط للتجارة</div>
        <div class="sub-header">HASNA'A MUSCAT TRADING</div>
        <div class="sub-header">CASH MEMO</div>
        <div class="details">
            <p>No: <span id="invoiceNo"><?php echo $task_id; ?></span></p>
            <p>Date: <input type="date" id="invoiceDate" value="<?php echo date('Y-m-d'); ?>"></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody id="invoiceBody">
                <tr>
                    <td>1</td>
                    <td><?php echo htmlspecialchars($task['machine_issue']); ?></td>
                    <td><?php echo htmlspecialchars($task['quantity_used']); ?></td>
                    <td><?php echo htmlspecialchars($task['received_price']); ?></td>
                    <td class="amount"><?php echo htmlspecialchars($task['received_price']); ?></td>
                </tr>
            </tbody>
        </table>
        <p class="total">Total R.O: <span id="totalAmount"><?php echo htmlspecialchars($task['received_price']); ?></span></p>
        <p class="footer">We are not responsible if you not taken your Machine within 45 days</p>
        <div class="signature">
            <p>Receiver's Sign: ______________</p>
            <p>Signature: ______________</p>
        </div>
    </div>
    <script>
        // Print the invoice
        function printInvoice() {
            window.print();
        }
    </script>
</body>
</html>