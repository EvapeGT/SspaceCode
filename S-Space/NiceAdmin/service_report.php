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

// Fetch monthly service request volume
$monthly_sql = "SELECT DATE_FORMAT(RequestDate, '%Y-%m') AS month, COUNT(*) AS total_requests
                FROM servicerequests
                GROUP BY month
                ORDER BY month";

$monthly_result = $conn->query($monthly_sql);
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Fetch request backlog and resolution rate
$backlog_sql = "SELECT DATE_FORMAT(RequestDate, '%Y-%m') AS month,
                       COUNT(*) AS total_requests,
                       SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) AS completed_requests,
                       SUM(CASE WHEN Status != 'Completed' THEN 1 ELSE 0 END) AS pending_requests
                FROM servicerequests
                GROUP BY month
                ORDER BY month";

$backlog_result = $conn->query($backlog_sql);
$backlog_data = [];
while ($row = $backlog_result->fetch_assoc()) {
    $backlog_data[] = $row;
}

// Fetch category-wise analysis
$category_sql = "SELECT DATE_FORMAT(RequestDate, '%Y-%m') AS month, Category, COUNT(*) AS total_requests
                 FROM servicerequests
                 GROUP BY month, Category
                 ORDER BY month, Category";

$category_result = $conn->query($category_sql);
$category_data = [];
while ($row = $category_result->fetch_assoc()) {
    $category_data[] = $row;
}

// Fetch room-wise maintenance trends
$room_sql = "SELECT room_id, COUNT(*) AS total_requests
             FROM servicerequests
             GROUP BY room_id
             ORDER BY total_requests DESC";

$room_result = $conn->query($room_sql);
$room_data = [];
while ($row = $room_result->fetch_assoc()) {
    $room_data[] = $row;
}

// Fetch response time monitoring
$response_time_sql = "SELECT RequestID, RequestDate, Status, DATEDIFF(CURDATE(), RequestDate) AS days_open
                      FROM servicerequests
                      WHERE Status != 'Completed'";

$response_time_result = $conn->query($response_time_sql);
$response_time_data = [];
while ($row = $response_time_result->fetch_assoc()) {
    $response_time_data[] = $row;
}

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
    <title>Service Request Report</title>
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
    <h1>S-Space Tenant Portal Service Request Report</h1>

    <div class="section">
        <h2>1. Monthly Service Request Volume</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($monthly_data as $data) {
    $html .= '<tr>
                <td>' . $data['month'] . '</td>
                <td>' . $data['total_requests'] . '</td>
              </tr>';
}
$html .= '      </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>2. Request Backlog and Resolution Rate</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Requests</th>
                        <th>Completed Requests</th>
                        <th>Pending Requests</th>
                        <th>Resolution Rate (%)</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($backlog_data as $data) {
    $resolution_rate = ($data['completed_requests'] / $data['total_requests']) * 100;
    $html .= '<tr>
                <td>' . $data['month'] . '</td>
                <td>' . $data['total_requests'] . '</td>
                <td>' . $data['completed_requests'] . '</td>
                <td>' . $data['pending_requests'] . '</td>
                <td>' . number_format($resolution_rate, 2) . '</td>
              </tr>';
}
$html .= '      </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>3. Category-wise Analysis</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Category</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($category_data as $data) {
    $html .= '<tr>
                <td>' . $data['month'] . '</td>
                <td>' . $data['Category'] . '</td>
                <td>' . $data['total_requests'] . '</td>
              </tr>';
}
$html .= '      </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>4. Room-wise Maintenance Trends</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($room_data as $data) {
    $html .= '<tr>
                <td>' . $data['room_id'] . '</td>
                <td>' . $data['total_requests'] . '</td>
              </tr>';
}
$html .= '      </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>5. Response Time Monitoring</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Days Open</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($response_time_data as $data) {
    $html .= '<tr>
                <td>' . $data['RequestID'] . '</td>
                <td>' . $data['RequestDate'] . '</td>
                <td>' . $data['Status'] . '</td>
                <td>' . $data['days_open'] . '</td>
              </tr>';
}
$html .= '      </tbody>
            </table>
        </div>
    </div>
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
$dompdf->stream("service_request_report.pdf", array("Attachment" => false));
?>
