<?php
// Load Dompdf library
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the HTML content
$html = '
<html>
<head>
    <title>Analytics Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; }
        h2 { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Analytics Dashboard</h1>
';

// Total rent payments received
$sql = "SELECT SUM(PaymentAmount) AS total_payments FROM rent_payments WHERE PaymentStatus = 'Paid'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$html .= '<p>Total Rent Payments: ₱' . number_format($row['total_payments'], 2) . '</p>';

// Payment method analysis
$payment_methods = array();
$sql = "SELECT PaymentMethod, COUNT(*) AS count FROM rent_payments WHERE PaymentStatus = 'Paid' GROUP BY PaymentMethod";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $payment_methods[$row['PaymentMethod']] = $row['count'];
}
$total_payments = array_sum($payment_methods);
$html .= '<h2>Payment Method Analysis</h2>';
$html .= '<table>';
foreach ($payment_methods as $method => $count) {
    $percentage = ($count / $total_payments) * 100;
    $html .= '<tr><td>' . $method . '</td><td>' . $count . '</td><td>' . number_format($percentage, 2) . '%</td></tr>';
}
$html .= '</table>';

// GCash utilization
$sql = "SELECT SUM(PaymentAmount) AS total_gcash_payments FROM rent_payments WHERE PaymentMethod = 'GCash' AND PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_gcash_payments = $row['total_gcash_payments'];

$sql = "SELECT COUNT(*) AS total_tenants FROM tenants";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_tenants = $row['total_tenants'];

$gcash_percentage = ($total_tenants > 0) ? ($total_gcash_payments / $total_tenants) * 100 : 0;

$html .= '<h2>GCash Utilization</h2>';
$html .= '<p>Total rent payments made via GCash in the past 12 months: ₱' . number_format($total_gcash_payments, 2) . '</p>';
$html .= '<p>Average monthly rent payments via GCash: ₱' . number_format($total_gcash_payments / 12, 2) . '</p>';
$html .= '<p>Percentage of residents using GCash for rent payments: ' . number_format($gcash_percentage, 2) . '%</p>';

// Rest of your code...

// Rent Payment Timeliness

$sql = "SELECT COUNT(*) AS on_time_payments, COUNT(CASE WHEN DATEDIFF(PaymentDate, DATE_ADD(LAST_DAY(DATE_SUB(PaymentDate, INTERVAL 1 MONTH)), INTERVAL 1 DAY)) > 0 THEN 1 END) AS late_payments FROM rent_payments WHERE PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$on_time_payments = $row['on_time_payments'];
$late_payments = $row['late_payments'];
$total_payments = $on_time_payments + $late_payments;
$on_time_percentage = ($total_payments > 0) ? ($on_time_payments / $total_payments) * 100 : 0;
$late_percentage = 100 - $on_time_percentage;

$sql = "SELECT AVG(DATEDIFF(PaymentDate, DATE_ADD(LAST_DAY(DATE_SUB(PaymentDate, INTERVAL 1 MONTH)), INTERVAL 1 DAY))) AS average_days_late FROM rent_payments WHERE DATEDIFF(PaymentDate, DATE_ADD(LAST_DAY(DATE_SUB(PaymentDate, INTERVAL 1 MONTH)), INTERVAL 1 DAY)) > 0 AND PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$average_days_late = round($row['average_days_late'], 2);

$html .= '<h2>Rent Payment Timeliness</h2>';
$html .= '<p>Percentage of residents paying rent on time (by the due date): ' . number_format($on_time_percentage, 2) . '%</p>';
$html .= '<p>Percentage of residents paying rent late: ' . number_format($late_percentage, 2) . '%</p>';
$html .= '<p>Average number of days late for late payments: ' . $average_days_late . ' days</p>';

// Service Requests
$html .= '<h2>Service Requests</h2>';

// Monthly Service Request Volume
$sql = "SELECT MONTH(RequestDate) AS month, COUNT(*) AS total_requests FROM servicerequests WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY MONTH(RequestDate)";
$result = $conn->query($sql);
$html .= '<p>Monthly Service Request Volume:</p>';
$html .= '<table><tr><th>Month</th><th>Total Requests</th></tr>';
while ($row = $result->fetch_assoc()) {
    $html .= '<tr><td>' . date('F', mktime(0, 0, 0, $row['month'], 1)) . '</td><td>' . $row['total_requests'] . '</td></tr>';
}
$html .= '</table>';

// Request Backlog and Resolution Rate

$sql = "SELECT COUNT(*) AS total_requests, COUNT(CASE WHEN Status = 'Completed' THEN 1 END) AS completed_requests FROM servicerequests WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$result = $conn->query($sql);

$row = $result->fetch_assoc();
$total_requests = $row['total_requests'];
$completed_requests = $row['completed_requests'];
$pending_requests = $total_requests - $completed_requests;
$resolution_rate = ($total_requests > 0) ? ($completed_requests / $total_requests) * 100 : 0;

$html .= '<p>Total Service Requests (Past 12 Months): ' . $total_requests . '</p>';
$html .= '<p>Completed Requests: ' . $completed_requests . '</p>';
$html .= '<p>Pending Requests: ' . $pending_requests . '</p>';
$html .= '<p>Request Resolution Rate: ' . number_format($resolution_rate, 2) . '%</p>';

// Category-wise Analysis
$sql = "SELECT Category, COUNT(*) AS total_requests FROM servicerequests WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY Category";
$result = $conn->query($sql);
$html .= '<p>Category-wise Analysis:</p>';
$html .= '<table><tr><th>Category</th><th>Total Requests</th></tr>';
while ($row = $result->fetch_assoc()) {
    $html .= '<tr><td>' . $row['Category'] . '</td><td>' . $row['total_requests'] . '</td></tr>';
}
$html .= '</table>';


// Response Time Monitoring
$sql = "SELECT AVG(DATEDIFF(CURDATE(), RequestDate)) AS average_response_time FROM servicerequests WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$average_response_time = round($row['average_response_time'], 2);


$html .= '<p>Average Response Time: ' . $average_response_time . ' days</p>';

// Close the HTML content
$html .= '</body></html>';

// Create a new Dompdf instance
$dompdf = new Dompdf();

// Load the HTML content
$dompdf->loadHtml($html);

// Render the PDF
$dompdf->render();

// Output the PDF
$dompdf->stream('dashboard_report.pdf', array('Attachment' => false));
?>