    <?php
    // Database connection code
    //insert_waterbill.php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "s_space_tenant_portal";
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start the session
    session_start();
    // Retrieve form data
    $roomId = $_POST['roomId'];
    $prevReading = $_POST['prevReading'];
    $presReading = $_POST['presReading'];
    $rateCuM = $_POST['rateCuM'];
    $dueDate = $_POST['dueDate'];
    $period = $_POST['period'];
    $consumption = $_POST['consumption'];
    $amountDue = $_POST['amountDue'];

    // Perform data validation
    // ...// Prepare the SQL statement
    $sql = "INSERT INTO water_bills (room_id, PreviousReading, PresentReading, Consumption, Rate, WaterBillAmount, BillDate)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("sddddds", $roomId, $prevReading, $presReading, $consumption, $rateCuM, $amountDue, $dueDate);

    // Execute the statement
    if ($stmt->execute()) {
    // Successful insertion
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
        Water bill sent successfully.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
    } else {
    // Error occurred
    echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
    ?>