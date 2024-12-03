<?php
// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check for a connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request ID and new status are set
if (isset($_POST['request_id'], $_POST['new_status'])) {
    // Assign POST variables
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['new_status'];

    // Prepare the SQL statement to update the status
    $stmt = $conn->prepare("UPDATE servicerequests SET Status = ? WHERE RequestID = ?");
    $stmt->bind_param("si", $newStatus, $requestId);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Request ID or new status not set.";
}

// Close the database connection
$conn->close();
?>