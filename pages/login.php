<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();
redirectIfLoggedIn("../index.php");

$email = $password = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passedValidation = true;

    if (empty($email)) {
        $error = $error ."Email Address or Username is required. <br>";
        $passedValidation = false;
    }

    if (empty($password)) {
        $error = $error ."Password is required. ";
        $passedValidation = false;
    }

    if ($passedValidation) {
        $user = authenticateUsername($email, $password);
        if ($user != -1) {
            // This function will return the username, even if email was used for login.
            echo ("successful. now set cookie time, congrats :)");
            $_SESSION['user_id'] = $user;
            redirectIfLoggedIn("../index.php");
        }
        else {
            $error = $error . ("incorrect email/password.");
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Log In</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../css/login.css">
</head>
<body>
	<div class="header">
		<button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main_btn" src="../images/logo_white.png"></button>
	</div>
	<div class="login_box">
		<h1 class="info_title">LOG IN</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="email"> <!-- MAY BREAK CSS ID=EMAIL -->
                <input name="email" id="email" type="text" max="256" placeholder="Email or Username" value="<?php echo $email;?>" required>
            </div>
            <div class="password">
                <input name="password" id="password" type="password" max="128" placeholder="Password" required>
            </div>
            <div class="register">
                <p>First Time Here?</p>
                <a href="register.php">Register Now</a>
            </div>
            <div class="final">
                <input class="continue" id="post" type="submit" value="Login">
            </div>
            <?php
            if ($error){
                echo("<p>". $error ."</p>");
            }
            ?>
        </form>
	</div>
	<footer>
	    <a href="#privacypolicy">Privacy Policy</a>
	    <a href="#t&c">Terms & Conditions</a>
	    <a href="#contact">Contact Us</a>
	</footer>
</body>
</html>