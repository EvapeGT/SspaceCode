<?php
// payment_processor.php
session_start();

// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];
    $paymentMethod = $_POST['paymentMethod'];
    $referenceNumber = $_POST['referenceNumber'];
    $billType = 'Water'; // Fixed as Water
    $billId = $_POST['billID'];
    $paymentAmount = $_POST['billAmount'];
    $paymentDate = date('Y-m-d');

    // Check if a file was uploaded
    if (isset($_FILES['receiptImage']) && $_FILES['receiptImage']['error'] === UPLOAD_ERR_OK) {
        // Get the file content
        $fileTmpPath = $_FILES['receiptImage']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);

        // Prepare the file content for insertion into the database
        $fileContent = mysqli_real_escape_string($conn, $fileContent);

        // Insert the payment into the database
        $sql = "INSERT INTO payments (TenantID, PaymentDate, PaymentAmount, PaymentMethod, referenceNumber, receiptImageURL, PaymentStatus, WaterBillID)
                VALUES ('$tenantID', '$paymentDate', '$paymentAmount', '$paymentMethod', '$referenceNumber', '$fileContent', 'Pending', '$billId')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Payment submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $sql . '<br>' . $conn->error]);
        }
    } else {
        // Handle the error if the file was not uploaded correctly
        echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
    }
}

$conn->close();
?>
