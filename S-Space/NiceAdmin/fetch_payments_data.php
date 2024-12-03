<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../signin.html');
  exit();
  }

// Connect to the database and fetch the updated payments data
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM payments";
$result = $conn->query($sql);

$paymentsData = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paymentsData[] = $row;
    }
}

$_SESSION['paymentsData'] = $paymentsData;

$conn->close();

// Return the payments data as JSON
echo json_encode($paymentsData);