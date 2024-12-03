<?php
// rent_payment_processor.php
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
    $billType = 'Rent Payment'; // Fixed as Rent Payment
    $billId = $_POST['billID'];
    $paymentAmount = $_POST['billAmount'];
    $paymentDate = date('Y-m-d H:i:s'); // Updated to include time

    // Check if a file was uploaded
    if (isset($_FILES['receiptImage']) && $_FILES['receiptImage']['error'] === UPLOAD_ERR_OK) {
        // Get the file content
        $fileTmpPath = $_FILES['receiptImage']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);

        // Encode the file content in base64
        $fileContent = base64_encode($fileContent);

        // Prepare the SQL statement
        $stmt = $conn->prepare("UPDATE rent_payments
                                SET PaymentDate=?, PaymentAmount=?, PaymentMethod=?, ReferenceNumber=?, receiptImageURL=?, PaymentStatus='Pending'
                                WHERE RentPaymentID=? AND TenantID=?");

        // Bind the parameters
        $stmt->bind_param("sdsssii", $paymentDate, $paymentAmount, $paymentMethod, $referenceNumber, $fileContent, $billId, $tenantID);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Payment submitted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle the error if the file was not uploaded correctly
        echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
    }
}

$conn->close();
?>
