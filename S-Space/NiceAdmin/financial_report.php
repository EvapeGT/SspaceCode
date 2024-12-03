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

// Fetch data from the rent_payments table
$rent_sql = "SELECT
                SUM(PaymentAmount) AS total_rent_collected,
                AVG(PaymentAmount) AS average_monthly_rent,
                SUM(CASE WHEN PaymentMethod = 'Cash' THEN PaymentAmount ELSE 0 END) AS cash_payments,
                SUM(CASE WHEN PaymentMethod = 'Gcash' THEN PaymentAmount ELSE 0 END) AS gcash_payments,
                SUM(CASE WHEN PaymentMethod = 'PayPal' THEN PaymentAmount ELSE 0 END) AS paypal_payments,
                SUM(CASE WHEN PaymentStatus = 'Paid' THEN PaymentAmount ELSE 0 END) AS paid_rent,
                SUM(CASE WHEN PaymentStatus = 'Unpaid' THEN PaymentAmount ELSE 0 END) AS unpaid_rent
            FROM rent_payments
            WHERE PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH);";

$rent_result = $conn->query($rent_sql);

if ($rent_result->num_rows > 0) {
    $rent_row = $rent_result->fetch_assoc();
    $total_rent_collected = $rent_row['total_rent_collected'];
    $average_monthly_rent = $rent_row['average_monthly_rent'];
    $cash_rent_payments = $rent_row['cash_payments'];
    $gcash_rent_payments = $rent_row['gcash_payments'];
    $paypal_rent_payments = $rent_row['paypal_payments'];
    $paid_rent = $rent_row['paid_rent'];
    $unpaid_rent = $rent_row['unpaid_rent'];
} else {
    echo "No rent data found";
}

// Fetch data from the payments table for water bills
$water_sql = "SELECT
                SUM(PaymentAmount) AS total_water_collected,
                AVG(PaymentAmount) AS average_monthly_water,
                SUM(CASE WHEN PaymentMethod = 'Cash' THEN PaymentAmount ELSE 0 END) AS cash_water_payments,
                SUM(CASE WHEN PaymentMethod = 'Gcash' THEN PaymentAmount ELSE 0 END) AS gcash_water_payments,
                SUM(CASE WHEN PaymentMethod = 'PayPal' THEN PaymentAmount ELSE 0 END) AS paypal_water_payments,
                SUM(CASE WHEN PaymentStatus = 'Paid' THEN PaymentAmount ELSE 0 END) AS paid_water,
                SUM(CASE WHEN PaymentStatus = 'Unpaid' THEN PaymentAmount ELSE 0 END) AS unpaid_water
            FROM payments
            WHERE PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH);";

$water_result = $conn->query($water_sql);

if ($water_result->num_rows > 0) {
    $water_row = $water_result->fetch_assoc();
    $total_water_collected = $water_row['total_water_collected'];
    $average_monthly_water = $water_row['average_monthly_water'];
    $cash_water_payments = $water_row['cash_water_payments'];
    $gcash_water_payments = $water_row['gcash_water_payments'];
    $paypal_water_payments = $water_row['paypal_water_payments'];
    $paid_water = $water_row['paid_water'];
    $unpaid_water = $water_row['unpaid_water'];
} else {
    echo "No water payment data found";
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
    <title>Financial Report</title>
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
    <h1>S-Space Tenant Portal Financial Report</h1>

    <div class="section">
        <h2>1. Total Rent Collected (Last 12 Months)</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . number_format($total_rent_collected, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>2. Average Monthly Rent Collected</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . number_format($average_monthly_rent, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>3. Rent Payment Methods</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Percentage (%)</th>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cash</td>
                        <td>' . number_format(($cash_rent_payments / $total_rent_collected) * 100, 2) . '</td>
                        <td>' . number_format($cash_rent_payments, 2) . '</td>
                    </tr>
                    <tr>
                        <td>GCash</td>
                        <td>' . number_format(($gcash_rent_payments / $total_rent_collected) * 100, 2) . '</td>
                        <td>' . number_format($gcash_rent_payments, 2) . '</td>
                    </tr>
                    <tr>
                        <td>PayPal</td>
                        <td>' . number_format(($paypal_rent_payments / $total_rent_collected) * 100, 2) . '</td>
                        <td>' . number_format($paypal_rent_payments, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>4. Rent Payment Status</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Percentage (%)</th>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Paid</td>
                        <td>' . number_format(($paid_rent / $total_rent_collected) * 100, 2) . '</td>
                        <td>' . number_format($paid_rent, 2) . '</td>
                    </tr>
                    <tr>
                        <td>Unpaid</td>
                        <td>' . number_format(($unpaid_rent / $total_rent_collected) * 100, 2) . '</td>
                        <td>' . number_format($unpaid_rent, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>5. Total Water Payments Collected (Last 12 Months)</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . number_format($total_water_collected, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>6. Average Monthly Water Payments Collected</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . number_format($average_monthly_water, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>7. Water Payment Methods</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Percentage (%)</th>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cash</td>
                        <td>' . number_format(($cash_water_payments / $total_water_collected) * 100, 2) . '</td>
                        <td>' . number_format($cash_water_payments, 2) . '</td>
                    </tr>
                    <tr>
                        <td>GCash</td>
                        <td>' . number_format(($gcash_water_payments / $total_water_collected) * 100, 2) . '</td>
                        <td>' . number_format($gcash_water_payments, 2) . '</td>
                    </tr>
                    <tr>
                        <td>PayPal</td>
                        <td>' . number_format(($paypal_water_payments / $total_water_collected) * 100, 2) . '</td>
                        <td>' . number_format($paypal_water_payments, 2) . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <h2>8. Water Payment Status</h2>
        <div class="content">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Percentage (%)</th>
                        <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Paid</td>
                        <td>' . number_format(($paid_water / $total_water_collected) * 100, 2) . '</td>
                        <td>' . number_format($paid_water, 2) . '</td>
                    </tr>
                    <tr>
                        <td>Unpaid</td>
                        <td>' . number_format(($unpaid_water / $total_water_collected) * 100, 2) . '</td>
                        <td>' . number_format($unpaid_water, 2) . '</td>
                    </tr>
                </tbody>
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
$dompdf->stream("financial_report.pdf", array("Attachment" => false));
?>
