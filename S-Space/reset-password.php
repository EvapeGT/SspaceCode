<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";

// Create a new mysqli object
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the reset token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Retrieve the user's information based on the token
    $sql = "SELECT l.LoginID, l.Username, t.Email FROM login l JOIN tenants t ON l.LoginID = t.LoginID WHERE l.resetToken = '$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $loginID = $row['LoginID'];
        $username = $row['Username'];
        $email = $row['Email'];
    } else {
        echo "Invalid or expired token.";
        exit();
    }
} else {
    echo "No token provided.";
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    // Validate and update the password
    if ($newPassword === $confirmPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $sql = "UPDATE login SET PasswordHash = '$passwordHash', resetToken = NULL, tokenExpiry = NULL WHERE LoginID = '$loginID'";
        if ($conn->query($sql) === TRUE) {
            echo "Password reset successful. You can now log in with your new password.";
        } else {
            echo "Error updating password: " . $conn->error;
        }
    } else {
        echo "New password and confirm password do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Space Dorm Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins&display=swap">
    <style>
         body {
            background-color: #ffffff;
            background-image: url('cafeteriareal.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            overflow: hidden;
        }

        .forgot-password-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 539px;
            background-color: rgba(255, 255, 255, 0.75);
            border-radius: 4vw;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            padding: 30px;
        }

        .forgot-password-box h2 {
            font-family: "Poppins", Helvetica;
            font-weight: 600;
            color: #d44d5d;
            margin-bottom: 30px;
        }

        .forgot-password-box label {
            font-family: "Poppins", Helvetica;
            font-weight: 500;
            color: #333;
        }

        .forgot-password-box input[type="email"] {
            font-family: "Poppins", Helvetica;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .forgot-password-box button[type="submit"] {
            font-family: "Poppins", Helvetica;
            background-color: #d44d5d;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .forgot-password-box button[type="submit"]:hover {
            background-color: green;
        }
    </style>
</head>
<body>
    <div class="forgot-password-box">
        <h2>Reset Password</h2>
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?token=' . $token; ?>" method="post">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>