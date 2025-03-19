<?php
session_start();
require_once "../db.php";

// Initialize error message
$error = null;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST["username"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : null;
    $password = $_POST["password"];
    $conf_password = $_POST["conf_password"];
    
    // Validate inputs
    if (empty($username)) {
        $error = "Username is required";
    } elseif (empty($email)) {
        $error = "Email is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } elseif ($password !== $conf_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                if ($existingUser['username'] === $username) {
                    // Suggest available usernames
                    $suggestedUsernames = [];
                    for ($i = 1; $i <= 3; $i++) {
                        $suggestedUsernames[] = $username . $i;
                    }
                    $error = "Username already exists. Try: " . implode(", ", $suggestedUsernames);
                } else {
                    $error = "Email already exists. Please use a different email or try to login.";
                }
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Create new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone) 
                                       VALUES (:username, :email, :password, :first_name, :last_name, :phone)");
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':phone', $phone);
                
                $stmt->execute();
                
                // Redirect to login page with success message
                $_SESSION['success'] = "Account created successfully. You can now log in.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Registration failed. Please try again later.";
        }
    }
    
    // If we got here, there was an error
    $_SESSION['error'] = $error;
    header("Location: signup.php");
    exit;
}

// If accessed directly without POST data, redirect to signup page
header("Location: signup.php");
exit;
