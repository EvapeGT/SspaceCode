<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../signin.html');
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
        .btn-view-description {
            background-color: #D54D5D;
            color: white;
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
    </style>
</head>

<body>

<main id="main" class="main">

<!-- ... Your existing HTML and PHP code ... -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Maintenance Service Requests</h5>
          <!-- Table with stripped rows -->
          <table class="table datatable table-borderless">
            <thead>
              <tr>
                <th>Room</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Issue Description</th>
                <th>Date Submitted</th>
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
                        <div class="status-btn status-<?= strtolower($request['Status']) ?>" onclick="changeStatus('<?= $request['RequestID'] ?>', '<?= $request['Status'] ?>')">
                            <?= htmlspecialchars($request['Status']) ?>
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

<!-- Bootstrap JS and its dependencies (jQuery and Popper.js) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
// JavaScript function to handle status change
function changeStatus(requestId, currentStatus) {
    // Define the new status based on the current status
    let newStatus = '';
    switch (currentStatus) {
        case 'Unassigned':
            newStatus = 'Pending';
            break;
        case 'Pending':
            newStatus = 'Completed';
            break;
        case 'Completed':
            newStatus = 'Unassigned';
            break;
        default:
            newStatus = 'Unassigned';
            break;
    }

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
            const statusButton = document.querySelector(`.status-btn[onclick*='${requestId}']`);
            statusButton.textContent = newStatus;
            statusButton.className = `status-btn status-${newStatus.toLowerCase()}`;
        },
        error: function(xhr, status, error) {
            // Handle errors here
            console.error('Status update failed:', error);
        }
    });
}
</script>

</body>
</html>
