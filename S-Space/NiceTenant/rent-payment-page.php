<?php
// PHP code (remains unchanged except for modal structure updates)
session_start();
if (!isset($_SESSION['isUserLoggedIn']) || $_SESSION['isUserLoggedIn'] !== true) {
    header('Location: ../signin.html');
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tenantID = $_SESSION['tenantTenantData'][0]['TenantID'];

$unpaidRentPaymentsQuery = "SELECT RentPaymentID AS PaymentID, PaymentDate, PaymentAmount, 'Unpaid' AS PaymentStatus, 'Rent Payment' AS BillType
FROM rent_payments
WHERE TenantID = $tenantID AND PaymentStatus = 'Unpaid'";
$unpaidRentPaymentsResult = $conn->query($unpaidRentPaymentsQuery);
$unpaidRentPaymentsData = $unpaidRentPaymentsResult->fetch_all(MYSQLI_ASSOC);

$paidRentPaymentsQuery = "SELECT RentPaymentID AS PaymentID, PaymentDate, PaymentAmount, PaymentStatus, 'Rent Payment' AS BillType
FROM rent_payments
WHERE TenantID = ? AND PaymentStatus = 'Paid'";
$stmt = $conn->prepare($paidRentPaymentsQuery);
$stmt->bind_param("i", $tenantID);
$stmt->execute();
$paidRentPaymentsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pendingRentPaymentsQuery = "SELECT RentPaymentID AS PaymentID, PaymentDate, PaymentAmount, PaymentStatus, PaymentMethod, 'Rent Payment' AS BillType
FROM rent_payments
WHERE TenantID = ? AND PaymentStatus = 'Pending'";
$stmt = $conn->prepare($pendingRentPaymentsQuery);
$stmt->bind_param("i", $tenantID);
$stmt->execute();
$pendingRentPaymentsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$allPaidBills = $paidRentPaymentsData;
usort($allPaidBills, function($a, $b) {
    return strtotime($a['PaymentDate']) - strtotime($b['PaymentDate']);
});

$allPendingPayments = $pendingRentPaymentsData;
usort($allPendingPayments, function($a, $b) {
    return strtotime($a['PaymentDate']) - strtotime($b['PaymentDate']);
});

$totalUnpaidAmount = array_sum(array_column($unpaidRentPaymentsData, 'PaymentAmount'));
$totalPendingAmount = array_sum(array_column($pendingRentPaymentsData, 'PaymentAmount'));
$totalPaidAmount = array_sum(array_column($paidRentPaymentsData, 'PaymentAmount'));

$upcomingUnpaidRentPaymentsQuery = "SELECT RentPaymentID, PaymentDate, PaymentAmount
FROM rent_payments
WHERE TenantID = $tenantID AND PaymentStatus = 'Unpaid' AND PaymentDate > CURRENT_DATE()";
$upcomingUnpaidRentPaymentsResult = $conn->query($upcomingUnpaidRentPaymentsQuery);
$upcomingUnpaidRentPaymentsData = $upcomingUnpaidRentPaymentsResult->fetch_all(MYSQLI_ASSOC);

$rentPaymentsQuery = "SELECT YEAR(PaymentDate) AS year, MONTH(PaymentDate) AS month, SUM(PaymentAmount) AS total_amount
FROM rent_payments
WHERE TenantID = $tenantID
GROUP BY year, month
ORDER BY year, month";
$rentPaymentsResult = $conn->query($rentPaymentsQuery);

$paymentDates = array();
$rentPaymentAmounts = array();

while ($row = $rentPaymentsResult->fetch_assoc()) {
    $paymentDate = $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT) . '-01';
    $paymentDates[] = $paymentDate;
    $rentPaymentAmounts[] = $row['total_amount'];
}

$paymentDatesJson = json_encode($paymentDates);
$rentPaymentAmountsJson = json_encode($rentPaymentAmounts);

include 'header.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Tenant Rent Payment Page</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Rent Payment Page</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="tenant_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Rent Payment Page</li>
            </ol>
        </nav>
    </div>
    <div class="container">
        <div class="row">
            <!-- Upcoming Payment Deadlines -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Payment Deadlines</h5>
                        <?php if (!empty($upcomingUnpaidRentPaymentsData)) { ?>
                            <div class="alert alert-warning" role="alert">
                                You have the following upcoming payment deadlines:
                                <ul class="mb-0">
                                    <?php foreach ($upcomingUnpaidRentPaymentsData as $upcomingPayment) { ?>
                                        <li>Rent Payment (₱<?php echo number_format($upcomingPayment['PaymentAmount'], 2); ?>) due on <?php echo date('M d, Y', strtotime($upcomingPayment['PaymentDate'])); ?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } else { ?>
                            <p class="text-muted">You have no upcoming payment deadlines.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Unpaid Rent Payments</h5>
                        <p class="lead mb-4">Total Unpaid Amount: <strong>₱<?php echo number_format($totalUnpaidAmount, 2); ?></strong></p>

                        <!-- Unpaid Rent Payments Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unpaidRentPaymentsData as $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment['BillType']; ?></td>
                                        <td><?php echo $payment['PaymentDate']; ?></td>
                                        <td>₱<?php echo number_format($payment['PaymentAmount'], 2); ?></td>
                                        <td><?php echo $payment['PaymentStatus']; ?></td>
                                        <td>
                                            <a href="#" class="btn btn-primary btn-sm pay-now-btn"
                                               data-payment-id="<?php echo $payment['PaymentID']; ?>"
                                               data-payment-amount="<?php echo $payment['PaymentAmount']; ?>">Pay Now</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Payments</h5>
                        <p class="lead mb-4">Total Pending Amount: <strong>₱<?php echo number_format($totalPendingAmount, 2); ?></strong></p>

                        <!-- Pending Payments Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPendingPayments as $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment['BillType']; ?></td>
                                        <td><?php echo $payment['PaymentDate']; ?></td>
                                        <td>₱<?php echo number_format($payment['PaymentAmount'], 2); ?></td>
                                        <td><?php echo $payment['PaymentStatus']; ?></td>
                                        <td><?php echo $payment['PaymentMethod']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Paid Payment History</h5>
                        <!-- Payment History Table -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Bill Type</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPaidBills as $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment['BillType']; ?></td>
                                        <td><?php echo $payment['PaymentDate']; ?></td>
                                        <td>₱<?php echo number_format($payment['PaymentAmount'], 2); ?></td>
                                        <td><?php echo $payment['PaymentStatus']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal for Payment Confirmation -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="billType" name="billType" value="Rent Payment">
                    <input type="hidden" id="billAmount" name="billAmount">
                    <input type="hidden" id="billID" name="billID">
                    <input type="hidden" id="tenantID" name="tenantID" value="<?php echo $tenantID; ?>">
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="paymentMethod">
                            <option value="Credit Card">Credit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Gcash">Gcash</option>
                            <option value="Paymaya">Paymaya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="referenceNumber" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="referenceNumber" name="referenceNumber" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receiptImage" class="form-label">Receipt Image</label>
                        <input type="file" class="form-control" id="receiptImage" name="receiptImage" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentSuccessModalLabel">Payment Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fa fa-check-circle fa-3x text-success"></i>
                <p class="mt-3">Your payment has been submitted successfully!</p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/js/main.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const payNowButtons = document.querySelectorAll('.pay-now-btn');
        payNowButtons.forEach(button => {
            button.addEventListener('click', function () {
                const paymentID = button.getAttribute('data-payment-id');
                const paymentAmount = button.getAttribute('data-payment-amount');

                document.getElementById('billID').value = paymentID;
                document.getElementById('billAmount').value = paymentAmount;

                const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                paymentModal.show();
            });
        });

        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(paymentForm);

            fetch('rent_payment_processor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                    paymentModal.hide();

                    const successModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
                    successModal.show();
                } else {
                    alert('Payment submission failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting payment:', error);
                alert('Error submitting payment. Please try again.');
            });
        });
    });
</script>
</body>
</html>