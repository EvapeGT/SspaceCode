<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is logged in as an admin
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
    header('Location: ../signin.html');
    exit();
}

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

// Check for database connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST["username"]; // Make sure to have a 'username' field in your form
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $contact = $_POST["contact"];
    $email = $_POST["email"];
    $room = $_POST["room"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Error: Passwords do not match.";
        exit();
    }

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into Login table
        $loginStmt = $conn->prepare("INSERT INTO Login (Username, PasswordHash) VALUES (?, ?)");
        if ($loginStmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $loginStmt->bind_param("ss", $username, $hashed_password);
        $loginStmt->execute();
        $loginStmt->close();

        // Get the last insert ID
        $loginID = $conn->insert_id;

        // Insert into Tenants table
        $tenantStmt = $conn->prepare("INSERT INTO Tenants (LoginID, FirstName, LastName, ContactNumber, Email, room_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($tenantStmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $tenantStmt->bind_param("issssi", $loginID, $firstname, $lastname, $contact, $email, $room);
        $tenantStmt->execute();
        $tenantStmt->close();

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = 'User successfully registered.';
        header('Location: SignUpForm.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Close connection
$conn->close();
?>
