<?php
    session_start();
 //signin.php 

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginID = $_POST["LoginID"];
    $password = $_POST["passwordID"];
    $stmt = $conn->prepare("SELECT `PasswordHash`, `UserType` FROM `Login` WHERE `LoginID` = ?");
    $stmt->bind_param("i", $loginID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userType = $row['UserType'];
        $storedPasswordHash = $row['PasswordHash'];
        if (password_verify($password, $storedPasswordHash)) {
            if ($userType == "admin") {
                $_SESSION['isAdminLoggedIn'] = true; // Set a session variable for admin login
                // Prepare the SQL statement to fetch data from rent_payments
                $rentPaymentsStmt = $conn->prepare("SELECT
                    RentPaymentID,
                    TenantID,
                    DATE_FORMAT(PaymentDate, '%Y-%m-%d') AS FormattedPaymentDate,
                    PaymentAmount,
                    PaymentMethod,
                    ReferenceNumber,
                    ReceiptImageURL,
                    PaymentStatus
                    FROM
                    rent_payments;");
            
                // Execute the statement
                $rentPaymentsStmt->execute();
                $rentPaymentsResult = $rentPaymentsStmt->get_result();
            
                // Initialize an array to store rent payment data
                $_SESSION['rentPaymentsData'] = [];
            
                // Fetch the data and store it in the array
                while ($rentPaymentRow = $rentPaymentsResult->fetch_assoc()) {
                    $_SESSION['rentPaymentsData'][] = [
                        'RentPaymentID' => $rentPaymentRow['RentPaymentID'],
                        'TenantID' => $rentPaymentRow['TenantID'],
                        'PaymentDate' => $rentPaymentRow['FormattedPaymentDate'],
                        'PaymentAmount' => $rentPaymentRow['PaymentAmount'],
                        'PaymentMethod' => $rentPaymentRow['PaymentMethod'],
                        'ReferenceNumber' => $rentPaymentRow['ReferenceNumber'],
                        'ReceiptImageURL' => $rentPaymentRow['ReceiptImageURL'],
                        'PaymentStatus' => $rentPaymentRow['PaymentStatus'],
                    ];
                }
            
                // Close the statement
                $rentPaymentsStmt->close();
            
                $waterBillsStmt = $conn->prepare("SELECT WaterBillID, room_id, PreviousReading, PresentReading, Consumption, Rate, WaterBillAmount, BillDate FROM water_bills;");
                $waterBillsStmt->execute();
                $waterBillsResult = $waterBillsStmt->get_result();
                $_SESSION['waterBillsData'] = array();
                while ($waterBillRow = $waterBillsResult->fetch_assoc()) {
                    $_SESSION['waterBillsData'][] = [
                        'WaterBillID' => $waterBillRow['WaterBillID'],
                        'room_id' => $waterBillRow['room_id'],
                        'PreviousReading' => $waterBillRow['PreviousReading'],
                        'PresentReading' => $waterBillRow['PresentReading'],
                        'Consumption' => $waterBillRow['Consumption'],
                        'Rate' => $waterBillRow['Rate'],
                        'WaterBillAmount' => $waterBillRow['WaterBillAmount'],
                        'BillDate' => $waterBillRow['BillDate'],
                    ];
                }
                $waterBillsStmt->close(); // Close the statement
            
                $tenantsStmt = $conn->prepare("SELECT
                    Tenants.TenantID,
                    Tenants.FirstName,
                    Tenants.LastName,
                    Tenants.Email,
                    Tenants.ContactNumber,
                    Contracts.ContractID,
                    Contracts.StartDate,
                    Contracts.EndDate,
                    Bills.BillID,
                    Bills.BillDate,
                    Bills.WaterBill,
                    Payments.PaymentID,
                    DATE_FORMAT(Payments.PaymentDate, '%Y-%m-%d') AS FormattedPaymentDate,
                    Payments.PaymentAmount,
                    Payments.PaymentMethod,
                    Payments.ReferenceNumber,
                    Payments.ReceiptImageURL,
                    Payments.PaymentStatus,
                    Rooms.room_id,
                    Rooms.price
                    FROM
                    Tenants
                    LEFT JOIN
                    Contracts ON Tenants.TenantID = Contracts.TenantID
                    LEFT JOIN
                    Bills ON Tenants.TenantID = Bills.TenantID
                    LEFT JOIN
                    Payments ON Tenants.TenantID = Payments.TenantID
                    LEFT JOIN
                    Rooms ON Tenants.room_id = Rooms.room_id
                    GROUP BY
                    Payments.PaymentID;");
            
                $tenantsStmt->execute();
                $tenantsResult = $tenantsStmt->get_result();
                while ($tenantRow = $tenantsResult->fetch_assoc()) {
                    $_SESSION['tenantsData'][] = [
                        'TenantID' => $tenantRow['TenantID'],
                        'FirstName' => $tenantRow['FirstName'],
                        'LastName' => $tenantRow['LastName'],
                        'Email' => $tenantRow['Email'],
                        'ContactNumber' => $tenantRow['ContactNumber'],
                        'room_id' => $tenantRow['room_id'],
                    ];
                    $_SESSION['contractsData'][] = [
                        'TenantID' => $tenantRow['TenantID'],
                        'ContractID' => $tenantRow['ContractID'],
                        'StartDate' => $tenantRow['StartDate'],
                        'EndDate' => $tenantRow['EndDate'],
                    ];
                    $_SESSION['billsData'][] = [
                        'TenantID' => $tenantRow['TenantID'],
                        'BillID' => $tenantRow['BillID'],
                        'BillDate' => $tenantRow['BillDate'],
                        'WaterBill' => $tenantRow['WaterBill'],
                    ];
                    $_SESSION['paymentsData'][] = [
                        'TenantID' => $tenantRow['TenantID'],
                        'PaymentID' => $tenantRow['PaymentID'],
                        'PaymentDate' => $tenantRow['FormattedPaymentDate'],
                        'PaymentAmount' => $tenantRow['PaymentAmount'],
                        'PaymentMethod' => $tenantRow['PaymentMethod'],
                        'PaymentStatus' => $tenantRow['PaymentStatus'],
                        'ReferenceNumber' => $tenantRow['ReferenceNumber'],
                        'ReceiptImageURL' => $tenantRow['ReceiptImageURL']
                    ];
                    $_SESSION['roomsData'][] = [
                        'room_id' => $tenantRow['room_id'],
                        'price' => $tenantRow['price'],
                    ];
                }
                $tenantsStmt->close();
                $revenueStmt = $conn->prepare("SELECT SUM(PaymentAmount) AS TotalRevenueThisMonth
                    FROM Payments
                    WHERE MONTH(PaymentDate) = MONTH(CURRENT_DATE)
                    AND YEAR(PaymentDate) = YEAR(CURRENT_DATE)
                    AND PaymentStatus = 'Paid'
                    AND WaterBillID IS NOT NULL;");
            
                $revenueStmt->execute();
                $revenueResult = $revenueStmt->get_result();
                $revenueData = $revenueResult->fetch_assoc();
                // Store the total revenue in a session variable
                $_SESSION['totalRevenueThisMonth'] = $revenueData['TotalRevenueThisMonth'];
            
                $serviceRequestsStmt = $conn->prepare("SELECT
                ServiceRequests.RequestID,
                ServiceRequests.TenantID,
                ServiceRequests.RequestDate,
                GROUP_CONCAT(DISTINCT ServiceRequests.Category SEPARATOR ', ') AS Category,
                ServiceRequests.IssueDescription,
                ServiceRequests.Status,
                ServiceRequests.room_id,
                ServiceRequests.Urgent, -- Include the Urgent column here
                Tenants.FirstName,
                Tenants.LastName,
                Tenants.Email,
                Tenants.ContactNumber
            FROM
                ServiceRequests
            LEFT JOIN
                Tenants ON ServiceRequests.TenantID = Tenants.TenantID
            GROUP BY
                ServiceRequests.RequestID;");
            
            $serviceRequestsStmt->execute();
            $serviceRequestsResult = $serviceRequestsStmt->get_result();
            
            // Initialize an array to store service request data
            $_SESSION['serviceRequestsData'] = [];
            
            // Fetch the data and store it in the array
            while ($serviceRequestRow = $serviceRequestsResult->fetch_assoc()) {
                $_SESSION['serviceRequestsData'][] = [
                    'TenantID' => $serviceRequestRow['TenantID'],
                    'RequestID' => $serviceRequestRow['RequestID'],
                    'RequestDate' => $serviceRequestRow['RequestDate'],
                    'Category' => $serviceRequestRow['Category'],
                    'IssueDescription' => $serviceRequestRow['IssueDescription'],
                    'Status' => $serviceRequestRow['Status'],
                    'room_id' => $serviceRequestRow['room_id'],
                    'FirstName' => $serviceRequestRow['FirstName'],
                    'LastName' => $serviceRequestRow['LastName'],
                    'Email' => $serviceRequestRow['Email'],
                    'ContactNumber' => $serviceRequestRow['ContactNumber'],
                    'Urgent' => $serviceRequestRow['Urgent'],
                ];
            }
            // Close the statement
            $serviceRequestsStmt->close();
            
            
            
                header('Location: NiceAdmin/index.php');
                exit();
            } else {
                // Tenant login logic
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['isUserLoggedIn'] = true;
                $tenantStmt = $conn->prepare("SELECT
                      Tenants.TenantID,
                      Tenants.FirstName,
                      Tenants.LastName,
                      Tenants.Email,
                      Tenants.ContactNumber,
                      Contracts.ContractID,
                      Contracts.StartDate,
                      Contracts.EndDate,
                      Bills.BillID,
                      Bills.BillDate,
                      Bills.WaterBill,
                      ServiceRequests.RequestID,
                      ServiceRequests.RequestDate,
                      ServiceRequests.Category,
                      ServiceRequests.IssueDescription,
                      ServiceRequests.Status,
                      Payments.PaymentID,
                      DATE_FORMAT(Payments.PaymentDate, '%Y-%m-%d') AS FormattedPaymentDate,
                      Payments.PaymentAmount,
                      Payments.PaymentMethod,
                      Payments.ReferenceNumber,
                      Payments.ReceiptImageURL,
                      Payments.PaymentStatus,
                      Rooms.room_id,
                      Rooms.price
                  FROM
                      Tenants
                  LEFT JOIN
                      Contracts ON Tenants.TenantID = Contracts.TenantID
                  LEFT JOIN
                      Bills ON Tenants.TenantID = Bills.TenantID
                  LEFT JOIN
                      ServiceRequests ON Tenants.TenantID = ServiceRequests.TenantID
                  LEFT JOIN
                      Payments ON Tenants.TenantID = Payments.TenantID
                  LEFT JOIN
                      Rooms ON Tenants.room_id = Rooms.room_id
                  WHERE
                      Tenants.LoginID = ?");

                    $tenantStmt->bind_param("i", $loginID);
                    $tenantStmt->execute();
                    $tenantResult = $tenantStmt->get_result();

while ($tenantRow = $tenantResult->fetch_assoc()) {
    if (empty($_SESSION['tenantTenantData'])) {
        $_SESSION['tenantTenantData'] = [];
    }
    if (empty($_SESSION['tenantContractData'])) {
        $_SESSION['tenantContractData'] = [];
    }
    if (empty($_SESSION['tenantServiceRequestData'])) {
        $_SESSION['tenantServiceRequestData'] = [];
    }
    if (empty($_SESSION['tenantPaymentData'])) {
        $_SESSION['tenantPaymentData'] = [];
    }

    if (empty($_SESSION['tenantRoomData'])) {
        $_SESSION['tenantRoomData'] = [];
    }

    $_SESSION['tenantTenantData'][] = [
        'TenantID' => $tenantRow['TenantID'],
        'FirstName' => $tenantRow['FirstName'],
        'LastName' => $tenantRow['LastName'],
        'Email' => $tenantRow['Email'],
        'ContactNumber' => $tenantRow['ContactNumber'],
        'room_id' => $tenantRow['room_id'],
    ];

    $_SESSION['tenantContractData'][] = [
        'ContractID' => $tenantRow['ContractID'],
        'StartDate' => $tenantRow['StartDate'],
        'EndDate' => $tenantRow['EndDate']
    ];

    $_SESSION['tenantServiceRequestData'][] = [
        'RequestID' => $tenantRow['RequestID'],
        'RequestDate' => $tenantRow['RequestDate'],
        'Category' => $tenantRow['Category'],
        'IssueDescription' => $tenantRow['IssueDescription'],
        'Status' => $tenantRow['Status']
    ];

    $_SESSION['tenantPaymentData'][] = [
        'PaymentID' => $tenantRow['PaymentID'],
        'PaymentDate' => $tenantRow['FormattedPaymentDate'],
        'PaymentAmount' => $tenantRow['PaymentAmount'],
        'PaymentMethod' => $tenantRow['PaymentMethod'],
        'ReferenceNumber' => $tenantRow['ReferenceNumber'],
        'ReceiptImageURL' => $tenantRow['ReceiptImageURL'],
        'PaymentStatus' => $tenantRow['PaymentStatus']
    ];

    $_SESSION['tenantRoomData'][] = [
        'room_id' => $tenantRow['room_id'],
        'price' => $tenantRow['price']
    ];
}

$tenantStmt->close();
header('Location: NiceTenant/index.php');
exit();
            }
        } else {
            // Incorrect password logic
            header('Location: NiceAdmin/pages-error-404.html');
            exit();
        }
    } else {
        // No user found logic
        header('Location: NiceAdmin/pages-error-404.html');
        exit();
    }
    $stmt->close();
}

$conn->close();
?>
