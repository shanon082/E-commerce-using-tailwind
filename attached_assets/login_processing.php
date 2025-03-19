<?php
session_start();
unset($_SESSION["error"]);
require_once ("../db.php");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql = "SELECT * FROM userdetails WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt -> bindParam(":email", $email);

    $stmt -> execute();
    $user = $stmt -> fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        header("Location: ../index.php");
    } else {
        $_SESSION["error"] = "Incorrect email or password";
        header("Location: login.php");
    }
    exit;
}
?>