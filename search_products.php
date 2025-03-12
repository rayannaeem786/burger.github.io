<?php
include 'config.php';

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);
    $sql = "SELECT * FROM products WHERE name LIKE '%$search%' OR category LIKE '%$search%' OR location LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM products";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['category']}</td>
            <td>{$row['price']}</td>
            <td>{$row['quantity']}</td>
            <td>{$row['location']}</td>
            <td>{$row['supplier']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No products found</td></tr>";
}
?>
