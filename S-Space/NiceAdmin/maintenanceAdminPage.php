<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../index.html');
  exit();
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
      <li class="breadcrumb-item active">Maintenance Dashboard</li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section dashboard">
  <div class="row">
    <!-- Pending Tickets Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card">
    <div class="filter">
      <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
          <h6>Filter</h6>
        </li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('pending', 'today')">Today</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('pending', 'month')">This Month</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('pending', 'year')">This Year</a></li>
      </ul>
    </div>
    <div class="card-body">
      <h5 class="card-title">Pending Tickets <span>| Today</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="ps-3">
          <h6 id="pending-tickets-count">...</h6>
          <span class="text-warning small pt-1 fw-bold">Pending</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Pending Tickets Card -->

<!-- Completed Tickets Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card">
    <div class="filter">
      <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
          <h6>Filter</h6>
        </li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('completed', 'today')">Today</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('completed', 'month')">This Month</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('completed', 'year')">This Year</a></li>
      </ul>
    </div>
    <div class="card-body">
      <h5 class="card-title">Completed Tickets <span>| Today</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="ps-3">
          <h6 id="completed-tickets-count">...</h6>
          <span class="text-success small pt-1 fw-bold">Completed</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Completed Tickets Card -->

<!-- Unassigned Tickets Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card">
    <div class="filter">
      <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <li class="dropdown-header text-start">
          <h6>Filter</h6>
        </li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('unassigned', 'today')">Today</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('unassigned', 'month')">This Month</a></li>
        <li><a class="dropdown-item" href="#" onclick="filterTickets('unassigned', 'year')">This Year</a></li>
      </ul>
    </div>
    <div class="card-body">
      <h5 class="card-title">Unassigned Tickets <span>| Today</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
          <i class="bi bi-exclamation-circle"></i>
        </div>
        <div class="ps-3">
          <h6 id="unassigned-tickets-count">...</h6>
          <span class="text-danger small pt-1 fw-bold">Unassigned</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Unassigned Tickets Card -->
            
<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <!-- Container for the title and controls -->
          <div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="card-title">Maintenance Service Requests</h5>

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
      <th>Room</th>
      <th>Customer Name</th>
      <th>Email</th>
      <th>Contact Number</th>
      <th>Category</th>
      <th>Issue Description</th>
      <th>Date Submitted</th>
      <th>Urgent</th> <!-- Added Urgent column -->
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($_SESSION['serviceRequestsData'] as $index => $request): ?>
      <?php
      if (empty($request['IssueDescription'])) {
        continue;
      }
      $tenantData = array_filter($_SESSION['tenantsData'], function ($tenant) use ($request) {
        return $tenant['TenantID'] === $request['TenantID'];
      });
      $roomData = array_filter($_SESSION['roomsData'], function ($room) use ($request) {
        return $room['room_id'] === $request['room_id'];
      });
      $tenantInfo = reset($tenantData);
      $roomInfo = reset($roomData);
      ?>
      <tr>
        <td><?= htmlspecialchars($roomInfo['room_id'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($tenantInfo['FirstName'] ?? 'N/A') . ' ' . htmlspecialchars($tenantInfo['LastName'] ?? '') ?></td>
        <td><?= htmlspecialchars($tenantInfo['Email'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($tenantInfo['ContactNumber'] ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($request['Category'] ?? 'N/A') ?></td>
        <td>
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-view-description btn-sm" data-toggle="modal" data-target="#issueModal<?= $index ?>">
            View Description
          </button>

          <!-- Modal -->
          <div class="modal fade" id="issueModal<?= $index ?>" tabindex="-1" role="dialog" aria-labelledby="issueModalLabel<?= $index ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="issueModalLabel<?= $index ?>">Issue Description</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
                <div class="modal-body">
                  <?= nl2br(htmlspecialchars($request['IssueDescription'])) ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td><?= htmlspecialchars($request['RequestDate']) ?></td>
        <td>
          <?php if (strtolower($request['Urgent']) === 'yes'): ?>
            <span class="badge bg-danger text-white">Urgent</span>
          <?php else: ?>
            <span class="badge bg-success text-white">Normal</span>
          <?php endif; ?>
        </td>
        <td>
          <div class="status-dropdown">
            <button id="status-btn-<?= $request['RequestID'] ?>" class="status-btn dropdown-toggle status-<?= strtolower($request['Status']) ?>" onclick="showDropdown('<?= $request['RequestID'] ?>')">
              <?= htmlspecialchars($request['Status']) ?>
            </button>
            <div id="dropdown-<?= $request['RequestID'] ?>" class="status-dropdown-content">
              <a href="#" onclick="changeStatus('<?= $request['RequestID'] ?>', 'Pending')">Pending</a>
              <a href="#" onclick="changeStatus('<?= $request['RequestID'] ?>', 'Unassigned')">Unassigned</a>
              <a href="#" onclick="changeStatus('<?= $request['RequestID'] ?>', 'Completed')">Completed</a>
            </div>
          </div>
        </td>
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
<script>
  function filterTickets(type, period) {
  const today = new Date();
  const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
  const startOfYear = new Date(today.getFullYear(), 0, 1);

  let filteredData;
  switch (period) {
    case 'today':
      filteredData = <?php echo json_encode($_SESSION['serviceRequestsData']); ?>.filter(request => {
        const requestDate = new Date(request.RequestDate);
        return requestDate >= startOfToday && requestDate < new Date(startOfToday.getTime() + 24 * 60 * 60 * 1000);
      });
      break;
    case 'month':
      filteredData = <?php echo json_encode($_SESSION['serviceRequestsData']); ?>.filter(request => {
        const requestDate = new Date(request.RequestDate);
        return requestDate >= startOfMonth && requestDate < new Date(startOfMonth.getFullYear(), startOfMonth.getMonth() + 1, 1);
      });
      break;
    case 'year':
      filteredData = <?php echo json_encode($_SESSION['serviceRequestsData']); ?>.filter(request => {
        const requestDate = new Date(request.RequestDate);
        return requestDate >= startOfYear && requestDate < new Date(startOfYear.getFullYear() + 1, 0, 1);
      });
      break;
  }

  const pendingCount = filteredData.filter(request => request.Status === 'Pending').length;
  const completedCount = filteredData.filter(request => request.Status === 'Completed').length;
  const unassignedCount = filteredData.filter(request => request.Status === 'Unassigned').length;

  document.getElementById('pending-tickets-count').textContent = pendingCount;
  document.getElementById('completed-tickets-count').textContent = completedCount;
  document.getElementById('unassigned-tickets-count').textContent = unassignedCount;

  // Update the span text for each card
  const pendingSpan = document.querySelector('.col-xxl-4:nth-child(1) .card-title span');
  const completedSpan = document.querySelector('.col-xxl-4:nth-child(2) .card-title span');
  const unassignedSpan = document.querySelector('.col-xxl-4:nth-child(3) .card-title span');

  pendingSpan.textContent = `| ${period.charAt(0).toUpperCase() + period.slice(1)}`;
  completedSpan.textContent = `| ${period.charAt(0).toUpperCase() + period.slice(1)}`;
  unassignedSpan.textContent = `| ${period.charAt(0).toUpperCase() + period.slice(1)}`;
}
</script>
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
