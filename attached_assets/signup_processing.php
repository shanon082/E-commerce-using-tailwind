<?php
session_start();
unset($_SESSION["error"]);

require_once ("../db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $phone = $_POST["phone"];
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["conf_password"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "wrong email format";
        header("location:signup.php");
    } else {
        if ($password !== $confirmPassword) {
            $_SESSION["error"] = "password doesn't match";
            header("location:signup.php");
        } else {
            // Check if username or email already exists
            $sql = "SELECT * FROM userdetails WHERE username = :username OR email = :email";
            $stmt = $conn -> prepare($sql);
            $stmt -> bindParam(":username", $username);
            $stmt -> bindParam(":email", $email);
            $stmt -> execute();
            $existingUser = $stmt -> fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                if ($existingUser['username'] == $username) {
                    // Suggest available usernames
                    $suggestedUsernames = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $suggestedUsernames[] = $username . $i;
                    }
                    $_SESSION["error"] = "Username already exists.<br>Try: " . implode(", ", $suggestedUsernames);
                } else {
                    $_SESSION["error"] = "Email already exists.";
                }
                header("location:signup.php");
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO userdetails (username, phone, email, password) VALUES (:username, :phone, :email, :password);";
                $stmt = $conn -> prepare($sql);
                $stmt -> bindParam(":username", $username);
                $stmt -> bindParam(":phone", $phone);
                $stmt -> bindParam(":email", $email);
                $stmt -> bindParam(":password", $hashedPassword);

                $stmt -> execute();

                $stmt = null;
                $conn = null;

                header("location:login.php");
            }
        }
    }
    exit;
}
?>
