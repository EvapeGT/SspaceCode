<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['isUserLoggedIn']) || $_SESSION['isUserLoggedIn'] !== true) {
    header('Location: ../index.html');
    exit();
}

// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the tenant's ID from the session data
$tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];

// Fetch unpaid water bills
$unpaidWaterBillsQuery = "SELECT wb.WaterBillID AS PaymentID, wb.BillDate AS PaymentDate, wb.WaterBillAmount AS PaymentAmount, 'Unpaid' AS PaymentStatus, 'Water Bill' AS BillType
FROM water_bills wb
INNER JOIN rooms r ON wb.room_id = r.room_id
INNER JOIN tenants t ON r.room_id = t.room_id
LEFT JOIN payments p ON wb.WaterBillID = p.WaterBillID AND p.PaymentStatus <> 'Paid'
WHERE t.TenantID = $tenantID AND p.PaymentID IS NULL";
$unpaidWaterBillsResult = $conn->query($unpaidWaterBillsQuery);
$unpaidWaterBillsData = $unpaidWaterBillsResult->fetch_all(MYSQLI_ASSOC);

// Fetch paid water bills
$paidWaterBillsQuery = "SELECT p.PaymentID, p.PaymentDate, p.PaymentAmount, p.PaymentStatus, 'Water Bill' AS BillType
FROM payments p
INNER JOIN tenants t ON p.TenantID = t.TenantID
WHERE t.TenantID = $tenantID AND p.PaymentStatus = 'Paid' AND p.WaterBillID IS NOT NULL";
$paidWaterBillsResult = $conn->query($paidWaterBillsQuery);
$paidWaterBillsData = $paidWaterBillsResult->fetch_all(MYSQLI_ASSOC);

// Fetch pending water bills
$pendingWaterBillsQuery = "SELECT p.PaymentID, p.PaymentDate, p.PaymentAmount, p.PaymentStatus, p.PaymentMethod, 'Water Bill' AS BillType
FROM payments p
INNER JOIN tenants t ON p.TenantID = t.TenantID
INNER JOIN water_bills wb ON p.WaterBillID = wb.WaterBillID
WHERE t.TenantID = $tenantID AND p.PaymentStatus = 'Pending'";
$pendingWaterBillsResult = $conn->query($pendingWaterBillsQuery);
$pendingWaterBillsData = $pendingWaterBillsResult->fetch_all(MYSQLI_ASSOC);

// Merge and sort all paid bills
$allPaidBills = $paidWaterBillsData;
usort($allPaidBills, function($a, $b) {
    return strtotime($a['PaymentDate']) - strtotime($b['PaymentDate']);
});

// Merge and sort all pending payments
$allPendingPayments = $pendingWaterBillsData;
usort($allPendingPayments, function($a, $b) {
    return strtotime($a['PaymentDate']) - strtotime($b['PaymentDate']);
});

// Calculate total unpaid, pending, and paid amounts
$totalUnpaidAmount = array_sum(array_column($unpaidWaterBillsData, 'PaymentAmount'));
$totalPendingAmount = array_sum(array_column($allPendingPayments, 'PaymentAmount'));
$totalPaidAmount = array_sum(array_column($paidWaterBillsData, 'PaymentAmount'));

// Calculate average water consumption for the tenant
$averageWaterConsumptionQuery = "SELECT AVG(wb.Consumption) AS average_consumption
FROM water_bills wb
INNER JOIN rooms r ON wb.room_id = r.room_id
INNER JOIN tenants t ON r.room_id = t.room_id
WHERE t.TenantID = $tenantID";
$averageWaterConsumptionResult = $conn->query($averageWaterConsumptionQuery);
$averageWaterConsumptionRow = $averageWaterConsumptionResult->fetch_assoc();
$averageWaterConsumption = $averageWaterConsumptionRow['average_consumption'];

// Fetch upcoming unpaid water bills
$upcomingUnpaidWaterBillsQuery = "SELECT wb.WaterBillID, wb.BillDate, wb.WaterBillAmount
                                  FROM water_bills wb
                                  INNER JOIN rooms r ON wb.room_id = r.room_id
                                  INNER JOIN tenants t ON r.room_id = t.room_id
                                  LEFT JOIN payments p ON wb.WaterBillID = p.WaterBillID AND p.PaymentStatus <> 'Paid'
                                  WHERE t.TenantID = $tenantID AND p.PaymentID IS NULL AND wb.BillDate > CURRENT_DATE()";
$upcomingUnpaidWaterBillsResult = $conn->query($upcomingUnpaidWaterBillsQuery);
$upcomingUnpaidWaterBillsData = $upcomingUnpaidWaterBillsResult->fetch_all(MYSQLI_ASSOC);

// Fetch water bill amounts grouped by month and year
$waterBillsQuery = "SELECT YEAR(BillDate) AS year, MONTH(BillDate) AS month, SUM(WaterBillAmount) AS total_amount
                    FROM water_bills
                    GROUP BY year, month
                    ORDER BY year, month";

$waterBillsResult = $conn->query($waterBillsQuery);

// Prepare arrays for chart data
$paymentDates = array();
$waterBillAmounts = array();

while ($row = $waterBillsResult->fetch_assoc()) {
    $paymentDate = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT) . '-01';
    $paymentDates[] = $paymentDate;
    $waterBillAmounts[] = $row['total_amount'];
}

// Convert the arrays to JSON for use in JavaScript
$paymentDatesJson = json_encode($paymentDates);
$waterBillAmountsJson = json_encode($waterBillAmounts);
include 'header.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Tenant Payment Page</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Water Payment Page</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="tenant_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Payment Page</li>
            </ol>
        </nav>
    </div>
    <div class="container">
    <div class="row">
        <!-- Upcoming Payment Deadlines -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Payment Deadlines</h5>
                    <?php if (!empty($upcomingUnpaidWaterBillsData)) { ?>
                        <div class="alert alert-warning" role="alert">
                            You have the following upcoming payment deadlines:
                            <ul class="mb-0">
                                <?php foreach ($upcomingUnpaidWaterBillsData as $upcomingBill) { ?>
                                    <li>Water Bill (₱<?php echo number_format($upcomingBill['WaterBillAmount'], 2); ?>) due on <?php echo date('M d, Y', strtotime($upcomingBill['BillDate'])); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted">You have no upcoming payment deadlines.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Average Water Consumption -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Water Consumption</h5>
                    <p class="lead mb-4">
                        <i class="fas fa-tint"></i> <!-- Water droplet icon -->
                        Average Water Consumption: <strong><?php echo number_format($averageWaterConsumption, 2); ?> m³</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

    
    <div class="card-body">
  <h5 class="card-title">Water Bill Payments <span>/ Monthly</span></h5>
  <!-- Bar Chart -->
  <div id="waterBillChart"></div>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const paymentDates = <?php echo $paymentDatesJson; ?>;
      const waterBillAmounts = <?php echo $waterBillAmountsJson; ?>;

      new ApexCharts(document.querySelector("#waterBillChart"), {
        series: [
          {
            name: 'Water Bill Amounts',
            data: waterBillAmounts,
          },
        ],
        chart: {
          type: 'bar',
          height: 350,
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent'],
        },
        xaxis: {
          categories: paymentDates,
          labels: {
            formatter: function(value) {
              return new Date(value).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }
          }
        },
        yaxis: {
          title: {
            text: 'Amount (in PHP)',
          },
          labels: {
            formatter: function(value) {
              return '₱' + value.toFixed(2);
            }
          }
        },
        fill: {
          opacity: 1,
          colors: ['#4154f1'],
        },
        tooltip: {
          y: {
            formatter: function(value) {
              return '₱' + value.toFixed(2);
            }
          }
        }
      }).render();
    });
  </script>
  <!-- End Bar Chart -->
</div>
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Unpaid Bills</h5>
                        <p class="lead mb-4">Total Unpaid Amount: <strong>₱<?php echo number_format($totalUnpaidAmount, 2); ?></strong></p>

                        <!-- Unpaid Bills Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Bill Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($unpaidWaterBillsData as $bill) {
                                    echo '<tr>';
                                    echo '<td>' . $bill['BillType'] . '</td>';
                                    echo '<td>' . $bill['PaymentDate'] . '</td>';
                                    echo '<td>₱' . number_format($bill['PaymentAmount'], 2) . '</td>';
                                    echo '<td>' . $bill['PaymentStatus'] . '</td>';
                                    echo '<td><a href="#" class="btn btn-primary btn-sm pay-now-btn"
                                    data-bs-toggle="modal" data-bs-target="#paymentModal"
                                    data-bill-type="' . $bill['BillType'] . '"
                                    data-bill-id="' . $bill['PaymentID'] . '"
                                    data-payment-amount="' . $bill['PaymentAmount'] . '">Pay Now</a> </td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Payments</h5>
                        <p class="lead mb-4">Total Pending Amount: <strong>₱<?php echo number_format($totalPendingAmount, 2); ?></strong></p>

                        <!-- Pending Payments Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allPendingPayments as $payment) {
                                    echo '<tr>';
                                    echo '<td>' . $payment['BillType'] . '</td>';
                                    echo '<td>' . $payment['PaymentDate'] . '</td>';
                                    echo '<td>₱' . number_format($payment['PaymentAmount'], 2) . '</td>';
                                    echo '<td>' . $payment['PaymentStatus'] . '</td>';
                                    echo '<td>' . $payment['PaymentMethod'] . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Paid Bills</h5>
                        <p class="lead mb-4">Total Paid Amount: <strong>₱<?php echo number_format($totalPaidAmount, 2); ?></strong></p>

                        <!-- Paid Bills Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allPaidBills as $bill) {
                                    echo '<tr>';
                                    echo '<td>' . $bill['BillType'] . '</td>';
                                    echo '<td>' . $bill['PaymentDate'] . '</td>';
                                    echo '<td>₱' . number_format($bill['PaymentAmount'], 2) . '</td>';
                                    echo '<td>' . $bill['PaymentStatus'] . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
               
            </div>
        </div>
    </section>
</main>
<!-- Modal for Payment Confirmation -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="billType" name="billType" value="Water">
                    <input type="hidden" id="billAmount" name="billAmount">
                    <input type="hidden" id="billID" name="billID">
                    <input type="hidden" id="tenantID" name="tenantID" value="<?php echo $tenantID; ?>">
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="paymentMethod">
                            <option value="Credit Card">Credit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Gcash">Gcash</option>
                            <option value="Paymaya">Paymaya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="referenceNumber" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="referenceNumber" name="referenceNumber" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receiptImage" class="form-label">Receipt Image</label>
                        <input type="file" class="form-control" id="receiptImage" name="receiptImage" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentSuccessModalLabel">Payment Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fa fa-check-circle fa-3x text-success"></i>
                <p class="mt-3">Your payment has been submitted successfully!</p>
            </div>
        </div>
    </div>
</div>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
</a>
<!-- Scripts -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.min.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/tinymce/tinymce.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/js/main.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {
            const payNowButtons = document.querySelectorAll('.pay-now-btn');
            payNowButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const billType = button.getAttribute('data-bill-type');
                    const billID = button.getAttribute('data-bill-id');
                    const billAmount = button.getAttribute('data-payment-amount');

                    document.getElementById('billType').value = billType;
                    document.getElementById('billID').value = billID;
                    document.getElementById('billAmount').value = billAmount;
                });
            });
            $('#paymentForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajax({
                url: 'payment_processor.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    let res = JSON.parse(response);
                    if (res.success) {
                        $('#paymentModal').modal('hide');
                        $('#paymentSuccessModal').modal('show');
                    } else {
                        alert(res.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while processing your payment. Please try again.');
                }
            });
        });

        // Reset form and data on modal hide
        $('#paymentModal').on('hidden.bs.modal', function () {
            $('#paymentForm')[0].reset();
        });

        // Remove backdrop on success modal hide
        $('#paymentSuccessModal').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
        });

        // Pay Now button click handler
        $('.pay-now-btn').on('click', function() {
            const billID = $(this).data('bill-id');
            const paymentAmount = $(this).data('payment-amount');

            $('#billID').val(billID);
            $('#billAmount').val(paymentAmount);
        });
    });
    </script>
 
</body>
</html>
