<?php
session_start();

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

// Get the form data
$issueDescription = $_POST['issueDescription'];
$category = $_POST['category'];
$tenantID = $_POST['tenantID'];
$roomID = $_POST['roomID'];

// Prepare and execute the SQL statement to insert the service request
$stmt = $conn->prepare("INSERT INTO servicerequests (TenantID, RequestDate, IssueDescription, Status, Category, room_id) VALUES (?, ?, ?, ?, ?, ?)");
$requestDate = date('Y-m-d'); // Get the current date
$status = 'Pending'; // Set the initial status to 'Pending'
$stmt->bind_param("issssi", $tenantID, $requestDate, $issueDescription, $status, $category, $roomID);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>