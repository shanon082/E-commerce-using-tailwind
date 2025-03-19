<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login&signup.css">
</head>
<body>
    <div class="wrapper">
        <form action="login_processing.php" method="post">
            <h1>Welcome back </h1>
            <h3>Log back into your account</h3>
            <div class="username">
                <label for="email">Email</label>
                <input type="email" placeholder="Email" name="email" required>
            </div>
            <div class="password">
                <label for="password">Password</label>
                <input type="password" placeholder="Password" name="password" required>
            </div>
            <div class="links">
                <a href="signup.php">New User</a>
                <a href="">Forgot password</a>
            </div>
            <div class="error_msg">
                <?php 
                if(isset($_SESSION["error"])){
                    echo $_SESSION["error"];
                    unset($_SESSION["error"]);
                }
                ?>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>