<?php
// Connect to the database
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get today's date
$currentDate = date('Y-m-d');

// Fetch tenants whose contract start date matches today's date
$fetchTenantsQuery = "SELECT t.TenantID, r.price
                      FROM tenants t
                      INNER JOIN contracts c ON t.TenantID = c.TenantID
                      INNER JOIN rooms r ON c.room_id = r.room_id
                      WHERE DATE(c.StartDate) = '$currentDate'";

$result = $conn->query($fetchTenantsQuery);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tenantID = $row['TenantID'];
        $roomPrice = $row['price'];

        // Insert a new record into the rent_payments table
        $insertQuery = "INSERT INTO rent_payments (TenantID, PaymentDate, PaymentAmount, PaymentMethod, PaymentStatus)
                        VALUES ($tenantID, '$currentDate', $roomPrice, 'Automatic', 'Unpaid')";

        if ($conn->query($insertQuery) === TRUE) {
            echo "Rent bill created for Tenant ID: $tenantID\n";
        } else {
            echo "Error creating rent bill for Tenant ID: $tenantID\n";
        }
    }
} else {
    echo "No tenants found with matching start date.\n";
}

$conn->close();
?>