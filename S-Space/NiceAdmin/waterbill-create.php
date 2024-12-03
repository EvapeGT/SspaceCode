
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        body.toggle-sidebar .container {
            transform: translateX(-250px); /* Adjust based on your sidebar's width */
            width: calc(100% + 250px); /* Increase width to take up the space */
        }

        .form-control {
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* New CSS for selected room box */
        .selected-room-box {
            background-color: #fff3cd; /* Bootstrap light yellow */
            border: 1px solid #ffeeba; /* Bootstrap border color */
            padding: 10px;
            border-radius: 5px;
            display: inline-flex; /* Adjusted to use flexbox */
            align-items: center; /* Align items vertically */
        }

        /* Adjust padding and margin for the icon */
        .info-icon {
    margin-right: 10px;
    width: 20px;
    height: 20px;
    fill: #ffc107; /* Bootstrap warning color */
}
    </style>
    
</head>

<body>
<?php
    include 'header.inc.php';
    ?>
    <section class="invoice-container">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Generate Water Bill</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <br>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#roomModal">
                                    Select Room
                                </button>
                                <!-- Updated selected room box with information icon -->
<div class="selected-room-box">
    <i class="bi bi-info-circle-fill text-primary mr-2"></i>
    <span id="selectedRoomName"> </span>
</div>

                            </div>
                            <div class="form-group">
                                <label for="prevReading">Previous Reading:</label>
                                <input type="number" class="form-control" id="prevReading" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="presReading">Present Reading:</label>
                                <input type="number" class="form-control" id="presReading" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="rateCuM">Rate per Cubic Meter (Cu.M):</label>
                                <input type="number" class="form-control" id="rateCuM" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="dueDate">Due Date:</label>
                                <input type="date" class="form-control" id="dueDate">
                            </div>
                            <div class="form-group">
                                <label for="period">Period:</label>
                                <input type="text" class="form-control" id="period" readonly>
                            </div>
                            <div class="form-group">
                                <label for="consumption">Consumption (Cu.M):</label>
                                <input type="text" class="form-control" id="consumption" readonly>
                            </div>
                            <div class="form-group">
                                <label for="amountDue">Amount Due:</label>
                                <input type="text" class="form-control" id="amountDue" readonly>
                            </div>
                            <button type="button" class="btn btn-primary" id="generateBill">Generate Bill</button>
                            <div id="response"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

  <!-- Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1" role="dialog" aria-labelledby="roomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalLabel">Select Room</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Room ID</th>
                            <th>Tenants</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
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

                    // Fetch room data from the database
                    $sql = "SELECT r.room_id, GROUP_CONCAT(t.FirstName, ' ', t.LastName, ' (', t.Email, ' / ', t.ContactNumber, ')' SEPARATOR '<br>') AS tenant_details
                            FROM rooms r
                            LEFT JOIN tenants t ON r.room_id = t.room_id
                            GROUP BY r.room_id
                            ORDER BY r.room_id";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $roomId = $row['room_id'];
                            $tenantDetails = $row['tenant_details'] ?: 'No tenants';
                    ?>
                    <tr>
                        <td><?php echo $roomId; ?></td>
                        <td>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#tenantModal<?php echo $roomId; ?>">
                                Show Tenants
                            </button>

                            <!-- Tenant Modal -->
                            <div class="modal fade tenantModal" id="tenantModal<?php echo $roomId; ?>" tabindex="-1" role="dialog" aria-labelledby="tenantModalLabel<?php echo $roomId; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="tenantModalLabel<?php echo $roomId; ?>">Tenants in Room <?php echo $roomId; ?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <?php echo $tenantDetails; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><button class="btn btn-primary btn-sm selectRoom" data-room-id="<?php echo $roomId; ?>">Select</button></td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="3">No rooms found.</td></tr>';
                    }

                    $conn->close();
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
    $('.selectRoom').click(function() {
        var roomId = $(this).data('room-id');
        console.log('Selected Room ID: ' + roomId);
        $('#roomModal').modal('hide');
    });

    // Function to update the invoice details
    function updateInvoiceDetails() {
        var prevReading = parseFloat($('#prevReading').val());
        var presReading = parseFloat($('#presReading').val());
        var rateCuM = parseFloat($('#rateCuM').val());
        var dueDate = $('#dueDate').val();

        // Check if all required fields are filled
        if (!isNaN(prevReading) && !isNaN(presReading) && !isNaN(rateCuM) && dueDate !== '') {
            var currentDate = new Date();
            var previousMonth = currentDate.getMonth() === 0 ? 11 : currentDate.getMonth() - 1;
            var periodStart = new Date(currentDate.getFullYear(), previousMonth, 1);
            var periodEnd = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0);

            var periodStartFormatted = periodStart.toLocaleString('default', { month: 'long' }) + ' ' + periodStart.getFullYear();
            var periodEndFormatted = periodEnd.toLocaleString('default', { month: 'long' }) + ' ' + periodEnd.getFullYear();

            var period = periodStartFormatted + ' - ' + periodEndFormatted;
            $('#period').val(period);

            var consumption = presReading - prevReading;
            $('#consumption').val(consumption.toFixed(2));

            var amountDue = consumption * rateCuM;
            $('#amountDue').val('₱ ' + amountDue.toFixed(2));
        }
    }

    // Event listeners for input fields to update the invoice details automatically
    $('#prevReading, #presReading, #rateCuM, #dueDate').on('input change', function() {
        updateInvoiceDetails();
    });

    // Initial call to set the invoice details if the fields are pre-filled
    updateInvoiceDetails();
});

    </script>
    <script>
    let selectedRoomId = null; // Declare a global variable to store the selected room ID
    let selectedRoomName = null; // Declare a variable to store the selected room name
    let successAlert = null; // Variable to store the success alert element

    $(document).ready(function() {
    $('.selectRoom').click(function() {
        selectedRoomId = $(this).data('room-id');
        selectedRoomName = 'Room ' + $(this).data('room-id'); // Store the room name

        // Display the success alert
        successAlert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">')
            .append('You have successfully selected ' + selectedRoomName + '.')
            .append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>')
            .css({
                'position': 'fixed',
                'top': '50%',
                'left': '50%',
                'transform': 'translate(-50%, -50%)',
                'z-index': '9999'
            })
            .appendTo('body');

        // Automatically close the success alert after 3 seconds
        setTimeout(function() {
            successAlert.alert('close');
        }, 3000);

        // Display the selected room name
        $('#selectedRoomName').text(selectedRoomName);
    });

    // Prevent the close event of the tenant modal from closing the room modal
    $('.tenantModal').on('click', '.close', function(event) {
        event.stopPropagation();
    });
        $('#generateBill').click(function() {
            var prevReading = $('#prevReading').val();
            var presReading = $('#presReading').val();
            var rateCuM = $('#rateCuM').val();
            var dueDate = $('#dueDate').val();
            var period = $('#period').val();
            var consumption = $('#consumption').val();
            var amountDue = $('#amountDue').val().replace('₱ ', '');

            $.ajax({
                type: 'POST',
                url: 'insert_water_bill.php',
                data: {
                    roomId: selectedRoomId, // Use the selectedRoomId variable
                    prevReading: prevReading,
                    presReading: presReading,
                    rateCuM: rateCuM,
                    dueDate: dueDate,
                    period: period,
                    consumption: consumption,
                    amountDue: amountDue
                },
                success: function(response) {
    if (response.includes('Bill inserted successfully')) {
        // Bill inserted successfully
        $('#response').html('<div class="alert alert-success">' + response + '</div>');
    } else {
        // Error occurred
        $('#response').html('<div class="alert alert-danger">' + response + '</div>');
    }
},
                error: function() {
                    $('#response').html('Error occurred while inserting water bill.');
                }
            });
        });
    });
    </script>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
