<?php
// view_receipt.php
// Database connection code
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
    header('Location: ../index.html');
    exit();
}

// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the payment ID is provided
if (isset($_GET['payment_id'])) {
    $paymentId = $_GET['payment_id'];

    // Fetch the receipt image data from the database
    $sql = "SELECT receiptImageURL FROM rent_payments WHERE RentPaymentID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageData = $row['receiptImageURL'];

        // Check if the image data is not empty
        if (!empty($imageData)) {
            // Decode the base64 encoded image data
            $imageData = base64_decode($imageData);

            // Determine the image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $imageType = $finfo->buffer($imageData);

            // Set the appropriate content type header
            header("Content-Type: $imageType");

            // Output the image data
            echo $imageData;
        } else {
            echo 'No receipt image found for the specified payment.';
        }
    } else {
        echo 'Invalid payment ID.';
    }

    $stmt->close();
} else {
    echo 'Payment ID not provided.';
}

$conn->close();
?>
