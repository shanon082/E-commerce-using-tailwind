<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="login&signup.css">
</head>
<body>
    <div class="wrapper">
        <form action="signup_processing.php" method="post">
            <h1>Welcome to ttukole business</h1>
            <h3>Type in the details to login or create your account</h3>
            <div class="username">
                <label for="username">Username:</label>
                <input type="text" placeholder="Username" name="username" required>
            </div>
            <div class="phone">
                <label for="phone">Phone number:</label>
                <input type="tel" placeholder="e.g. 07xxxxxxxxx" name="phone" required>
            </div>
            <div class="email">
                <label for="email">Email</label>
                <input type="email" placeholder="email" name="email" required>
            </div>
            <div class="password">
                <label for="password">Password</label>
                <input type="password" placeholder="password" name="password" required>
            </div>
            <div class="confirm_password">
                <label for="conpassword">Confirm Password</label>
                <input type="password" placeholder="Confirm password" name="conf_password" required>
            </div>
            <span style="color: red;"><?php
            if(isset($_SESSION["error"])){
                echo $_SESSION["error"];
                unset($_SESSION["error"]);
            }
            
            ?></span>
            <div class="links">
                <a href="login.php">Already have account!!</a>
            </div>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>