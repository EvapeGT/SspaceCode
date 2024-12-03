<?php
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

// Fetch contract data
$contracts_sql = "SELECT ContractID, TenantID, StartDate, EndDate, room_id FROM contracts";
$contracts_result = $conn->query($contracts_sql);

$contracts = [];
while ($row = $contracts_result->fetch_assoc()) {
    $contracts[] = $row;
}

if (count($contracts) == 0) {
    die("No contract data found");
}

// Contract Duration Analysis
$durations = array_map(function($contract) {
    $start = new DateTime($contract['StartDate']);
    $end = new DateTime($contract['EndDate']);
    return $end->diff($start)->m + ($end->diff($start)->y * 12);
}, $contracts);

$average_duration = array_sum($durations) / count($durations);
$longest_duration = max($durations);
$shortest_duration = min($durations);

// Close the database connection
$conn->close();

// Create a new Dompdf instance
$dompdf = new Dompdf();

// Generate the HTML content for the PDF with styling
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contractual Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h1, h2 { color: #2E86C1; margin-bottom: 10px; }
        .section { margin-bottom: 15px; }
        .content { padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
        .content ul { list-style: none; padding: 0; }
        .content ul li { margin-bottom: 5px; }
        .content p, .content ul li { margin: 0; }
        .compact-table { width: 100%; border-collapse: collapse; }
        .compact-table th, .compact-table td { border: 1px solid #ddd; padding: 8px; }
        .compact-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>S-Space Tenant Portal Contractual Report</h1>

    <div class="section">
        <h2>1. Contract Duration Analysis</h2>
        <div class="content">
            <ul>
                <li>Average contract duration: ' . number_format($average_duration, 2) . ' months</li>
                <li>Longest contract duration: ' . $longest_duration . ' months</li>
                <li>Shortest contract duration: ' . $shortest_duration . ' months</li>
            </ul>
        </div>
    </div>

    <!-- Placeholder for additional sections -->
    <!-- Include additional analyses as data becomes available -->

</body>
</html>
';

// Load the HTML content into Dompdf
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

// Output the PDF to the browser
$dompdf->stream("contractual_report.pdf", array("Attachment" => false));
?>
