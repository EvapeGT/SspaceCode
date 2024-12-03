<?php

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "s_space_tenant_portal";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include Composer's autoload file
require 'vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if the email exists in the tenants table
    $sql = "SELECT t.Email, l.LoginID, l.resetToken, l.tokenExpiry
            FROM tenants t
            JOIN login l ON t.LoginID = l.LoginID
            WHERE t.Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Generate a secure token if the user doesn't have one or if the existing token has expired
        if (empty($row["resetToken"]) || $row["tokenExpiry"] < date("Y-m-d H:i:s")) {
            $token = bin2hex(random_bytes(32));
            $expiry_time = date("Y-m-d H:i:s", strtotime("+24 hours"));

            // Update the user's record with the token and expiry time
            $sql = "UPDATE login SET resetToken = '$token', tokenExpiry = '$expiry_time' WHERE LoginID = '{$row["LoginID"]}'";
            $conn->query($sql);
        } else {
            $token = $row["resetToken"];
        }


        // Send the reset email with the token
        $reset_link = "http://localhost/SspaceCode/S-Space/reset-password.php?token=" . $token;
        $to = $email;
        $subject = "Reset your password";

        // Initialize the PHPMailer object
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rhusseldry@gmail.com'; // Replace with your SMTP username
            $mail->Password = 'wmqj mhyw razs bywg'; // Replace with your SMTP password or App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('rhusselbalajadia@gmail.com', 'S-Space Dormitels');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body>
<p>Hey there!</p>
<p>We noticed you're having trouble logging into your S-Space Dormitel account. No need to worry, it happens to the best of us!</p>
<p>Just click on the stellar button below and you'll be zooming back to your account in no time:</p>
<table cellspacing="0" cellpadding="0"> <tr>
    <td align="center" width="300" height="40" bgcolor="#0D47A1" style="-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block;">
        <a href="$reset_link" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; line-height:40px; width:100%; display:inline-block">
        Click to Reset Password
        </a>
    </td>
</tr> </table>
<p>If you didn't request a password reset, please ignore this email or <a href="support@yourdomain.com">contact support</a> if you have any questions.</p>
<p>Thanks,</p>
<p>The S-Space Dormitels Team</p></body>
</html>
EOT;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'A password reset link has been sent to your email address.';
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

$conn->close();