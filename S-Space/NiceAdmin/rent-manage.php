<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../index.html');
  exit();
}
include 'header.inc.php';

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve rent payments data with tenants and rooms information
$sql = "SELECT rp.RentPaymentID, rp.TenantID, rp.PaymentDate, rp.PaymentAmount, rp.PaymentMethod, rp.ReferenceNumber, rp.ReceiptImageURL, rp.PaymentStatus, t.FirstName, t.LastName, t.ContactNumber, t.Email, r.room_id, r.price
        FROM rent_payments rp
        JOIN tenants t ON rp.TenantID = t.TenantID
        JOIN rooms r ON t.room_id = r.room_id";
$result = $conn->query($sql);

$rentPaymentsData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rentPaymentsData[] = $row;
    }
}
$pendingRentAmounts = array_fill(0, 12, 0);
$paidRentAmounts = array_fill(0, 12, 0);
$disputedRentAmounts = array_fill(0, 12, 0);

// Fetch and categorize rent payment amounts by month
$sql = "SELECT MONTH(PaymentDate) as month, PaymentAmount, PaymentStatus 
        FROM rent_payments 
        WHERE YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $month = $row['month'] - 1; // Adjust month to be zero-indexed
        $amount = $row['PaymentAmount'];
        $status = $row['PaymentStatus'];
        
        if ($status == 'Pending') {
            $pendingRentAmounts[$month] += $amount;
        } elseif ($status == 'Paid') {
            $paidRentAmounts[$month] += $amount;
        } else {
            $disputedRentAmounts[$month] += $amount;
        }
    }
}
// Fetch rent payments by room
$sql = "SELECT r.room_id, SUM(rp.PaymentAmount) as totalAmount 
        FROM rent_payments rp 
        JOIN tenants t ON rp.TenantID = t.TenantID 
        JOIN rooms r ON t.room_id = r.room_id 
        GROUP BY r.room_id";
$result = $conn->query($sql);
$rentByRoomData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rentByRoomData[] = $row;
    }
}
// Fetch overdue payments by month
$sql = "SELECT MONTH(PaymentDate) as month, COUNT(*) as count 
        FROM rent_payments 
        WHERE PaymentStatus = 'Unpaid' AND PaymentDate < DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
        GROUP BY MONTH(PaymentDate)";
$result = $conn->query($sql);

$overduePaymentsData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $overduePaymentsData[] = $row;
    }
}
// Retrieve payment status distribution
$sql = "SELECT PaymentStatus, COUNT(*) as count FROM rent_payments GROUP BY PaymentStatus";
$result = $conn->query($sql);

$paymentStatusData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentStatusData[] = $row;
    }
}

// Fetch count of pending rent payments for today
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Pending' AND PaymentDate = CURDATE()";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingRentTodayCount = $row['count'];

// Fetch count of paid rent payments for today
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Paid' AND PaymentDate = CURDATE()";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$paidRentTodayCount = $row['count'];

// Fetch count of unpaid rent payments for today
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus != 'Paid' AND PaymentDate = CURDATE()";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$unpaidRentTodayCount = $row['count'];

// Fetch count of pending rent payments for this month
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Pending' AND MONTH(PaymentDate) = MONTH(CURDATE()) AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingRentMonthCount = $row['count'];

// Fetch count of paid rent payments for this month
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Paid' AND MONTH(PaymentDate) = MONTH(CURDATE()) AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$paidRentMonthCount = $row['count'];

// Fetch count of unpaid rent payments for this month
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus != 'Paid' AND MONTH(PaymentDate) = MONTH(CURDATE()) AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$unpaidRentMonthCount = $row['count'];

// Fetch count of pending rent payments for this year
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Pending' AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pendingRentYearCount = $row['count'];

// Fetch count of paid rent payments for this year
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus = 'Paid' AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$paidRentYearCount = $row['count'];

// Fetch count of unpaid rent payments for this year
$sql = "SELECT COUNT(*) as count FROM rent_payments WHERE PaymentStatus != 'Paid' AND YEAR(PaymentDate) = YEAR(CURDATE())";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$unpaidRentYearCount = $row['count'];

// Fetch payment method distribution
$sql = "SELECT PaymentMethod, COUNT(*) as count FROM rent_payments GROUP BY PaymentMethod";
$result = $conn->query($sql);

$paymentMethodData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentMethodData[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Management</title>
    <!-- Bootstrap CSS -->
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
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 10px 20px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 12px;
  margin: 4px 2px;
  transition-duration: 0.4s;
  cursor: pointer;
}

.btn-view-receipt:hover {
  background-color: white;
  color: black;
  border: 0px solid #4CAF50;
}

        .status-btn {
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            text-align: center;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: white;
        }
        .status-overdue {
            background-color: #dc3545;
            color: white;
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
  border: 1px solid rgba(255, 0, 0, 0.2); /* Light
red border */
}
.btn-group .dropdown-toggle:hover {
  background-color: rgba(255, 0, 0, 0.2); /* Darker red on hover */
}

    </style>
</head>


<main id="main" class="main">
    <div class="pagetitle">
      <h1>Hello Admin👋,</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Rent Management</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">
        <!-- Pending Rent Card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('pending', 'today')">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('pending', 'month')">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('pending', 'year')">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Pending Rent <span>| Today</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="ps-3">
                  <h6 id="pending-rent-count"><?php echo $pendingRentTodayCount; ?></h6>
                  <span class="text-warning small pt-1 fw-bold">Pending</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Pending Rent Card -->

        <!-- Paid Rent Card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('paid', 'today')">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('paid', 'month')">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('paid', 'year')">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Paid Rent <span>| Today</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-check-circle"></i>
                </div>
                <div class="ps-3">
                  <h6 id="paid-rent-count"><?php echo $paidRentTodayCount; ?></h6>
                  <span class="text-success small pt-1 fw-bold">Paid</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Paid Rent Card -->

        <!-- Unpaid Rent Card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('unpaid', 'today')">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('unpaid', 'month')">This Month</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterRentPayments('unpaid', 'year')">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Unpaid Rent <span>| Today</span></h5>
              <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                  <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="ps-3">
                  <h6 id="unpaid-rent-count"><?php echo $unpaidRentTodayCount; ?></h6>
                  <span class="text-danger small pt-1 fw-bold">Unpaid</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Unpaid Rent Card -->
      </div>
    </section>
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
                    <h5 class="card-title">Monthly Rent Payments Trend</h5>
                    <div id="monthlyTrendChart"></div>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const pendingRentData = <?php echo json_encode(array_values($pendingRentAmounts)); ?>;
                            const paidRentData = <?php echo json_encode(array_values($paidRentAmounts)); ?>;
                            const disputedRentData = <?php echo json_encode(array_values($disputedRentAmounts)); ?>;

                            new ApexCharts(document.querySelector("#monthlyTrendChart"), {
                                series: [{
                                    name: 'Pending',
                                    data: pendingRentData
                                }, {
                                    name: 'Paid',
                                    data: paidRentData
                                }, {
                                    name: 'Disputed',
                                    data: disputedRentData
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
                    <h5 class="card-title">Rent Payments by Room</h5>
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
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Overdue Payments</h5>
                    <div id="overduePaymentsChart" style="min-height: 400px;" class="echart"></div>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const overduePaymentsData = <?php echo json_encode($overduePaymentsData); ?>;
                            echarts.init(document.querySelector("#overduePaymentsChart")).setOption({
                                title: {
                                    text: 'Monthly',
                                    left: 'center'
                                },
                                xAxis: {
                                    type: 'category',
                                    data: overduePaymentsData.map(data => 'Month ' + data.month)
                                },
                                yAxis: {
                                    type: 'value'
                                },
                                series: [{
                                    type: 'bar',
                                    data: overduePaymentsData.map(data => data.count)
                                }]
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="container">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Rent Payments Chart</h5>
                <!-- Rent Payments Chart -->
                <div id="rentPaymentsChart"></div>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const pendingRentData = <?php echo json_encode(array_values($pendingRentAmounts)); ?>;
                        const paidRentData = <?php echo json_encode(array_values($paidRentAmounts)); ?>;
                        const disputedRentData = <?php echo json_encode(array_values($disputedRentAmounts)); ?>;

                        new ApexCharts(document.querySelector("#rentPaymentsChart"), {
                            series: [{
                                name: 'Pending',
                                data: pendingRentData
                            }, {
                                name: 'Paid',
                                data: paidRentData
                            }, {
                                name: 'Disputed',
                                data: disputedRentData
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
            </div>
        </div>
    </div>
</div>   

    <section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <!-- Container for the title and controls -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title">Rent Payments</h5>

            <div>
              <!-- Search input with magnifying glass icon on the left -->
              <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-transparent border-right-0">
                    <i class="bi bi-search"></i>
                  </span>
                </div>
                <input type="text" id="searchInput" class="form-control border-left-0" placeholder="Search..." style="width: 200px;">
              </div>

              <!-- Dropdown for sorting with the current selection displayed -->
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
          <table id="dataTable" class="table datatable table-borderless">
            <thead>
              <tr>
                <th>Room ID</th>
                <th>Tenant Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Payment Date</th>
                <th>Payment Amount</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
                <th>Receipt</th>
                <th>Payment Status</th>
              </tr>
            </thead>
            <tbody>
  <?php foreach ($rentPaymentsData as $index => $payment): ?>
    <?php if ($payment['PaymentStatus'] !== 'Paid'): ?>
      <tr data-payment-id="<?= $payment['RentPaymentID'] ?>">
        <td><?= htmlspecialchars($payment['room_id'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($payment['FirstName'] ?? 'N/A') . ' ' . htmlspecialchars($payment['LastName'] ?? '') ?></td>
        <td><?= htmlspecialchars($payment['Email'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($payment['ContactNumber'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($payment['PaymentDate']) ?></td>
        <td><?= htmlspecialchars($payment['PaymentAmount']) ?></td>
        <td><?= htmlspecialchars($payment['PaymentMethod']) ?></td>
        <td><?= htmlspecialchars($payment['ReferenceNumber'] ?? 'N/A') ?></td>
        <td>
          <?php if ($payment['ReceiptImageURL']): ?>
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary btn-view-receipt" data-toggle="modal" data-target="#receiptModal<?= $payment['RentPaymentID'] ?>">
              View Receipt
            </button>

            <!-- Modal -->
            <div class="modal fade" id="receiptModal<?= $payment['RentPaymentID'] ?>" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel<?= $payment['RentPaymentID'] ?>" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel<?= $payment['RentPaymentID'] ?>">Receipt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                  <img src="view_receipt.php?payment_id=<?= $payment['RentPaymentID'] ?>" alt="Receipt Image" class="img-fluid">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            N/A
          <?php endif; ?>
        </td>
        <td>
          <div class="status-dropdown">
            <button id="status-btn-<?= $payment['RentPaymentID'] ?>" class="status-btn dropdown-toggle status-<?= strtolower($payment['PaymentStatus']) ?>" onclick="showDropdown('<?= $payment['RentPaymentID'] ?>')">
              <?= htmlspecialchars($payment['PaymentStatus']) ?>
            </button>
            <div id="dropdown-<?= $payment['RentPaymentID'] ?>" class="status-dropdown-content">
              <a href="#" onclick="changePaymentStatus('<?= $payment['RentPaymentID'] ?>', 'Pending')">Pending</a>
              <a href="#" onclick="changePaymentStatus('<?= $payment['RentPaymentID'] ?>', 'Paid')">Paid</a>
              <a href="#" onclick="changePaymentStatus('<?= $payment['RentPaymentID'] ?>', 'Disputed')">Disputed</a>
            </div>
          </div>
        </td>
      </tr>
    <?php endif; ?>
  <?php endforeach; ?>
</tbody>
          </table>
          <!-- End Table with stripped rows -->

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
          <h5 class="card-title">Paid Rent Payments</h5>
          <table id="paidTable" class="table datatable table-borderless">
            <thead>
              <tr>
                <th>Room ID</th>
                <th>Tenant Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Payment Date</th>
                <th>Payment Amount</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
                <th>Receipt</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rentPaymentsData as $payment): ?>
  <?php if ($payment['PaymentStatus'] === 'Paid'): ?>
    <tr>
      <td><?= htmlspecialchars($payment['room_id']) ?></td>
      <td><?= htmlspecialchars($payment['FirstName'] . ' ' . $payment['LastName']) ?></td>
      <td><?= htmlspecialchars($payment['Email']) ?></td>
      <td><?= htmlspecialchars($payment['ContactNumber']) ?></td>
      <td><?= htmlspecialchars($payment['PaymentDate']) ?></td>
      <td><?= htmlspecialchars($payment['PaymentAmount']) ?></td>
      <td><?= htmlspecialchars($payment['PaymentMethod']) ?></td>
      <td><?= htmlspecialchars($payment['ReferenceNumber'] ?? 'N/A') ?></td>
      <td>
        <?php if ($payment['ReceiptImageURL']): ?>
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary btn-view-receipt" data-toggle="modal" data-target="#receiptModal<?= $payment['RentPaymentID'] ?>">
            View Receipt
          </button>

          <!-- Modal -->
          <div class="modal fade" id="receiptModal<?= $payment['RentPaymentID'] ?>" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel<?= $payment['RentPaymentID'] ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="receiptModalLabel<?= $payment['RentPaymentID'] ?>">Receipt</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
                <div class="modal-body">
                <img src="view_receipt.php?payment_id=<?= $payment['RentPaymentID'] ?>" alt="Receipt Image" class="img-fluid">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          N/A
        <?php endif; ?>
      </td>
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
    <!-- Modal for confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Status Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="confirmationMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal for confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Status Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="confirmationMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
      </div>
    </div>
  </div>
</div>
  </main><!-- End #main -->
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





function showDropdown(paymentId) {
  document.getElementById('dropdown-' + paymentId).classList.toggle('show');
}
let currentPaymentId;
let currentNewStatus;

function changePaymentStatus(paymentId, newStatus) {
  // Store the current payment ID and new status for use in the confirmation modal
  currentPaymentId = paymentId;
  currentNewStatus = newStatus;

  // Set the confirmation message
  document.getElementById('confirmationMessage').innerText = 
    `Are you sure you want to change the payment status to ${newStatus}?`;

  // Show the confirmation modal
  $('#confirmationModal').modal('show');
}

// Handle the confirmation button click
document.getElementById('confirmButton').addEventListener('click', function() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_rent_status.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    if (this.status === 200) {
      try {
        var response = JSON.parse(this.responseText);
        if (response.status === 'success') {
          document.getElementById('status-btn-' + currentPaymentId).innerText = currentNewStatus;
          document.getElementById('status-btn-' + currentPaymentId).className = 'status-btn dropdown-toggle status-' + currentNewStatus.toLowerCase();

          // Optionally, if you need to move the row to a different table
          if (currentNewStatus.toLowerCase() === 'paid') {
            moveToPaymentTable(currentPaymentId);
          }
        } else {
          alert('Error updating status: ' + response.message);
        }
      } catch (e) {
        alert('Error updating status: ' + this.responseText);
      }
    } else {
      alert('Error updating status: ' + this.status);
    }
  };
  xhr.send('payment_id=' + currentPaymentId + '&new_status=' + currentNewStatus);

  // Hide the confirmation modal
  $('#confirmationModal').modal('hide');
});

// Existing click event listener for dropdowns
document.addEventListener('click', function(event) {
  if (!event.target.matches('.status-btn')) {
    var dropdowns = document.getElementsByClassName('status-dropdown-content');
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
});

function moveToPaymentTable(paymentId) {
  const row = document.querySelector(`#dataTable tr[data-payment-id="${paymentId}"]`);
  const paidTable = document.getElementById('paidTable').getElementsByTagName('tbody')[0];
  paidTable.appendChild(row);

  // Remove the status dropdown from the paid table
  const statusCell = row.querySelector('td:last-child');
  statusCell.innerHTML = '';
}
</script>

<script>
  function filterRentPayments(status, period) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_rent_counts.php?period=' + period, true);
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    var response = JSON.parse(this.responseText);
                    var pendingRentCount = response.pendingRentCount;
                    var paidRentCount = response.paidRentCount;
                    var unpaidRentCount = response.unpaidRentCount;

                    // Update card counts
                    document.getElementById('pending-rent-count').textContent = pendingRentCount;
                    document.getElementById('paid-rent-count').textContent = paidRentCount;
                    document.getElementById('unpaid-rent-count').textContent = unpaidRentCount;

                    // Update card titles
                    var pendingRentTitle = document.querySelector('.card-title', '.col-xxl-4.col-md-6:first-child');
                    var paidRentTitle = document.querySelector('.card-title', '.col-xxl-4.col-md-6:nth-child(2)');
                    var unpaidRentTitle = document.querySelector('.card-title', '.col-xxl-4.col-md-6:last-child');

                    pendingRentTitle.querySelector('span').textContent = '| ' + period.charAt(0).toUpperCase() + period.slice(1);
                    paidRentTitle.querySelector('span').textContent = '| ' + period.charAt(0).toUpperCase() + period.slice(1);
                    unpaidRentTitle.querySelector('span').textContent = '| ' + period.charAt(0).toUpperCase() + period.slice(1);
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            } else {
                console.error('Error fetching rent counts:', this.status);
            }
        };
        xhr.send();
    }
</script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<!-- Bootstrap JS and its dependencies (jQuery and Popper.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>