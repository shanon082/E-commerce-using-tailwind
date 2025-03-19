<?php
session_start();
require_once "../db.php";

// Initialize error message
$error = null;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $redirect = isset($_POST["redirect"]) ? $_POST["redirect"] : "../index.php";
    
    // Validate inputs
    if (empty($email)) {
        $error = "Email is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } else {
        try {
            // Check if user exists and password matches
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Check if remember me was checked
                if (isset($_POST['remember_me'])) {
                    // Generate a secure token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    // Store the token in the database
                    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (:user_id, :token, :expires)");
                    $stmt->bindParam(':user_id', $user['id']);
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':expires', $expires);
                    $stmt->execute();
                    
                    // Set cookie (30 days expiration)
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
                }
                
                // Redirect to the appropriate page
                if ($user['is_admin']) {
                    header("Location: ../admin/index.php");
                } else {
                    header("Location: ../" . $redirect);
                }
                exit;
            } else {
                $error = "Incorrect email or password";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again later.";
        }
    }
    
    // If we got here, there was an error
    $_SESSION['error'] = $error;
    header("Location: login.php");
    exit;
}

// If accessed directly without POST data, redirect to login page
header("Location: login.php");
exit;
