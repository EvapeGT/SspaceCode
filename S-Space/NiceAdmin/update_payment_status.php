<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
    header('Location: ../signin.html');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database credentials
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "s_space_tenant_portal";

    // Connection to the database
    $conn = new mysqli($host, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the value from POST request
    $paymentID = isset($_POST['payment_id']) ? $conn->real_escape_string($_POST['payment_id']) : '';
    $newStatus = isset($_POST['new_status']) ? $conn->real_escape_string($_POST['new_status']) : '';

    // Update query
    $stmt = $conn->prepare("UPDATE Payments SET PaymentStatus = ? WHERE PaymentID = ?");
    $stmt->bind_param("si", $newStatus, $paymentID);

    // Execute the query
    if ($stmt->execute()) {
        // Update the session data only after successfully updating the database
        $paymentIndex = array_search($paymentID, array_column($_SESSION['paymentsData'], 'PaymentID'));
        if ($paymentIndex !== false) {
            $_SESSION['paymentsData'][$paymentIndex]['PaymentStatus'] = $newStatus;
        }
        echo "Payment status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
}
?>