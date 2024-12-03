<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
 session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
 header('Location: ../index.html');
 exit();
}
// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$period = 'today'; // Default value
// Function to get water bill amounts by status
function getWaterBillAmounts($conn, $status) {
    $sql = "SELECT MONTH(PaymentDate) as month, SUM(PaymentAmount) as total FROM payments WHERE PaymentStatus = ? GROUP BY MONTH(PaymentDate)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array_fill(0, 12, 0); // Initialize array for 12 months

    while ($row = $result->fetch_assoc()) {
        $data[$row['month'] - 1] = $row['total']; // Subtract 1 to align with zero-indexed array
    }

    return $data;
}

// Fetch data for each status
$pendingWaterAmounts = getWaterBillAmounts($conn, 'Pending');
$paidWaterAmounts = getWaterBillAmounts($conn, 'Paid');
$disputedWaterAmounts = getWaterBillAmounts($conn, 'Unpaid');

// Close connection
$conn->close();

function countRentPaymentsByStatusAndMonth($rentPaymentsData, $status) {
  $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $amounts = array_fill_keys($months, 0); // Initialize amounts for each month

  foreach ($rentPaymentsData as $rentPayment) {
      if ($rentPayment['PaymentStatus'] === $status && isset($rentPayment['PaymentAmount'])) {
          $month = date('M', strtotime($rentPayment['PaymentDate']));
          $amounts[$month] += $rentPayment['PaymentAmount'];
      }
  }

  return array_values($amounts); // Return amounts in the order of the months
  
}

// Usage:
$pendingRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Pending');
$paidRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Paid');
$disputedRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Unpaid');

// Usage:
$pendingRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Pending');
$paidRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Paid');
$disputedRentAmounts = countRentPaymentsByStatusAndMonth($_SESSION['rentPaymentsData'], 'Unpaid');
$paymentsData = array_filter($_SESSION['paymentsData'], function($payment) {
  return !empty($payment['PaymentID']);
});

$jsPaymentsData = json_encode($paymentsData);
// Database connection code
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get payment counts by status and period
function getPaymentCounts($conn, $status, $period) {
    $today = new DateTime();
    $startOfToday = $today->format('Y-m-d');
    $startOfMonth = $today->modify('first day of this month')->format('Y-m-d');
    $startOfYear = $today->modify('first day of january')->format('Y-m-d');

    $sql = "SELECT COUNT(*) AS count FROM payments WHERE PaymentStatus = ?";

    switch ($period) {
        case 'today':
            $sql .= " AND PaymentDate = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $status, $startOfToday);
            break;
        case 'month':
            $sql .= " AND PaymentDate >= ? AND PaymentDate < DATE_ADD(?, INTERVAL 1 MONTH)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $status, $startOfMonth, $startOfMonth);
            break;
        case 'year':
            $sql .= " AND PaymentDate >= ? AND PaymentDate < DATE_ADD(?, INTERVAL 1 YEAR)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $status, $startOfYear, $startOfYear);
            break;
        default:
            return 0;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['count'];
}

// Function to get total revenue by period
function getTotalRevenue($conn, $period) {
    $today = new DateTime();
    $startOfToday = $today->format('Y-m-d');
    $startOfMonth = $today->modify('first day of this month')->format('Y-m-d');
    $startOfYear = $today->modify('first day of january')->format('Y-m-d');

    $sql = "SELECT SUM(PaymentAmount) AS total_revenue FROM payments";

    switch ($period) {
        case 'today':
            $sql .= " WHERE PaymentDate = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $startOfToday);
            break;
        case 'month':
            $sql .= " WHERE PaymentDate >= ? AND PaymentDate < DATE_ADD(?, INTERVAL 1 MONTH)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $startOfMonth, $startOfMonth);
            break;
        case 'year':
            $sql .= " WHERE PaymentDate >= ? AND PaymentDate < DATE_ADD(?, INTERVAL 1 YEAR)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $startOfYear, $startOfYear);
            break;
        default:
            return 0;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total_revenue'];
}

// Get payment counts for today
$paidPaymentCount = getPaymentCounts($conn, 'Paid', 'today');
$pendingPaymentCount = getPaymentCounts($conn, 'Pending', 'today');
$totalRevenue = getTotalRevenue($conn, 'today');

// Close the database connection
// 1. Water Bill Payment Status Distribution
$sql = "SELECT PaymentStatus, COUNT(*) AS count FROM payments GROUP BY PaymentStatus";
$result = $conn->query($sql);
$paymentStatusData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentStatusData[] = $row;
    }
}

// 2. Monthly Water Bill Payments Trend
$sql = "SELECT MONTH(PaymentDate) AS month, PaymentStatus, SUM(PaymentAmount) AS total 
        FROM payments
        GROUP BY MONTH(PaymentDate), PaymentStatus
        ORDER BY MONTH(PaymentDate)";
$result = $conn->query($sql);
$pendingWaterAmounts = array_fill(0, 12, 0);
$paidWaterAmounts = array_fill(0, 12, 0);
$disputedWaterAmounts = array_fill(0, 12, 0);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $month = $row['month'] - 1; // Month index starts from 0
        if ($row['PaymentStatus'] === 'Pending') {
            $pendingWaterAmounts[$month] = $row['total'];
        } elseif ($row['PaymentStatus'] === 'Paid') {
            $paidWaterAmounts[$month] = $row['total'];
        } elseif ($row['PaymentStatus'] === 'Disputed') {
            $disputedWaterAmounts[$month] = $row['total'];
        }
    }
}

// 3. Payment Method Distribution for Water Bills
$sql = "SELECT PaymentMethod, COUNT(*) AS count FROM payments GROUP BY PaymentMethod";
$result = $conn->query($sql);
$paymentMethodData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentMethodData[] = $row;
    }
}

// 4. Water Bill Payments by Room/Unit
$sql = "SELECT t.room_id, SUM(p.PaymentAmount) AS totalAmount
        FROM payments p
        INNER JOIN tenants t ON p.TenantID = t.TenantID
        GROUP BY t.room_id";
$result = $conn->query($sql);
$rentByRoomData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rentByRoomData[] = $row;
    }
}

// 5. Overdue Water Bill Payments
$oneWeekAgo = date('Y-m-d', strtotime('-1 week'));
$sql = "SELECT wb.BillDate, wb.WaterBillAmount
        FROM water_bills wb
        LEFT JOIN payments p ON p.WaterBillID = wb.WaterBillID
        WHERE wb.BillDate < '$oneWeekAgo' AND p.PaymentID IS NULL";
$result = $conn->query($sql);
$overduePaymentsData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $overduePaymentsData[] = array(
            'billDate' => $row['BillDate'],
            'amount' => $row['WaterBillAmount']
        );
    }
}
$conn -> close();
  include 'header.inc.php';
  
  ?>


  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Records</title>
    <!-- Bootstrap CSS -->
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media only screen and (max-width: 768px) {
  .datatable {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
  .datatable th,
  .datatable td {
    white-space: normal;
  }
  }
        .btn-view-receipt {
            background-color: #D54D5D;
            color: white;
            font-family: "Nunito", sans-serif;
        }
        .btn-view-receipt:hover {
            background-color: #C04850;
        }
        .status-btn {
            cursor: pointer;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            text-align: center;
        }
        .status-paid {
            background-color: #90ff90;
            color: #008767;
        }
        .status-unpaid {
            background-color: #dc3545;
        }
        .status-btn:hover {
            opacity: 0.8;
        }
        .dropdown-toggle::after {
        display: inline-block;
        margin-left: .255em;
        vertical-align: .255em;
        content: "";
        border-top: .3em solid;
        border-right: .3em solid transparent;
        border-bottom: 0;
        border-left: .3em solid transparent;
    }
    .status-dropdown {
        cursor: pointer;
        position: relative;
        display: inline-block;
    }
    .status-dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }
    .status-dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }
    .status-dropdown-content a:hover {background-color: #f1f1f1}
    .status-dropdown:hover .status-dropdown-content {display: block;}
    .status-dropdown:hover .dropdown-toggle {background-color: #3e8e41;}
    

        .text-wrapper-18 {
            font-weight: 500;
            color: #000000;
            font-size: 12px;
            letter-spacing: -0.12px;
            line-height: 12px;
            font-family: "Poppins", Helvetica;
            white-space: nowrap;
            margin: 0 10px;
        }

        /* Responsive styles */
        @media only screen and (max-width: 768px) {
            .product {
                width: 100%;
                height: auto;
                top: 0;
            }

            .overlap {
                width: 100%;
                height: auto;
                top: 0;
            }

            .rectangle {
                width: 100%;
                height: auto;
                left: 0;
                box-shadow: none;
            }

            .text-wrapper {
                top: 20px;
                left: 20px;
            }

            .div {
                top: 50px;
                left: 20px;
            }

            .navbar {
                width: 100%;
                top: 80px;
                left: 20px;
                overflow-x: auto;
                white-space: nowrap;
            }
            .navbar span {
                margin:10px
            }
            .overlap-group-wrapper {
                top: 20px;
                left: 20px;
            }

            .group-9 {
                width: 100%;
                top: auto;
                left: 0;
                padding: 20px;
                box-sizing: border-box;
            }

            .pagination-btn {
                margin: 0 2px;
                padding: 4px 6px;
            }

            .text-wrapper-18 {
                margin: 0 5px;
            }}
            .dashboard-stats {
  display: flex;
  justify-content: space-between;
  width: 100%;
  }
  .breadcrumb{
    margin-bottom : 0px;
  }
  .card-body{
    padding-right:0px;
    width : 100%;
    
  }

  * {
  box-sizing: border-box;
  }

  /* Adjust the width of the columns */
  .col-xxl-4, .col-xl-12 {
  flex: 0 0 auto; /* Prevents the columns from shrinking smaller than their content */
  max-width: calc(33.3333% - 0px); /* Increase the percentage or decrease the subtracted value */
  }

  /* Adjust the card styles */
  .card {
  margin: 0px; /* Decrease the margin to give more space to the card */
  padding: 15px; /* Adjust the padding as needed */
  /* Other styles */
  }


  /* Responsive adjustments */
  @media only screen and (max-width: 1200px) {
  .col-xxl-4, .col-xl-12 {
    max-width: calc(50% - 10px); /* Adjust for medium screens */
  }
  }

  @media only screen and (max-width: 768px) {
  .col-xxl-4, .col-xl-12 {
    max-width: calc(100% - 20px); /* Adjust for small screens */
  }
  }
  .dropdown-item:hover {
  background-color: #f8f9fa;
  color: #007bff;
  }

  /* Style for the search input */
  #searchInput {
  padding-right: 30px; /* Make room for the magnifying glass icon */
  width:40%;
  }

  /* Style for the input group text */
  .input-group-text {
  background: transparent;
  border: none;
  }

  /* Style for the Bootstrap icons */
  .bi-search {
  font-size: 1rem;
  }
        .table th, .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: none;
            font-family: "Poppins", Helvetica;
            font-weight: 500;
            color: #292d32;
            font-size: 14px;
            letter-spacing: -0.14px;
        }

        .table thead th {
            border-bottom: 1px solid #dee2e6;
            color: #b5b7c0;
        }
        /* Custom styles for the search input */
  .input-group-text.bg-transparent {
  background-color: transparent;
  }

  .input-group .border-right-0 {
  border-right: 0;
  }

  .input-group .border-left-0 {
  border-left: 0;
  }

  /* Custom styles for the sort button */
  .btn-group .dropdown-toggle {
  background-color: rgba(255, 0, 0, 0.1); /* Light red transparent background */
  border: 1px solid rgba(255, 0, 0, 0.2); /* Light red border */
  }

  .btn-group .dropdown-toggle:hover {
  background-color: rgba(255, 0, 0,0.2); /* Darker red on hover */
  }
  .status-pending {
      background-color: #F8FB91; /* Light yellow background */
      color: #9A9400; /* Black text color */
  }
  .status-disputed {
      background-color: #FFC5C5; /* Light yellow background */
      color: #DF0404; /* Black text color */
  }
  .filter-container {
  display: flex;
  align-items: center;
  border-radius: 10px;
  border: 1px solid rgba(213, 213, 213, 1);
  background-color: #f9f9fb;
  padding: 0 34px;
  gap: 20px;
  font-family: 'Nunito Sans', sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #202224;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 20px;
}

.filter-icon {
  width: 45px;
  aspect-ratio: 0.64;
  object-fit: auto;
  object-position: center;
}

.separator {
  width: 1px;
  height: 100%;
  stroke-width: 0.3px;
  stroke: #979797;
  border: 0px solid rgba(151, 151, 151, 1);
}

.dropdown-icon {
  width: 24px;
  aspect-ratio: 0.96;
  object-fit: auto;
  object-position: center;
  margin: auto 0;
}

.reset-filter {
  display: flex;
  gap: 8px;
  color: #ea0234;
  font-weight: 600;
  margin: auto 0;
}

.reset-icon {
  width: 18px;
  aspect-ratio: 1;
  object-fit: auto;
  object-position: center;
}

@media (max-width: 991px) {
  .filter-container {
    flex-wrap: wrap;
    padding: 0 20px;
  }
}
.btn-view-receipt {
    background-color: #D54D5D;
    color: white;
    font-family: "Nunito", sans-serif;
}

.btn-view-receipt:hover {
    background-color: #C04850;
}
  </style>
  </head>
  <body>
  <main id="main" class="main">
  <div class="pagetitle">
    <h1>Hello Admin👋,</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Payment Records</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->
  <section class="section dashboard">
    <div class="row">
        <!-- Paid Payments Card -->
        <div class="col-xxl-4 col-md-6">
    <div class="card info-card">
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('today');">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('month');">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('year');">This Year</a></li>
            </ul>
        </div>
        <div class="card-body">
            
                    <h5 class="card-title">Paid Payments <span>| <?php echo ucfirst($period); ?></span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6 id="paid-payments-count"><?php echo $paidPaymentCount; ?></h6>
                            <span class="text-success small pt-1 fw-bold">Paid</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Paid Payments Card -->
  <!-- Pending Payments Card -->
  <div class="col-xxl-4 col-md-6">
    <div class="card info-card">
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('today');">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('month');">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('year');">This Year</a></li>
            </ul>
        </div>
        <div class="card-body">
                    <h5 class="card-title">Pending Payments <span>| <?php echo ucfirst($period); ?></span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6 id="unpaid-payments-count"><?php echo $pendingPaymentCount; ?></h6>
                            <span class="text-danger small pt-1 fw-bold">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Pending Payments Card -->
<!-- Total Revenue Card -->
<div class="col-xxl-4 col-md-6">
    <div class="card info-card">
        <div class="filter">
            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                    <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('today');">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('month');">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterPayments('year');">This Year</a></li>
            </ul>
        </div>
        <div class="card-body">
                    <h5 class="card-title">Total Revenue <span>| <?php echo ucfirst($period); ?></span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ps-3">
                            <h6 id="total-revenue">₱<?php echo number_format($totalRevenue, 2); ?></h6>
                            <span class="text-primary small pt-1 fw-bold">Revenue</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Total Revenue Card -->

  <div class="col-lg-10">
      <div class="card">
          <div class="card-body">
              <h5 class="card-title">Water Earnings Chart</h5>
              <!-- Water Earnings Chart -->
              <div id="waterBillChart"></div>
              <script>
    document.addEventListener("DOMContentLoaded", () => {
      const pendingWaterData = <?php echo json_encode($pendingWaterAmounts); ?>;
    const paidWaterData = <?php echo json_encode($paidWaterAmounts); ?>;
    const disputedWaterData = <?php echo json_encode($disputedWaterAmounts); ?>;

        new ApexCharts(document.querySelector("#waterBillChart"), {
            series: [{
                name: 'Pending',
                data: pendingWaterData
            }, {
                name: 'Paid',
                data: paidWaterData
            }, {
                name: 'Disputed',
                data: disputedWaterData
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            yaxis: {
                title: {
                    text: 'Amount (in thousands)'
                },
                labels: {
                    formatter: function(value) {
                        return value / 1000 + 'k';
                    }
                }
            },
            fill: {
                opacity: 1,
                colors: ['rgb(254, 176, 25)', 'rgb(0, 227, 150)', 'rgb(255, 69, 96)'] // Yellow, green, red
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "$" + val.toFixed(2);
                    }
                }
            }
        }).render();
    });
</script>
              <!-- End Payment Status Chart -->
          </div>
      </div>
  </div>
  <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Status Distribution</h5>
                        <div id="paymentStatusChart" style="min-height: 400px;" class="echart"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const paymentStatusData = <?php echo json_encode($paymentStatusData); ?>;
                                echarts.init(document.querySelector("#paymentStatusChart")).setOption({
                                    title: {
                                        text: 'Payment Status Distribution',
                                        left: 'center'
                                    },
                                    tooltip: {
                                        trigger: 'item'
                                    },
                                    legend: {
                                        orient: 'vertical',
                                        left: 'left'
                                    },
                                    series: [{
                                        name: 'Payment Status',
                                        type: 'pie',
                                        radius: '50%',
                                        data: paymentStatusData.map(data => ({ value: data.count, name: data.PaymentStatus })),
                                        emphasis: {
                                            itemStyle: {
                                                shadowBlur: 10,
                                                shadowOffsetX: 0,
                                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                                            }
                                        }
                                    }]
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Water Bill Payments Trend</h5>
                        <div id="monthlyTrendChart"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const pendingWaterData = <?php echo json_encode(array_values($pendingWaterAmounts)); ?>;
                                const paidWaterData = <?php echo json_encode(array_values($paidWaterAmounts)); ?>;
                                const disputedWaterData = <?php echo json_encode(array_values($disputedWaterAmounts)); ?>;

                                new ApexCharts(document.querySelector("#monthlyTrendChart"), {
                                    series: [{
                                        name: 'Pending',
                                        data: pendingWaterData
                                    }, {
                                        name: 'Paid',
                                        data: paidWaterData
                                    }, {
                                        name: 'Disputed',
                                        data: disputedWaterData
                                    }],
                                    chart: {
                                        type: 'line',
                                        height: 350
                                    },
                                    xaxis: {
                                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Amount (in thousands)'
                                        },
                                        labels: {
                                            formatter: function(value) {
                                                return value / 1000 + 'k';
                                            }
                                        }
                                    },
                                    stroke: {
                                        curve: 'smooth'
                                    },
                                    markers: {
                                        size: 4
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return "$" + val.toFixed(2);
                                            }
                                        }
                                    }
                                }).render();
                            });
                        </script>
                    </div>
                </div>
                </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Method Distribution</h5>
                        <div id="paymentMethodChart"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const paymentMethodData = <?php echo json_encode($paymentMethodData); ?>;
                                new ApexCharts(document.querySelector("#paymentMethodChart"), {
                                    series: [{
                                        data: paymentMethodData.map(data => data.count)
                                    }],
                                    chart: {
                                        type: 'bar',
                                        height: 350
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: true,
                                        },
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    xaxis: {
                                        categories: paymentMethodData.map(data => data.PaymentMethod),
                                    }
                                }).render();
                            });
                        </script>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Water Bill Payments by Room</h5>
                        <div id="rentByRoomChart" style="min-height: 400px;" class="echart"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const rentByRoomData = <?php echo json_encode($rentByRoomData); ?>;
                                echarts.init(document.querySelector("#rentByRoomChart")).setOption({
                                    title: {
                                        text: 'Room No.',
                                        left: 'center'
                                    },
                                    xAxis: {
                                        type: 'category',
                                        data: rentByRoomData.map(data => data.room_id)
                                    },
                                    yAxis: {
                                        type: 'value'
                                    },
                                    series: [{
                                        type: 'bar',
                                        data: rentByRoomData.map(data => data.totalAmount)
                                    }]
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Overdue Water Bill Payments</h5>
                        <div id="overduePaymentsChart" style="min-height: 400px;" class="echart"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const overduePaymentsData = <?php echo json_encode($overduePaymentsData); ?>;
                                echarts.init(document.querySelector("#overduePaymentsChart")).setOption({
                                    title: {
                                        text: 'Overdue Water Bill Payments',
                                        left: 'center'
                                    },
                                    xAxis: {
                                        type: 'category',
                                        data: overduePaymentsData.map(data => data.billDate)
                                    },
                                    yAxis: {
                                        type: 'value',
                                        name: 'Amount'
                                    },
                                    series: [{
                                        type: 'bar',
                                        data: overduePaymentsData.map(data => data.amount)
                                    }]
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
                        </section>
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <!-- Container for the title and controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title">Water Payment Records</h5>
    <div>
      <!-- Search input with magnifying glass icon on the left -->
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-transparent border-right-0">
            <i class="bi bi-search"></i>
          </span>
        </div>
        <input type="text" id="searchInput" class="form-control border-left-0" placeholder="Search..." style="width: 200px;">
      </div><!-- Dropdown for sorting with the current selection displayed -->
  <div class="btn-group ml-2">
    <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: rgba(255, 0, 0, 0.1); border: 1px solid rgba(255, 0, 0, 0.2);" id="sortButton">
      Sort by: Newest
    </button>
    <div class="dropdown-menu dropdown-menu-right">
    <a class="dropdown-item" href="#" onclick="updateSort('Newest')">Newest</a>
  <a class="dropdown-item" href="#" onclick="updateSort('Oldest')">Oldest</a>
  </div>
      </div>
    </div>
  </div>
  
  <!-- Table with stripped rows -->
  <h6 class="card-title">Unpaid and Pending Payments</h6>
  <table id="unpaidTable" class="table datatable table-borderless">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Tenant Name</th>
                                <th>Payment Date</th>
                                <th>Payment Amount</th>
                                <th>Payment Method</th>
                                <th>Reference Number</th>
                                <th>Receipt</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['paymentsData'] as $index => $payment): ?>
                                <?php if (!empty($payment['PaymentID']) && ($payment['PaymentStatus'] === 'Pending' || $payment['PaymentStatus'] === 'Disputed')): ?>
                                    <?php
                                    $tenantData = array_filter($_SESSION['tenantsData'], function ($tenant) use ($payment) {
                                        return $tenant['TenantID'] === $payment['TenantID'];
                                    });
                                    $tenantInfo = reset($tenantData);
                                    $roomId = $tenantInfo['room_id'] ?? 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($roomId) ?></td>
                                        <td><?= htmlspecialchars($tenantInfo['FirstName'] ?? 'N/A') . ' ' . htmlspecialchars($tenantInfo['LastName'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentDate'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentAmount'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentMethod'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['ReferenceNumber'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (!empty($payment['PaymentID'])): ?>
                                                <button class="btn btn-sm btn-view-receipt" data-bs-toggle="modal" data-bs-target="#receiptModal" data-payment-id="<?php echo $payment['PaymentID']; ?>">View Receipt</button>
                                            <?php else: ?>
                                                No receipt available
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="status-dropdown">
                                                <button id="status-btn-<?= $payment['PaymentID'] ?>" class="status-btn dropdown-toggle status-<?= strtolower($payment['PaymentStatus']) ?>" onclick="showDropdown('<?= $payment['PaymentID'] ?>')">
                                                    <?= htmlspecialchars($payment['PaymentStatus']) ?> <i class="bi bi-caret-down-fill"></i>
                                                </button>
                                                <div id="dropdown-<?= $payment['PaymentID'] ?>" class="status-dropdown-content">
                                                    <a href="#" onclick="changeStatus('<?= $payment['PaymentID'] ?>', 'Paid')">Paid</a>
                                                    <a href="#" onclick="changeStatus('<?= $payment['PaymentID'] ?>', 'Pending')">Pending</a>
                                                    <a href="#" onclick="changeStatus('<?= $payment['PaymentID'] ?>', 'Disputed')">Disputed</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
<!-- Table for paid payments -->
<h6 class="card-title">Paid Payments</h6>
                    <table id="paidTable" class="table datatable table-borderless">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Tenant Name</th>
                                <th>Payment Date</th>
                                <th>Payment Amount</th>
                                <th>Payment Method</th>
                                <th>Reference Number</th>
                                <th>Receipt</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['paymentsData'] as $index => $payment): ?>
                                <?php if (!empty($payment['PaymentID']) && $payment['PaymentStatus'] === 'Paid'): ?>
                                    <?php
                                    $tenantData = array_filter($_SESSION['tenantsData'], function ($tenant) use ($payment) {
                                        return $tenant['TenantID'] === $payment['TenantID'];
                                    });
                                    $tenantInfo = reset($tenantData);
                                    $roomId = $tenantInfo['room_id'] ?? 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($roomId) ?></td>
                                        <td><?= htmlspecialchars($tenantInfo['FirstName'] ?? 'N/A') . ' ' . htmlspecialchars($tenantInfo['LastName'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentDate'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentAmount'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['PaymentMethod'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($payment['ReferenceNumber'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (!empty($payment['PaymentID'])): ?>
                                                <button class="btn btn-sm btn-view-receipt" data-bs-toggle="modal" data-bs-target="#receiptModal" data-payment-id="<?php echo $payment['PaymentID']; ?>">View Receipt</button>
                                            <?php else: ?>
                                                No receipt available
                                            <?php endif; ?>
                                        </td>
                                        <td>Paid</td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
  </main><!-- End #main -->
 
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to change the payment status?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChangeBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="receiptImage" src="" alt="Receipt Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

  <?php
  // Move the PHP code block outside of the <script> tag
  function ($tenant) use ($payment) {
      return $tenant['TenantID'] === $payment['TenantID'];
  }
  ?>
  <script>
      document.getElementById('searchInput').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var tableRows = document.getElementById('dataTable').getElementsByTagName('tr');

    for (var i = 1; i < tableRows.length; i++) {
      var cells = tableRows[i].getElementsByTagName('td');
      var rowText = '';
      for (var j = 0; j < cells.length; j++) {
        rowText += cells[j].textContent.toLowerCase() + ' ';
      }
      if (rowText.indexOf(searchValue) === -1) {
        tableRows[i].style.display = 'none';
      } else {
        tableRows[i].style.display = '';
      }
    }
  });

          function sortTable(order) {
          var rows, switching, i, x, y, shouldSwitch;
          var table = document.getElementById("dataTable");
          var tableBody = table.getElementsByTagName("tbody")[0];
          var newTableBody = document.createElement("tbody");
          // Convert the rows to an array for easier sorting
          rows = Array.from(tableBody.rows);
          // Use index 3 for 'Payment Date' column
          var dateColumnIndex = 3;
          // Sort the rows based on the 'Payment Date' column
          rows.sort(function(a, b) {
          x = new Date(a.getElementsByTagName("TD")[dateColumnIndex].textContent);
          y = new Date(b.getElementsByTagName("TD")[dateColumnIndex].textContent);
          if (order === 'newest') {
          return y - x;
          } else {
          return x - y;
          }
          });
  // Append the sorted rows to the new table body
  rows.forEach(function(row) {
  newTableBody.appendChild(row);
  });
  // Replace the old table body with the new sorted one
  table.replaceChild(newTableBody, tableBody);
  }
  function updateSort(sortType) {
  document.getElementById('sortButton').textContent = 'Sort by: ' + sortType;
  sortTable(sortType.toLowerCase());
  }
      document.getElementById('searchInput').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var tableRows = document.getElementById('dataTable').getElementsByTagName('tr');
  for (var i = 1; i < tableRows.length; i++) {
  var cells = tableRows[i].getElementsByTagName('td');
  var rowText = '';
  for (var j = 0; j < cells.length; j++) {
  rowText += cells[j].textContent.toLowerCase() + ' ';
  }
  if (rowText.indexOf(searchValue) === -1) {
  tableRows[i].style.display = 'none';
  } else {
  tableRows[i].style.display = '';
  }
  }
  });
  </script>

<script>
        // Function to change the payment status
        function changeStatus(paymentId, newStatus) {
            // Show the confirmation modal
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            confirmationModal.show();

            // Handle the confirmation
            const confirmStatusChangeBtn = document.getElementById('confirmStatusChangeBtn');
            confirmStatusChangeBtn.addEventListener('click', () => {
                // Send an AJAX request to the server to update the status
                $.ajax({
                    url: 'update_payment_status.php',
                    type: 'POST',
                    data: {
                        'payment_id': paymentId,
                        'new_status': newStatus
                    },
                    success: function(response) {
                        // Update the button text and class based on the new status
                        const statusButton = document.querySelector(`#status-btn-${paymentId}`);
                        statusButton.textContent = newStatus;
                        statusButton.className = `status-btn dropdown-toggle status-${newStatus.toLowerCase()}`;

                        // Close the dropdown if it's open
                        const dropdown = document.getElementById(`dropdown-${paymentId}`);
                        if (dropdown.classList.contains('show')) {
    dropdown.classList.remove('show');
}

// Update the table row with the new status
const tableRow = statusButton.closest('tr');
const statusCell = tableRow.querySelector('td:last-child');
statusCell.innerHTML = `
    <div class="status-dropdown">
        <button id="status-btn-${paymentId}" class="status-btn dropdown-toggle status-${newStatus.toLowerCase()}" onclick="showDropdown('${paymentId}')">
            ${newStatus} <i class="bi bi-caret-down-fill"></i>
        </button>
        <div id="dropdown-${paymentId}" class="status-dropdown-content">
            <a href="#" onclick="changeStatus('${paymentId}', 'Paid')">Paid</a>
            <a href="#" onclick="changeStatus('${paymentId}', 'Pending')">Pending</a>
            <a href="#" onclick="changeStatus('${paymentId}', 'Disputed')">Disputed</a>
        </div>
    </div>
`;

// If the new status is 'Paid', move the table row to the paid table
if (newStatus === 'Paid') {
    const paidTable = document.getElementById('paidTable').getElementsByTagName('tbody')[0];
    paidTable.appendChild(tableRow);
    const paidStatusCell = tableRow.querySelector('td:last-child');
    paidStatusCell.innerHTML = 'Paid'; // Set the status cell to 'Paid' without a dropdown
}

showSuccessNotification('Payment status updated successfully!');
confirmationModal.hide(); // Hide the confirmation modal
},
error: function(xhr, status, error) {
    // Handle errors here
    console.error('Status update failed:', error);
}
});
});
}
            
  // Function to show the dropdown content
  function showDropdown(paymentId) {
      document.getElementById(`dropdown-${paymentId}`).classList.toggle("show");
  }

  // Close the dropdown if the user clicks outside of it
  window.onclick = function(event) {
          if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.status-dropdown-content')) {
            if (!event.target.matches('.dropdown-toggle')) {
                var dropdowns = document.getElementsByClassName("status-dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }}
  </script>

<script>
    const paymentsData = <?php echo $jsPaymentsData; ?>;

    function filterPayments(type, period) {
        console.log('Filtering payments for type:', type, 'and period:', period);
        const today = new Date();
        const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const startOfYear = new Date(today.getFullYear(), 0, 1);

        let filteredData;
        switch (period) {
            case 'today':
                filteredData = paymentsData.filter(payment => {
                    const paymentDate = new Date(payment.PaymentDate);
                    return paymentDate >= startOfToday && paymentDate < new Date(startOfToday.getTime() + 24 * 60 * 60 * 1000);
                });
                break;
            case 'month':
                filteredData = paymentsData.filter(payment => {
                    const paymentDate = new Date(payment.PaymentDate);
                    return paymentDate >= startOfMonth && paymentDate < new Date(startOfMonth.getFullYear(), startOfMonth.getMonth() + 1, 1);
                });
                break;
            case 'year':
                filteredData = paymentsData.filter(payment => {
                    const paymentDate = new Date(payment.PaymentDate);
                    return paymentDate >= startOfYear && paymentDate < new Date(startOfYear.getFullYear() + 1, 0, 1);
                });
                break;
        }

       // Calculate total revenue
    const totalEarnings = paymentsData.reduce((acc, payment) => acc + parseFloat(payment.PaymentAmount), 0);

// Count paid and pending payments
const paidPaymentsCount = paymentsData.filter(payment => payment.PaymentStatus === 'Paid').length;
const pendingPaymentsCount = paymentsData.filter(payment => payment.PaymentStatus === 'Pending').length;

// Update the total revenue display
document.getElementById('total-revenue').textContent = '₱' + totalEarnings.toFixed(2);

// Update the paid and pending payment counts
document.getElementById('paid-payments-count').textContent = paidPaymentsCount;
document.getElementById('unpaid-payments-count').textContent = pendingPaymentsCount;

// Update the span text for the card title
const cardTitleSpan = document.querySelector('.col-xxl-4 .card-title span');
cardTitleSpan.textContent = `| ${period.charAt(0).toUpperCase() + period.slice(1)}`;
    }

  function showSuccessNotification(message) {
      const notification = document.createElement('div');
      notification.classList.add('alert', 'alert-success', 'position-fixed', 'top-50', 'start-50', 'translate-middle-x', 'mt-3');
      notification.textContent = message;
      document.body.appendChild(notification);

      setTimeout(() => {
          notification.classList.add('fade');
          setTimeout(() => {
              notification.remove();
          }, 300);
      }, 3000);
  }

  
  </script>
  <script>
     function filterData(period) {
            window.location.href = '?period=' + period;
        }
  </script>
<script>
    const receiptModal = document.getElementById('receiptModal');
    const receiptImage = document.getElementById('receiptImage');

    receiptModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const paymentId = button.getAttribute('data-payment-id');
        receiptImage.src = `get_receipt_image.php?payment_id=${paymentId}`;
    });
</script>

  <!-- Bootstrap JS and its dependencies (jQuery and Popper.js) -->
  <!-- Bootstrap JS -->

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  </body>
</html>
