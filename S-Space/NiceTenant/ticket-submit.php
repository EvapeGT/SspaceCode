<?php
session_start();

if (!isset($_SESSION['isUserLoggedIn']) || $_SESSION['isUserLoggedIn'] !== true) {
    header('Location: index.html');
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $issueDescription = $_POST['issueDescription'];
    $category = $_POST['category'];
    $urgent = isset($_POST['urgent']) && $_POST['urgent'] === 'Yes' ? 'Yes' : 'No'; // Correctly handling urgent value

    $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];
    $roomID = $_SESSION['tenantRoomData'][0]['room_id'];

    $stmt = $conn->prepare("INSERT INTO servicerequests (TenantID, RequestDate, IssueDescription, Status, Category, room_id, Urgent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $requestDate = date('Y-m-d');
    $status = 'Pending';
    $stmt->bind_param("issssis", $tenantID, $requestDate, $issueDescription, $status, $category, $roomID, $urgent);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
