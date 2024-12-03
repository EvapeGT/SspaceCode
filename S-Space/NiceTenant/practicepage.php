<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch data based on payment status
function fetchPaymentsByStatus($conn, $status) {
    $sql = "SELECT * FROM rent_payments WHERE PaymentStatus = '$status'";
    $result = $conn->query($sql);
    return $result;
}

$paidBills = fetchPaymentsByStatus($conn, 'Paid');
$pendingBills = fetchPaymentsByStatus($conn, 'Pending');
$unpaidBills = fetchPaymentsByStatus($conn, 'Unpaid');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Payments Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Rent Payments Status</h1>

        <!-- Paid Bills Table -->
        <h2 class="mt-5">Paid Bills</h2>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>RentPaymentID</th>
                    <th>TenantID</th>
                    <th>PaymentDate</th>
                    <th>PaymentAmount</th>
                    <th>PaymentMethod</th>
                    <th>ReferenceNumber</th>
                    <th>PaymentStatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($paidBills->num_rows > 0) {
                    while($row = $paidBills->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['RentPaymentID']; ?></td>
                            <td><?php echo $row['TenantID']; ?></td>
                            <td><?php echo $row['PaymentDate']; ?></td>
                            <td><?php echo $row['PaymentAmount']; ?></td>
                            <td><?php echo $row['PaymentMethod']; ?></td>
                            <td><?php echo $row['ReferenceNumber']; ?></td>
                            <td><?php echo $row['PaymentStatus']; ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="7">No paid bills found.</td></tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pending Bills Table -->
        <h2 class="mt-5">Pending Bills</h2>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>RentPaymentID</th>
                    <th>TenantID</th>
                    <th>PaymentDate</th>
                    <th>PaymentAmount</th>
                    <th>PaymentMethod</th>
                    <th>ReferenceNumber</th>
                    <th>PaymentStatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendingBills->num_rows > 0) {
                    while($row = $pendingBills->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['RentPaymentID']; ?></td>
                            <td><?php echo $row['TenantID']; ?></td>
                            <td><?php echo $row['PaymentDate']; ?></td>
                            <td><?php echo $row['PaymentAmount']; ?></td>
                            <td><?php echo $row['PaymentMethod']; ?></td>
                            <td><?php echo $row['ReferenceNumber']; ?></td>
                            <td><?php echo $row['PaymentStatus']; ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="7">No pending bills found.</td></tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Unpaid Bills Table -->
        <h2 class="mt-5">Unpaid Bills</h2>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>RentPaymentID</th>
                    <th>TenantID</th>
                    <th>PaymentDate</th>
                    <th>PaymentAmount</th>
                    <th>PaymentMethod</th>
                    <th>ReferenceNumber</th>
                    <th>PaymentStatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($unpaidBills->num_rows > 0) {
                    while($row = $unpaidBills->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['RentPaymentID']; ?></td>
                            <td><?php echo $row['TenantID']; ?></td>
                            <td><?php echo $row['PaymentDate']; ?></td>
                            <td><?php echo $row['PaymentAmount']; ?></td>
                            <td><?php echo $row['PaymentMethod']; ?></td>
                            <td><?php echo $row['ReferenceNumber']; ?></td>
                            <td><?php echo $row['PaymentStatus']; ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="7">No unpaid bills found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
