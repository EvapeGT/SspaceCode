<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
//index.php
// Check if the session variable 'isAdminLoggedIn' is set and true
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  // If not, redirect to the login page or show an error
  header('Location: ../index.html');
  exit();
}
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$monthlyEarnings = [];

foreach ($_SESSION['paymentsData'] as $payment) {
  // Check if 'PaymentDate' and 'PaymentStatus' exist in the payment array
  if (!isset($payment['PaymentDate']) || !isset($payment['PaymentStatus'])) {
      // Skip this payment or handle the missing key appropriately
      continue;
  }

  // Only include payments with a status of 'Paid'
  if ($payment['PaymentStatus'] !== 'Paid') {
      continue;
  }

  // Use the payment date (YYYY-MM-DD) as the key for grouping
  $yearMonth = substr($payment['PaymentDate'], 0, 7); // yyyy-mm

  // Initialize the month in the array if not already present
  if (!isset($monthlyEarnings[$yearMonth])) {
      $monthlyEarnings[$yearMonth] = 0;
  }

  // Add the payment amount to the month's total
  $monthlyEarnings[$yearMonth] += floatval($payment['PaymentAmount']);
}

// Sort the monthly earnings array by year and month
ksort($monthlyEarnings);

// Convert the sorted data into arrays for JSON encoding
$paymentAmounts = array_values($monthlyEarnings);
$paymentDates = array_keys($monthlyEarnings);

// Convert the PHP arrays into JSON
$paymentAmountsJson = json_encode($paymentAmounts);
$paymentDatesJson = json_encode($paymentDates);



//This is for the Pending Payment Chart
$statusCounts = [
  'Pending' => 0,
  'Unpaid' => 0,
  'Paid' => 0
];
$statusColors = [
  'Pending' => '#edb95e', // Yellow
  'Unpaid' => '#e23636', // Red
  'Paid' => '#82dd55', // Green
];

// Loop through each payment and increment the appropriate counter
foreach ($_SESSION['paymentsData'] as $payment) {
  if (isset($statusCounts[$payment['PaymentStatus']])) {
      $statusCounts[$payment['PaymentStatus']]++;
  }
}

$chartData = [];
foreach ($statusCounts as $status => $count) {
  $chartData[] = [
      'value' => $count,
      'name' => $status,
      'itemStyle' => [
          'color' => $statusColors[$status] ?? '#000000' // Default to black if status not found
      ]
  ];
}

// Convert the PHP array to JSON
$chartDataJson = json_encode($chartData);
$rentPaymentStatusQuery = "SELECT PaymentStatus, COUNT(*) AS count
                           FROM rent_payments
                           GROUP BY PaymentStatus";

$rentPaymentStatusResult = $conn->query($rentPaymentStatusQuery);

$rentPaymentStatusCounts = [
  'Pending' => 0,
  'Unpaid' => 0,
  'Paid' => 0
];

while ($row = $rentPaymentStatusResult->fetch_assoc()) {
    $status = $row['PaymentStatus'];
    $count = $row['count'];
    if (isset($rentPaymentStatusCounts[$status])) {
        $rentPaymentStatusCounts[$status] = $count;
    }
}

$rentPaymentChartData = [];
foreach ($rentPaymentStatusCounts as $status => $count) {
  $rentPaymentChartData[] = [
      'value' => $count,
      'name' => $status,
      'itemStyle' => [
          'color' => $statusColors[$status] ?? '#000000' // Use the same colors as the first chart
      ]
  ];
}

// Convert the PHP array to JSON
$rentPaymentChartDataJson = json_encode($rentPaymentChartData);

?>

<?php

function getRecentActivity($limit = 10) {
  $host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

  $activities = array();

  // Fetch recent payments
  $paymentsQuery = "SELECT 'payment' AS type, tp.FirstName, tp.LastName, p.PaymentAmount, p.PaymentDate, p.PaymentStatus, p.PaymentDate AS timestamp
                    FROM payments p
                    JOIN tenants tp ON p.TenantID = tp.TenantID
                    ORDER BY p.PaymentDate DESC
                    LIMIT $limit";
  $paymentsResult = $conn->query($paymentsQuery);
  while ($row = $paymentsResult->fetch_assoc()) {
      $activity = array(
          'type' => $row['type'],
          'content' => "{$row['FirstName']} {$row['LastName']} made a water payment of ₱{$row['PaymentAmount']} ({$row['PaymentStatus']})",
          'timestamp' => $row['timestamp']
      );
      $activities[] = $activity;
  }

  // Fetch recent rent payments
  $rentPaymentsQuery = "SELECT 'rent_payment' AS type, tp.FirstName, tp.LastName, rp.PaymentAmount, rp.PaymentDate, rp.PaymentStatus, rp.PaymentDate AS timestamp
                        FROM rent_payments rp
                        JOIN tenants tp ON rp.TenantID = tp.TenantID
                        ORDER BY rp.PaymentDate DESC
                        LIMIT $limit";
  $rentPaymentsResult = $conn->query($rentPaymentsQuery);
  while ($row = $rentPaymentsResult->fetch_assoc()) {
      $activity = array(
          'type' => $row['type'],
          'content' => "{$row['FirstName']} {$row['LastName']} made a rent payment of ₱{$row['PaymentAmount']} ({$row['PaymentStatus']})",
          'timestamp' => $row['timestamp']
      );
      $activities[] = $activity;
  }

  // Fetch recent service requests (assuming you have a table named 'servicerequests')
  $serviceRequestsQuery = "SELECT 'service_request' AS type, tp.FirstName, tp.LastName, sr.IssueDescription, sr.RequestDate, sr.Status, sr.RequestDate AS timestamp
                           FROM servicerequests sr
                           JOIN tenants tp ON sr.TenantID = tp.TenantID
                           ORDER BY sr.RequestDate DESC
                           LIMIT $limit";
  $serviceRequestsResult = $conn->query($serviceRequestsQuery);
  while ($row = $serviceRequestsResult->fetch_assoc()) {
      $activity = array(
          'type' => $row['type'],
          'content' => "{$row['FirstName']} {$row['LastName']} submitted a service request: {$row['IssueDescription']} ({$row['Status']})",
          'timestamp' => $row['timestamp']
      );
      $activities[] = $activity;
  }

  // Sort activities by timestamp in descending order
  usort($activities, function($a, $b) {
      return strtotime($b['timestamp']) - strtotime($a['timestamp']);
  });

  return array_slice($activities, 0, $limit);
}

function formatTimestamp($timestamp) {
  $now = new DateTime();
  $activityTime = new DateTime($timestamp);
  $diff = $now->diff($activityTime);

  if ($diff->y > 0) {
      return $diff->y . ' year(s) ago';
  } elseif ($diff->m > 0) {
      return $diff->m . ' month(s) ago';
  } elseif ($diff->d > 0) {
      return $diff->d . ' day(s) ago';
  } elseif ($diff->h > 0) {
      return $diff->h . ' hour(s) ago';
  } else {
      return $diff->i . ' minute(s) ago';
  }
}

function getActivityStatusColor($type) {
  switch ($type) {
      case 'payment':
          return 'success';
      case 'rent_payment':
          return 'primary';
      case 'service_request':
          return 'warning';
      default:
          return 'muted';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - NiceAdmin Bootstrap Template</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/dormlogo.png" rel="icon">
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
    .bi-currency-peso::before {
    content: "\20b1"; /* Unicode for the Philippine Peso sign */
}

  </style>
</head>

<body>
<?php
include 'header.inc.php';
?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Max Capacity<span>| Today</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>100</h6>
                      <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span>

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <?php

// Query to get total revenue from payments table
$paymentRevenueQuery = "SELECT SUM(PaymentAmount) AS TotalPaymentRevenue
                        FROM Payments
                        WHERE PaymentStatus = 'Paid'";

$paymentRevenueResult = $conn->query($paymentRevenueQuery);
$paymentRevenueData = $paymentRevenueResult->fetch_assoc();
$totalPaymentRevenue = $paymentRevenueData['TotalPaymentRevenue'];

// Query to get total revenue from rent_payments table
$rentPaymentRevenueQuery = "SELECT SUM(PaymentAmount) AS TotalRentPaymentRevenue
                            FROM rent_payments
                            WHERE PaymentStatus = 'Paid'";

$rentPaymentRevenueResult = $conn->query($rentPaymentRevenueQuery);
$rentPaymentRevenueData = $rentPaymentRevenueResult->fetch_assoc();
$totalRentPaymentRevenue = $rentPaymentRevenueData['TotalRentPaymentRevenue'];

// Calculate the total revenue
$totalRevenue = $totalPaymentRevenue + $totalRentPaymentRevenue;
?>

<!-- Revenue Card -->
<div class="col-xxl-4 col-md-6">
    <div class="card info-card revenue-card">
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
            </ul>
        </div>
        <div class="card-body">
            <h5 class="card-title">Total Revenue</h5>
            <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-currency-peso"></i>
                </div>
                <div class="ps-3">
                    <h6><?php echo "₱" . number_format($totalRevenue, 2); ?></h6>
                    <span class="text-success small pt-1 fw-bold">8%</span> <span class="text-muted small pt-2 ps-1">increase</span>
                </div>
            </div>
        </div>
    </div>
</div><!-- End Revenue Card -->



            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>
                <div class="card-body">
  <h5 class="card-title">Occupancy <span>| West..</span></h5>

  <div class="d-flex align-items-center">
    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
      <i class="bi bi-people"></i>
    </div>
    <div class="ps-3">
    <h6>17 / 100</h6>
      <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span>
    </div>
  </div>
</div>
              </div>

            </div><!-- End Customers Card -->
             <!-- Generate Reports Section -->
  <div class="row mt-4">
    <div class="col-12">
      <h2>Generate Reports</h2>
      <div class="d-grid gap-2 d-md-flex justify-content-md-start">
        <a class="btn btn-primary" href="service_report.php" target="_blank">Maintenance Request Report</a>
        <a class="btn btn-secondary" href="financial_report.php" target="_blank">Finance Report</a>
        <a class="btn btn-info" href="dashboard_report.php" target="_blank">Dashboard Report</a>
        <a class="btn btn-success" href="contract_report.php" target="_blank">Contract Report</a>
        
      </div>
    </div>
  </div>
  <!-- End Generate Reports Section -->

            <!-- Reports -->
            <div class="col-12">
              <div class="card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>
<?php

// Query to get monthly rent earnings by payment status
$query = "SELECT DATE_FORMAT(PaymentDate, '%Y-%m') AS month_year,
                 SUM(CASE WHEN PaymentStatus = 'Paid' THEN PaymentAmount ELSE 0 END) AS paid_earnings,
                 SUM(CASE WHEN PaymentStatus = 'Pending' THEN PaymentAmount ELSE 0 END) AS pending_earnings,
                 SUM(CASE WHEN PaymentStatus = 'Unpaid' THEN PaymentAmount ELSE 0 END) AS unpaid_earnings
          FROM rent_payments
          GROUP BY month_year
          ORDER BY month_year ASC";

$result = $conn->query($query);

// Initialize arrays to store data for the chart
$paymentDates = [];
$paidEarnings = [];
$pendingEarnings = [];
$unpaidEarnings = [];

// Loop through the results and populate the arrays
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentDates[] = $row['month_year'];
        $paidEarnings[] = $row['paid_earnings'];
        $pendingEarnings[] = $row['pending_earnings'];
        $unpaidEarnings[] = $row['unpaid_earnings'];
    }
}

// Close the database connection
$conn->close();

// Convert the PHP arrays into JSON
$paymentDatesJson = json_encode($paymentDates);
$paidEarningsJson = json_encode($paidEarnings);
$pendingEarningsJson = json_encode($pendingEarnings);
$unpaidEarningsJson = json_encode($unpaidEarnings);

?>
               <div class="card-body">
  <h5 class="card-title">Rent Earnings Reports <span>/ Monthly</span></h5>
  <!-- Bar Chart -->
<div id="reportsChart"></div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const paymentDates = <?php echo $paymentDatesJson; ?>;
  const paidEarnings = <?php echo $paidEarningsJson; ?>;
  const pendingEarnings = <?php echo $pendingEarningsJson; ?>;
  const unpaidEarnings = <?php echo $unpaidEarningsJson; ?>;

  new ApexCharts(document.querySelector("#reportsChart"), {
    series: [
      {
        name: 'Paid Earnings',
        data: paidEarnings,
      },
      {
        name: 'Pending Earnings',
        data: pendingEarnings,
      },
      {
        name: 'Unpaid Earnings',
        data: unpaidEarnings,
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
          // Format the x-axis labels as desired
          return new Date(value).toLocaleDateString();
        }
      }
    },
    yaxis: {
      title: {
        text: 'Amount (in thousands)',
      },
      labels: {
        formatter: function(value) {
          return value / 1000 + 'k';
        }
      }
    },
    fill: {
      opacity: 1,
      colors: ['#4154f1', '#2eca6a', '#ff771d'], // Custom colors for the bars
    },
    tooltip: {
      y: {
        formatter: function(value) {
          return "$" + value.toFixed(2);
        }
      }
    }
  }).render();
});
</script>
<!-- End Bar Chart -->
                </div>

              </div>
              
            </div><!-- End Reports -->

           <!-- Recent Sales -->
<div class="col-12">
    <div class="card recent-sales overflow-auto">
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
            </ul>
        </div>
        <div class="card-body">
            <h5 class="card-title">Recent Sales <span>| Today</span></h5>
            <table class="table table-borderless datatable">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
                   // Fetch data from payments table
$paymentsSql = "SELECT p.PaymentID, CONCAT(t.FirstName, ' ', t.LastName) AS Customer, p.PaymentAmount AS Price, p.PaymentStatus AS Status
FROM payments p
JOIN tenants t ON p.TenantID = t.TenantID
ORDER BY p.PaymentDate DESC";
$paymentsResult = $conn->query($paymentsSql);

// Fetch data from rent_payments table
$rentPaymentsSql = "SELECT rp.RentPaymentID, CONCAT(t.FirstName, ' ', t.LastName) AS Customer, rp.PaymentAmount AS Price, rp.PaymentStatus AS Status
    FROM rent_payments rp
    JOIN tenants t ON rp.TenantID = t.TenantID
    ORDER BY rp.PaymentDate DESC";
$rentPaymentsResult = $conn->query($rentPaymentsSql);

while (($paymentRow = $paymentsResult->fetch_assoc()) || ($rentPaymentRow = $rentPaymentsResult->fetch_assoc())) {
if ($paymentRow) {
$row = $paymentRow;
$id = $row['PaymentID'];
$customer = $row['Customer'];
$product = 'Water Bill Payment';
$price = $row['Price'];
$status = $row['Status'];
$statusBadgeClass = $status == 'Paid' ? 'bg-success' : ($status == 'Pending' ? 'bg-warning' : 'bg-danger');
echo "<tr>";
echo "<th scope='row'><a href='#'>#$id</a></th>";
echo "<td>$customer</td>";
echo "<td><a href='#' class='text-primary'>$product</a></td>";
echo "<td>$price</td>";
echo "<td><span class='badge $statusBadgeClass'>$status</span></td>";
echo "</tr>";
} elseif ($rentPaymentRow) {
$row = $rentPaymentRow;
$id = $row['RentPaymentID'];
$customer = $row['Customer'];
$price = $row['Price'];
$status = $row['Status'];
$statusBadgeClass = $status == 'Paid' ? 'bg-success' : ($status == 'Pending' ? 'bg-warning' : 'bg-danger');
echo "<tr>";
echo "<th scope='row'><a href='#'>#$id</a></th>";
echo "<td>$customer</td>";
echo "<td><a href='#' class='text-primary'>Rent Payment</a></td>";
echo "<td>$price</td>";
echo "<td><span class='badge $statusBadgeClass'>$status</span></td>";
echo "</tr>";
}
}
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- End Recent Sales -->

           

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">
         <!-- Recent Activity -->
<div class="card">
    <!-- ... other code ... -->
    <div class="card-body">
        <h5 class="card-title">Recent Activity <span>| Today</span></h5>
        <div class="activity">
           <?php
$recentActivities = getRecentActivity();
foreach ($recentActivities as $activity) {
    $activityClass = 'activity-' . $activity['type'];
    $activityStatusColor = getActivityStatusColor($activity['type']);
    echo '<div class="activity-item d-flex ' . $activityClass . '">';
    echo '<div class="activite-label">' . formatTimestamp($activity['timestamp']) . '</div>';
    echo '<i class="bi bi-circle-fill activity-badge text-' . $activityStatusColor . ' align-self-start"></i>';
    echo '<div class="activity-content">' . $activity['content'] . '</div>';
    echo '</div>';
}
           ?>
                <h6>No recent activities found.</h6>
        </div>
    </div>
</div>


          <!-- Website Traffic -->
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">Water Payment Status <span>| Today</span></h5>

              <div id="trafficChart" style="min-height: 400px;" class="echart"></div>
              <script>
document.addEventListener("DOMContentLoaded", () => {
  const paymentStatusData = <?php echo $chartDataJson; ?>;
  const rentPaymentStatusData = <?php echo $rentPaymentChartDataJson; ?>;

  echarts.init(document.querySelector("#trafficChart")).setOption({
    // ... (existing chart options)
  });

  echarts.init(document.querySelector("#rentPaymentChart")).setOption({
    tooltip: {
      trigger: 'item'
    },
    legend: {
      top: '5%',
      left: 'center'
    },
    series: [{
      name: 'Rent Payment Status',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '18',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: rentPaymentStatusData
    }]
  });
});
</script>
            </div>
          </div><!-- End Website Traffic -->

            <script>
document.addEventListener("DOMContentLoaded", () => {
  const paymentStatusData = <?php echo $chartDataJson; ?>;

  echarts.init(document.querySelector("#trafficChart")).setOption({
    tooltip: {
      trigger: 'item'
    },
    legend: {
      top: '5%',
      left: 'center'
    },
    series: [{
      name: 'Payment Status',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '18',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: paymentStatusData
    }]
  });
  
});
</script>


        </div><!-- End Right side columns -->

      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>S-Space</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Designed by <a href="https://www.facebook.com/monay.maykagat">Rhussel Combo</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
</body>

</html>