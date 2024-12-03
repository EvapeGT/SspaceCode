<?php
//index.php for tenant
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  
if (!isset($_SESSION['isUserLoggedIn']) || $_SESSION['isUserLoggedIn'] !== true) {
    header('Location: ../index.html');
    exit();
    }

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the tenant's first name from the session data
$tenantFirstName = $_SESSION['tenantTenantData'][0]['FirstName'];

// Calculate the total amount due from the invoice data
$totalAmountDue = 0;
 include 'header.inc.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Tenant Dashhboard</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <!-- ======================================================= -->
  <style>
    
  </style>
</head>

<body>

<main id="main" class="main">
        <div class="pagetitle">
            <h1>Welcome, <?php echo $tenantFirstName; ?></h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="tenant_dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->


        <section class="section dashboard">
            <div class="row">
                <!-- Left side columns -->
                <div class="col-lg-10">
                    <div class="row">
                       <!-- Unpaid Rent Card -->
<div class="col-xxl-4 col-md-6">
    <div class="card info-card revenue-card">
        <div class="card-body">
            <h5 class="card-title">Unpaid Rent</h5>
            <?php
            $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];
            $unpaidRentQuery = "SELECT COUNT(*) as unpaidRentCount
                                FROM rent_payments
                                WHERE TenantID = $tenantID AND PaymentStatus = 'Unpaid'";
            $unpaidRentResult = $conn->query($unpaidRentQuery);
            $unpaidRentCount = $unpaidRentResult->fetch_assoc()['unpaidRentCount'];

            if ($unpaidRentCount > 0) {
                echo '<div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>You have ' . $unpaidRentCount . ' unpaid rent payment(s)</h6>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unpaidRentModal">
                                View Unpaid Rent
                            </button>
                        </div>
                    </div>';
            } else {
                echo '<div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>No unpaid rent</h6>
                        </div>
                    </div>';
            }
            ?>
        </div>
    </div>
</div>
<!-- End Unpaid Rent Card -->
      <!-- Unpaid Water Bills Card -->
<div class="col-xxl-6 col-md-6">
    <div class="card info-card revenue-card">
        <div class="card-body">
            <h5 class="card-title">Unpaid Water Bills</h5>
            <?php
            $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];
            $unpaidBillsQuery = "SELECT COUNT(*) as unpaidBillsCount
                                 FROM water_bills wb
                                 INNER JOIN rooms r ON wb.room_id = r.room_id
                                 INNER JOIN tenants t ON r.room_id = t.room_id
                                 LEFT JOIN payments p ON wb.WaterBillID = p.WaterBillID AND p.PaymentStatus <> 'Paid'
                                 WHERE t.TenantID = $tenantID AND p.PaymentID IS NULL";
            $unpaidBillsResult = $conn->query($unpaidBillsQuery);
            $unpaidBillsCount = $unpaidBillsResult->fetch_assoc()['unpaidBillsCount'];

            if ($unpaidBillsCount > 0) {
                echo '<div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>You have ' . $unpaidBillsCount . ' unpaid water bill(s)</h6>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unpaidWaterBillsModal">
                                View Unpaid Water Bills
                            </button>
                        </div>
                    </div>';
            } else {
                echo '<div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>No unpaid water bills</h6>
                        </div>
                    </div>';
            }
            ?>
        </div>
    </div>
</div>
<!-- Unpaid Water Bills Modal -->
<div class="modal fade" id="unpaidWaterBillsModal" tabindex="-1" aria-labelledby="unpaidWaterBillsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unpaidWaterBillsModalLabel">Unpaid Water Bills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <?php
$unpaidBillsQuery = "SELECT wb.WaterBillID, wb.BillDate, wb.WaterBillAmount
                     FROM water_bills wb
                     INNER JOIN rooms r ON wb.room_id = r.room_id
                     INNER JOIN tenants t ON r.room_id = t.room_id
                     LEFT JOIN payments p ON wb.WaterBillID = p.WaterBillID AND p.PaymentStatus = 'Unpaid'
                     WHERE t.TenantID = $tenantID AND p.PaymentID IS NULL";

$unpaidBillsResult = $conn->query($unpaidBillsQuery);

if ($unpaidBillsResult->num_rows > 0) {
    while ($unpaidBill = $unpaidBillsResult->fetch_assoc()) {
        echo '<div class="d-flex align-items-center mb-3">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h6>Due Date: ' . $unpaidBill['BillDate'] . '</h6>
                    <span class="text-muted small pt-1 fw-bold">Amount: ₱' . number_format($unpaidBill['WaterBillAmount'], 2) . '</span>
                </div>
            </div>';
    }
} else {
    echo '<p>No unpaid water bills</p>';
}
?>
            </div>
        </div>
    </div>
</div>
<!-- End Unpaid Water Bills Modal -->
                        <!-- Room Details Card -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card revenue-card">
                                <div class="card-body">
                                    <h5 class="card-title">Room Details</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-door-open"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>Room No. <?php echo $_SESSION['tenantRoomData'][0]['room_id']; ?></h6>
                                            <span class="text-muted small pt-1 fw-bold">Rent: ₱<?php echo number_format($_SESSION['tenantRoomData'][0]['price'], 2); ?>/month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Room Details Card -->
                       <!-- Service Requests Card -->
<div class="col-xxl-4 col-md-6">
    <div class="card info-card customers-card">
        <div class="card-body">
            <h5 class="card-title">Service Requests</h5>
            <?php
            $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];
            $serviceRequestsQuery = "SELECT RequestID, IssueDescription, Status, Category
                                     FROM servicerequests
                                     WHERE TenantID = $tenantID";
            $serviceRequestsResult = $conn->query($serviceRequestsQuery);

            if ($serviceRequestsResult->num_rows > 0) {
                // Button to open the modal
                echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceRequestsModal">
                        View your maintenance requests
                      </button>';

                // Modal structure
                echo '<div class="modal fade" id="serviceRequestsModal" tabindex="-1" aria-labelledby="serviceRequestsModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="serviceRequestsModalLabel">Maintenance Requests</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">';
                
                // Loop through service requests and display them in the modal
                while ($serviceRequest = $serviceRequestsResult->fetch_assoc()) {
                    echo '<div class="d-flex align-items-center mb-3">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div>
                                <h6>' . $serviceRequest['Category'] . '</h6>
                                <span class="text-muted">' . $serviceRequest['IssueDescription'] . '</span><br>
                                <span class="text-muted">Status: ' . $serviceRequest['Status'] . '</span>
                            </div>
                        </div>';
                }

                echo '      </div>
                            </div>
                        </div>
                    </div>';
            } else {
                echo '<p>No service requests</p>';
            }
            ?>
        </div>
    </div>
</div>


                        <!-- Right side columns -->
                <div class="col-lg-4">
                    
                </div><!-- End Right side columns -->
            </div>
                        <div class="card-body">
                        <h5 class="card-title">Water Consumption <span>/Monthly</span></h5>
<!-- Bar Chart -->
<div id="reportsChart"></div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    <?php
    // Fetch water bill data grouped by month and year
    $waterBillsQuery = "SELECT YEAR(BillDate) AS year, MONTH(BillDate) AS month, SUM(WaterBillAmount) AS total_amount
                        FROM water_bills
                        GROUP BY YEAR(BillDate), MONTH(BillDate)
                        ORDER BY year, month";
    $waterBillsResult = $conn->query($waterBillsQuery);

    $monthlyBills = array();
    $monthlyBillDates = array();

    while ($row = $waterBillsResult->fetch_assoc()) {
        $month = $row['month'];
        $year = $row['year'];
        $amount = $row['total_amount'];
        $monthName = date("M", mktime(0, 0, 0, $month, 1));
        $monthYear = $monthName . " " . $year;

        $monthlyBills[] = $amount;
        $monthlyBillDates[] = $monthYear;
    }

    $monthlyBillsJson = json_encode($monthlyBills);
    $monthlyBillDatesJson = json_encode($monthlyBillDates);
    ?>

    new ApexCharts(document.querySelector("#reportsChart"), {
        series: [{
            name: 'Monthly Water Bills',
            data: <?php echo $monthlyBillsJson; ?>
        }],
        chart: {
            height: 350,
            type: 'bar',  // Change here from 'line' to 'bar'
            toolbar: {
                show: false
            },
        },
        colors: ['#4154f1'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: 2
        },
        xaxis: {
            categories: <?php echo $monthlyBillDatesJson; ?>
        },
        tooltip: {
            x: {
                format: 'MMM yyyy'
            }
        }
    }).render();
});
</script>
<!-- End Bar Chart -->

                        <div class="card-body">
                  <h5 class="card-title">Maintenance Ticket Request <span>/Monthly</span></h5>
                  <!-- Stacked Bar Chart -->
<canvas id="stakedBarChart" style="max-height: 400px;"></canvas>
<script>
document.addEventListener("DOMContentLoaded", () => {
    <?php
    // Fetch service request data grouped by month and year
    $serviceRequestsQuery = "SELECT YEAR(RequestDate) AS year, MONTH(RequestDate) AS month, COUNT(*) AS total_requests
                             FROM servicerequests
                             WHERE TenantID = {$_SESSION['tenantTenantData'][0]['TenantID']}
                             GROUP BY YEAR(RequestDate), MONTH(RequestDate)
                             ORDER BY year, month";
    $serviceRequestsResult = $conn->query($serviceRequestsQuery);

    $monthlyRequests = array();
    $monthlyRequestDates = array();

    while ($row = $serviceRequestsResult->fetch_assoc()) {
        $month = $row['month'];
        $year = $row['year'];
        $count = $row['total_requests'];
        $monthName = date("M", mktime(0, 0, 0, $month, 1));
        $monthYear = $monthName . " " . $year;

        $monthlyRequests[] = $count;
        $monthlyRequestDates[] = $monthYear;
    }

    $monthlyRequestsJson = json_encode($monthlyRequests);
    $monthlyRequestDatesJson = json_encode($monthlyRequestDates);
    ?>

    new Chart(document.querySelector('#stakedBarChart'), {
        type: 'bar',
        data: {
            labels: <?php echo $monthlyRequestDatesJson; ?>,
            datasets: [{
                label: 'Service Requests',
                data: <?php echo $monthlyRequestsJson; ?>,
                backgroundColor: 'rgb(44, 202, 106)'
            }]
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Service Requests'
                }
            },
            responsive: true,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true
                }
            }
        }
    });
});
</script>
<!-- End Stacked Bar Chart -->
<!-- Unpaid Rent Modal -->
<div class="modal fade" id="unpaidRentModal" tabindex="-1" aria-labelledby="unpaidRentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unpaidRentModalLabel">Unpaid Rent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                $unpaidRentQuery = "SELECT RentPaymentID, PaymentAmount, PaymentMethod, ReferenceNumber, PaymentStatus
                                    FROM rent_payments
                                    WHERE TenantID = $tenantID AND PaymentStatus = 'Unpaid'";
                $unpaidRentResult = $conn->query($unpaidRentQuery);

                if ($unpaidRentResult->num_rows > 0) {
                    while ($unpaidRent = $unpaidRentResult->fetch_assoc()) {
                        echo '<div class="d-flex align-items-center mb-3">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h6>Rent Payment ID: ' . $unpaidRent['RentPaymentID'] . '</h6>
                                    <span class="text-muted small pt-1 fw-bold">Amount: ₱' . number_format($unpaidRent['PaymentAmount'], 2) . '</span><br>
                                    <span class="text-muted small pt-1 fw-bold">Method: ' . $unpaidRent['PaymentMethod'] . '</span><br>
                                    <span class="text-muted small pt-1 fw-bold">Reference: ' . $unpaidRent['ReferenceNumber'] . '</span>
                                </div>
                            </div>';
                    }
                } else {
                    echo '<p>No unpaid rent</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- End Unpaid Rent Modal -->

                        <!-- Recent Payments -->
<div class="col-12">
    <div class="card recent-sales overflow-auto">
        <div class="card-body">
            <h5 class="card-title">Recent Payments</h5>
            <table class="table table-borderless datatable">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Payment Date</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Method</th>
                        <th scope="col">Reference</th>
                        <th scope="col">Status</th>
                        <th scope="col">Type</th>
                        <th scope="col">Associated Tenant(s)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paymentIndex = 1;
                    $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];

                    // Fetch rent payments
                    $rentPaymentsQuery = "SELECT rp.*, t.FirstName, t.LastName 
                                          FROM rent_payments rp
                                          INNER JOIN tenants t ON rp.TenantID = t.TenantID
                                          WHERE rp.TenantID = $tenantID";
                    $rentPaymentsResult = $conn->query($rentPaymentsQuery);

                    while ($rentPayment = $rentPaymentsResult->fetch_assoc()) {
                        echo '<tr>';
                        echo '<th scope="row">' . $paymentIndex . '</th>';
                        echo '<td>' . $rentPayment['PaymentDate'] . '</td>';
                        echo '<td>₱' . number_format($rentPayment['PaymentAmount'], 2) . '</td>';
                        echo '<td>' . $rentPayment['PaymentMethod'] . '</td>';
                        echo '<td>' . $rentPayment['ReferenceNumber'] . '</td>';
                        echo '<td><span class="badge bg-' . ($rentPayment['PaymentStatus'] === 'Paid' ? 'success' : 'warning') . '">' . $rentPayment['PaymentStatus'] . '</span></td>';
                        echo '<td>Rent</td>';
                        echo '<td>' . $rentPayment['FirstName'] . ' ' . $rentPayment['LastName'] . '</td>';
                        echo '</tr>';
                        $paymentIndex++;
                    }

                    // Fetch water bill payments
                    $waterBillPaymentsQuery = "SELECT p.*, wb.BillDate, r.room_id, t.FirstName, t.LastName
                                               FROM payments p
                                               INNER JOIN water_bills wb ON p.WaterBillID = wb.WaterBillID
                                               INNER JOIN rooms r ON wb.room_id = r.room_id
                                               INNER JOIN tenants t ON p.TenantID = t.TenantID
                                               WHERE p.TenantID = $tenantID";
                    $waterBillPaymentsResult = $conn->query($waterBillPaymentsQuery);

                    while ($waterBillPayment = $waterBillPaymentsResult->fetch_assoc()) {
                        echo '<tr>';
                        echo '<th scope="row">' . $paymentIndex . '</th>';
                        echo '<td>' . $waterBillPayment['PaymentDate'] . '</td>';
                        echo '<td>₱' . number_format($waterBillPayment['PaymentAmount'], 2) . '</td>';
                        echo '<td>' . $waterBillPayment['PaymentMethod'] . '</td>';
                        echo '<td>' . $waterBillPayment['referenceNumber'] . '</td>';
                        echo '<td><span class="badge bg-' . ($waterBillPayment['PaymentStatus'] === 'Paid' ? 'success' : 'warning') . '">' . $waterBillPayment['PaymentStatus'] . '</span></td>';
                        echo '<td>Water Bill (' . $waterBillPayment['BillDate'] . ')</td>';
                        echo '<td>' . $waterBillPayment['FirstName'] . ' ' . $waterBillPayment['LastName'] . '</td>';
                        echo '</tr>';
                        $paymentIndex++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- End Recent Payments -->
                    </div>
                </div><!-- End Left side columns -->
        </section>
    </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>S-Space</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
      Designed by <a href="https://www.facebook.com/monay.maykagat">Rhussel Combo</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>



</body>

</html>