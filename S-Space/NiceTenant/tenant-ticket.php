<?php
include 'header.inc.php';

// Check if the user is logged in
if (!isset($_SESSION['isUserLoggedIn']) || $_SESSION['isUserLoggedIn'] !== true) {
    // Redirect to the login page if the user is not logged in
    header('Location: index.html');
    exit;
}

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new mysqli instance
$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Maintenance Request Tenant Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/cssA/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="assets/cssA/animate.css">
    <link rel="stylesheet" href="assets/cssA/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/cssA/owl.theme.default.min.css">
    <link rel="stylesheet" href="assets/cssA/magnific-popup.css">
    <link rel="stylesheet" href="assets/cssA/aos.css">
    <link rel="stylesheet" href="assets/cssA/ionicons.min.css">
    <link rel="stylesheet" href="assets/cssA/bootstrap-datepicker.css">
    <link rel="stylesheet" href="assets/cssA/jquery.timepicker.css">
    <link rel="stylesheet" href="assets/cssA/flaticon.css">
    <link rel="stylesheet" href="assets/cssA/icomoon.css">
    <link rel="stylesheet" href="assets/cssA/styleA.css">
    <style>
      /* Custom styles for the select category */
      select#category {
        background-color: #f8f9fa; /* Light gray background color */
        color: #343a40; /* Dark text color */
      }

      select#category option {
        color: #343a40; /* Dark text color for options */
      }
    </style>
  </head>
  <body>

    <div class="hero-wrap ftco-degree-bg" style="background-image: url('assets/imagesA/cafeteriareal.jpg');" data-stellar-background-ratio="0.5">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text justify-content-start align-items-center justify-content-center">
          <div class="col-lg-8 ftco-animate">
            <div class="text w-100 text-center mb-md-5 pb-md-5">
              <h1 class="mb-4">Fast &amp; Easy Way To Submit A Ticket</h1>
              <p style="font-size: 18px;">Quickly and easily report any issues and track the status of your maintenance requests.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section ftco-no-pt bg-light">
      <div class="container">
        <div class="row no-gutters">
          <div class="col-md-12 featured-top">
            <div class="row no-gutters">
            <div class="col-md-4 d-flex align-items-center">
            <form id="ticketForm" method="post" class="request-form ftco-animate bg-primary">
    <h2>Make a Ticket Request</h2>
    <div class="form-group">
        <label for="category" class="label">Issue Category:</label>
        <select class="form-control" name="category" id="category" required>
            <option value="">Select Category</option>
            <?php
            $categories = array("HVAC", "Electrical", "Plumbing", "Maintenance", "Safety", "Roofing");
            foreach ($categories as $category) {
                echo "<option value=\"$category\">$category</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="issueDescription" class="label">Issue Description:</label>
        <textarea class="form-control" name="issueDescription" id="issueDescription" required></textarea>
    </div>
    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" name="urgent" id="urgent">
        <label class="form-check-label" for="urgent">Urgent</label>
    </div>
    <div class="form-group">
        <input type="button" value="Submit a Ticket" class="btn btn-secondary py-3 px-4" id="submitTicket">
    </div>
</form>


</div>
              <div class="col-md-8 d-flex align-items-center">
                <div class="services-wrap rounded-right w-100">
                  <h3 class="heading-section mb-4">How your ticket is processed</h3>
                  <div class="row d-flex mb-4">
                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                      <div class="services w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center">
                          <i class="bi bi-geo-alt display-3"></i>
                        </div>
                        <div class="text w-100">
                          <h3 class="heading mb-2">Your room is located</h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                      <div class="services w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center">
                          <i class="bi bi-person-check display-3"></i>
                        </div>
                        <div class="text w-100">
                          <h3 class="heading mb-2">We select the worker fitted to your issues</h3>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                      <div class="services w-100 text-center light-red">
                        <div class="icon d-flex align-items-center justify-content-center">
                          <i class="bi bi-tools display-3"></i>
                        </div>
                        <div class="text w-100">
                          <h3 class="heading mb-2">Then we solve the problem</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section ftco-no-pt bg-light">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section ftco-animate text-center">
            <h2 class="mb-4">Your Submitted Tickets</h2>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ticketModal">View</button>
          </div>
        </div>
      </div>
    </section>
    <!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Success!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Service request submitted successfully!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

    <!-- Modal -->
    <div class="modal fade" id="ticketModal" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="ticketModalLabel">Your Submitted Tickets</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="table-responsive<div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Request ID</th>
                    <th>Request Date</th>
                    <th>Issue Description</th>
                    <th>Category</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Database connection details
                  $host = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "s_space_tenant_portal";

                  // Create a new mysqli instance
                  $conn = new mysqli($host, $username, $password, $database);

                  // Check for connection errors
                  if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                  }

                  // Get the tenant ID from the session data
                  $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];

                  // Prepare and execute the SQL statement to fetch the service requests for the current tenant
                  $stmt = $conn->prepare("SELECT RequestID, RequestDate, IssueDescription, Category, Status FROM servicerequests WHERE TenantID = ?");
                  $stmt->bind_param("i", $tenantID);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  // Loop through the results and display them in the table
                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>" . $row['RequestID'] . "</td>";
                      echo "<td>" . $row['RequestDate'] . "</td>";
                      echo "<td>" . $row['IssueDescription'] . "</td>";
                      echo "<td>" . $row['Category'] . "</td>";
                      echo "<td>" . $row['Status'] . "</td>";
                      echo "</tr>";
                    }
                  } else {
                    echo "<tr><td colspan='5'>No tickets found.</td></tr>";
                  }

                  // Close the statement and database connection
                  $stmt->close();
                  $conn->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section ftco-no-pt bg-light">
      <section class="ftco-counter ftco-section img bg-light" id="section-counter">
        <div class="overlay"></div>
        <div class="container">
          <div class="row">
            <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
              <div class="block-18">
                <div class="text text-border d-flex align-items-center">
                  <strong class="number" data-number="4">0</strong>
                  <span>Year <br>Experienced</span>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
              <div class="block-18">
                <div class="text text-border d-flex align-items-center">
                  <strong class="number" data-number="400">0</strong>
                  <span>Total <br>Beds</span>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
              <div class="block-18">
                <div class="text text-border d-flex align-items-center">
                  <strong class="number" data-number="100">0</strong>
                  <span>Happy <br>Customers</span>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
              <div class="block-18">
                <div class="text d-flex align-items-center">
                  <strong class="number" data-number="2">0</strong>
                  <span>Total <br>Branches</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </section>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

    <script src="assets/jsA/jquery.min.js"></script>
    <script src="assets/jsA/jquery-migrate-3.0.1.min.js"></script>
    <script src="assets/jsA/popper.min.js"></script>
    <script src="assets/jsA/bootstrap.min.js"></script>
    <script src="assets/jsA/jquery.easing.1.3.js"></script>
    <script src="assets/jsA/jquery.waypoints.min.js"></script>
    <script src="assets/jsA/jquery.stellar.min.js"></script>
    <script src="assets/jsA/owl.carousel.min.js"></script>
    <script src="assets/jsA/jquery.magnific-popup.min.js"></script>
    <script src="assets/jsA/aos.js"></script>
    <script src="assets/jsA/jquery.animateNumber.min.js"></script>
    <script src="assets/jsA/bootstrap-datepicker.js"></script>
    <script src="assets/jsA/jquery.timepicker.min.js"></script>
    <script src="assets/jsA/scrollax.min.js"></script>
    <script src="assets/jsA/main.js"></script>
    <script>
   $(document).ready(function() {
    $('#submitTicket').click(function(e) {
        e.preventDefault();

        var issueDescription = $('#issueDescription').val();
        var category = $('#category').val();
        var urgent = $('#urgent').is(':checked') ? 'Yes' : 'No'; // Correctly setting urgent value
        var tenantID = <?php echo $_SESSION['tenantTenantData'][0]['TenantID']; ?>;
        var roomID = <?php echo $_SESSION['tenantRoomData'][0]['room_id']; ?>;

        if (issueDescription && category) {
            $.ajax({
                type: 'POST',
                url: 'ticket-submit.php',
                data: {
                    issueDescription: issueDescription,
                    category: category,
                    urgent: urgent,
                    tenantID: tenantID,
                    roomID: roomID
                },
                success: function(response) {
                    if (response === 'success') {
                        $('#issueDescription').val('');
                        $('#category').val('');
                        $('#urgent').prop('checked', false);
                        $('#successModal').modal('show');
                    } else {
                        alert('Error submitting the ticket. Please try again.');
                    }
                },
                error: function() {
                    alert('An error occurred while submitting the ticket. Please try again later.');
                }
            });
        } else {
            alert('Please fill in all required fields.');
        }
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});
</script>
  </body>
</html>