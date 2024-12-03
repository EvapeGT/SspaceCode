<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$period = $_GET['period'];

$dateCondition = "";
switch ($period) {
    case 'today':
        $dateCondition = "PaymentDate = CURDATE()";
        break;
    case 'month':
        $dateCondition = "MONTH(PaymentDate) = MONTH(CURDATE()) AND YEAR(PaymentDate) = YEAR(CURDATE())";
        break;
    case 'year':
        $dateCondition = "YEAR(PaymentDate) = YEAR(CURDATE())";
        break;
    default:
        $dateCondition = "1=1"; // No date condition, fetch all records
}

// Fetch count of pending rent payments
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Pending' AND $dateCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingRentCount = $row['count'];

// Fetch count of paid rent payments
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Paid' AND $dateCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$paidRentCount = $row['count'];

// Fetch count of unpaid rent payments
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Unpaid' AND $dateCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$unpaidRentCount = $row['count'];

$response = [
    'pendingRentCount' => $pendingRentCount,
    'paidRentCount' => $paidRentCount,
    'unpaidRentCount' => $unpaidRentCount
];

echo json_encode($response);

$conn->close();
?>