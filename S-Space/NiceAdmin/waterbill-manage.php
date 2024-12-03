<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../index.html');
  exit();
}
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

include 'header.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Service Requests</title>
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
        .btn-view-description {
            background-color: #D54D5D;
            color: white;
            font-family: "Nunito", sans-serif;
        }
        .btn-view-description:hover {
            background-color: #C04850;
        }
        .status-btn {
            cursor: pointer;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            text-align: center;
        }
        .status-completed {
            background-color: #28a745;
        }
        .status-pending {
            background-color: #ffc107;
        }
        .status-unassigned {
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
  background-color: rgba(255, 0, 0, 0.2); /* Darker red on hover */
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
                <li class="breadcrumb-item active">WaterBill Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Total Revenue from Water Bills Card -->
            <div class="col-xxl-4 col-md-6">
                <div class="card info-card">
                <div class="filter">
    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
            <h6>Filter</h6>
        </li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('today'); calculateAverageAmount('today'); countUnpaidBills('today');">Today</a></li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('month'); calculateAverageAmount('month'); countUnpaidBills('month');">This Month</a></li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('year'); calculateAverageAmount('year'); countUnpaidBills('year');">This Year</a></li>
    </ul>
</div>
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue <span>| Today</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="total-revenue">...</h6>
                                <span class="text-success small pt-1 fw-bold">Revenue</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Total Revenue from Water Bills Card -->

            <!-- Average Water Bill Amount Card -->
            <div class="col-xxl-4 col-md-6">
                <div class="card info-card">
                <div class="filter">
    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
            <h6>Filter</h6>
        </li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('today'); calculateAverageAmount('today'); countUnpaidBills('today');">Today</a></li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('month'); calculateAverageAmount('month'); countUnpaidBills('month');">This Month</a></li>
        <li><a class="dropdown-item" href="#" onclick="calculateTotalRevenue('year'); calculateAverageAmount('year'); countUnpaidBills('year');">This Year</a></li>
    </ul>
</div>
                    <div class="card-body">
                        <h5 class="card-title">Average Bill Amount <span>| Today</span></h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-calculator"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="average-bill-amount">...</h6>
                                <span class="text-info small pt-1 fw-bold">Average</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Average Water Bill Amount Card -->

            <!-- Unpaid Bills Card -->
            <div class="col-xxl-4 col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Unpaid Bills</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                            <div class="ps-3">
                                <h6 id="unpaid-bills-count">...</h6>
                                <span class="text-danger small pt-1 fw-bold">Unpaid</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Unpaid Bills Card -->

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Container for the title and controls -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Water Bills</h5>
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
                                <?php
var_dump($_SESSION['waterBillsData']);
                                ?>
                                <!-- Table with stripped rows -->
                                <table id="dataTable" class="table datatable table-borderless">
                                                                    <thead>
                                        <tr>
                                            <th>Water Bill ID</th>
                                            <th>Room ID</th>
                                            <th>Previous Reading</th>
                                            <th>Present Reading</th>
                                            <th>Consumption</th>
                                            <th>Rate</th>
                                            <th>Water Bill Amount</th>
                                            <th>Bill Date</th>
                                            <th>Status</th> <!-- Add this line -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['waterBillsData'] as $waterBill): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($waterBill['WaterBillID']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['room_id']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['PreviousReading']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['PresentReading']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['Consumption']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['Rate']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['WaterBillAmount']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['BillDate']) ?></td>
                                                <td><?= htmlspecialchars($waterBill['Status']) ?></td> <!-- Add this line -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <!-- End Table with stripped rows -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
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

function sortTable(order) {
  var rows, switching, i, x, y, shouldSwitch;
  var table = document.getElementById("dataTable");
  var tableBody = table.getElementsByTagName("tbody")[0];
  var newTableBody = document.createElement("tbody");

  // Convert the rows to an array for easier sorting
  rows = Array.from(tableBody.rows);

  // Use index 6 for 'Date Submitted' column
  var dateColumnIndex = 6;

  // Sort the rows based on the 'Date Submitted' column
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

</script>



<script>
// JavaScript function to handle status change
function changeStatus(requestId, newStatus) {
    // Send an AJAX request to the server to update the status
    $.ajax({
        url: 'update_status.php', // The PHP script that will update the status
        type: 'POST',
        data: {
            'request_id': requestId,
            'new_status': newStatus
        },
        success: function(response) {
            // Update the button text and class based on the new status
            const statusButton = document.querySelector(`#status-btn-${requestId}`);
            statusButton.textContent = newStatus;
            statusButton.className = `status-btn status-${newStatus.toLowerCase()}`;
            // Close the dropdown if it's open
            const dropdown = document.getElementById(`dropdown-${requestId}`);
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error('Status update failed:', error);
        }
    });
}

// Function to show the dropdown content
function showDropdown(requestId) {
    document.getElementById(`dropdown-${requestId}`).classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
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
}
</script>

<?php
function calculateTotalRevenue($filter) {
    global $conn;
    $currentDate = date("Y-m-d");
    $startOfToday = $currentDate;
    $startOfMonth = date("Y-m-01", strtotime($currentDate));
    $startOfYear = date("Y-01-01", strtotime($currentDate));

    $query = "SELECT SUM(paymentamount) AS total_revenue
              FROM payments
WHERE paymentstatus = 'Paid' AND paymentdate >= ?";

    switch ($filter) {
        case 'today':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 DAY)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfToday, $startOfToday);
            break;
        case 'month':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 MONTH)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfMonth, $startOfMonth);
            break;
        case 'year':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 YEAR)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfYear, $startOfYear);
            break;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalRevenue = isset($row['total_revenue']) ? $row['total_revenue'] : 0;
    $stmt->close();

    echo $totalRevenue;
}

function calculateAverageAmount($filter) {
    global $conn;
    $currentDate = date("Y-m-d");
    $startOfToday = $currentDate;
    $startOfMonth = date("Y-m-01", strtotime($currentDate));
    $startOfYear = date("Y-01-01", strtotime($currentDate));

    $query = "SELECT AVG(paymentamount) AS average_amount
              FROM payments
              WHERE PaymentDate >= ?";

    switch ($filter) {
        case 'today':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 DAY)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfToday, $startOfToday);
            break;
        case 'month':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 MONTH)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfMonth, $startOfMonth);
            break;
        case 'year':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 YEAR)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfYear, $startOfYear);
            break;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $averageAmount = isset($row['average_amount']) ? $row['average_amount'] : 0;
    $stmt->close();

    echo $averageAmount;
}

function countUnpaidBills($filter) {
    global $conn;
    $currentDate = date("Y-m-d");
    $startOfToday = $currentDate;
    $startOfMonth = date("Y-m-01", strtotime($currentDate));
    $startOfYear = date("Y-01-01", strtotime($currentDate));

    $query = "SELECT COUNT(*) AS unpaid_bills_count
              FROM payments
              WHERE paymentstatus != 'Paid' AND paymentdate >= ?";

    switch ($filter) {
        case 'today':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 DAY)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfToday, $startOfToday);
            break;
        case 'month':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 MONTH)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfMonth, $startOfMonth);
            break;
        case 'year':
            $query .= " AND PaymentDate < DATE_ADD(?, INTERVAL 1 YEAR)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $startOfYear, $startOfYear);
            break;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $unpaidBillsCount = isset($row['unpaid_bills_count']) ? $row['unpaid_bills_count'] : 0;
    $stmt->close();

    echo $unpaidBillsCount;

}
// Call the functions initially with the 'today' filter
echo '<script>calculateTotalRevenue("today");</script>';
echo '<script>calculateAverageAmount("today");</script>';
echo '<script>countUnpaidBills("today");</script>';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<!-- Bootstrap JS and its dependencies (jQuery and Popper.js) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</body>
</html>
