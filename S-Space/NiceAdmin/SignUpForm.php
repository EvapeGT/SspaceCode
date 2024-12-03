<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['isAdminLoggedIn']) || $_SESSION['isAdminLoggedIn'] !== true) {
  header('Location: ../index.html');
  exit();
}
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show custom-alert text-center" role="alert">
    <i class="bi bi-check-circle me-1"></i> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <style>
        .custom-alert {
    margin-top: 50px; /* Adjust this value as needed to create enough space */
    margin-bottom: 0px;
}
.text-center {
    text-align: center; /* This will center the text inside the div */
}
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign Up Form by Colorlib</title>

    <!-- Font Icon -->
    <link rel="stylesheet" href="assets/fonts/material-icon/css/material-design-iconic-font.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="assets/css/signUp.css">
</head>
<?php
    include 'header.inc.php';
    ?>
<body>
        <!-- Sign up form -->
        <section class="signup signup-container">
        <section class="signup" >
            <div class="container">
                <div class="signup-content">
                    <div class="signup-form">
                        <h2 class="form-title">Sign up a Tenant</h2>
                        <form method="POST" class="register-form" id="register-form" action="registerHandler.php" >
                            <div class="form-group">
                                <label for="firstname"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="firstname" id="firstname" placeholder="Your First Name" required />
                            </div>
                            <div class="form-group">
                                <label for="lastname"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="lastname" id="lastname" placeholder="Your Last Name" required />
                            </div>
                            <div class="form-group">
                                <label for="contact"><i class="zmdi zmdi-phone"></i></label>
                                <input type="text" name="contact" id="contact" placeholder="Your Contact Number" required />
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="zmdi zmdi-email"></i></label>
                                <input type="email" name="email" id="email" placeholder="Your Email" required />
                            </div>
                            <div class="form-group">
                                <label for="room"><i class="zmdi zmdi-home"></i></label>
                                <input type="text" name="room" id="room" placeholder="Your Room" required />
                            </div>
                            <div class="form-group">
        <label for="username"><i class="zmdi zmdi-account-circle"></i></label>
        <input type="text" name="username" id="username" placeholder="Username" required />
    </div>
    <div class="form-group">
    <label for="password"><i class="zmdi zmdi-lock"></i></label>
    <input type="password" name="password" id="password" placeholder="Password" pattern=".{12,}" title="Password must be at least 12 characters long" required />
</div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="zmdi zmdi-lock-outline"></i></label>
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="agree-term" id="agree-term" class="agree-term" required />
                                <label for="agree-term" class="label-agree-term"><span><span></span></span>I agree all statements in <a href="#" class="term-service">Terms of service</a></label>
                            </div>
                            <div class="form-group form-button">
                                <input type="submit" name="signup" id="signup" class="form-submit" value="Register"/>
                            </div>
                        </form>
                    </div>
                    <div class="signup-image">
                        <figure><img src="assets/img/signup-image.jpg" alt="sing up image"></figure>
                    </div>
                </div>
            </div>
        </section>
        </section>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
