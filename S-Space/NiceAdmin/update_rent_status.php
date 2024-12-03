<?php
//update_rent_status.php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    $response = ["status" => "error", "message" => "Connection failed: " . $conn->connect_error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if (!isset($_POST['payment_id'], $_POST['new_status'])) {
    $response = ["status" => "error", "message" => "Request ID or new status not set."];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$requestId = $_POST['payment_id'];
$newStatus = $_POST['new_status'];

// Debugging output for incoming POST data
error_log("Received POST data - payment_id: $requestId, new_status: $newStatus");

$stmt = $conn->prepare("UPDATE rent_payments SET PaymentStatus = ? WHERE RentPaymentID = ?");
if (!$stmt) {
    $response = ["status" => "error", "message" => "Prepare statement failed: " . $conn->error];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Bind parameters
$stmt->bind_param("si", $newStatus, $requestId);

if ($stmt->execute()) {
    $response = ["status" => "success", "message" => "Status updated successfully"];
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response = ["status" => "error", "message" => "Error updating status: " . $stmt->error];
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Debugging output for statement error
error_log("Statement error: " . $stmt->error);

$stmt->close();
$conn->close();