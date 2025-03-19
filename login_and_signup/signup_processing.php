<?php
session_start();
require_once '../db.php';

// Clear any existing error
unset($_SESSION['signup_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confPassword = $_POST['conf_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Basic validation
    if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($confPassword)) {
        $_SESSION['signup_error'] = 'Please fill in all fields.';
        header('Location: signup.php');
        exit;
    }
    
    // Check if email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = 'Please enter a valid email address.';
        header('Location: signup.php');
        exit;
    }
    
    // Validate phone number (simple validation)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) < 10) {
        $_SESSION['signup_error'] = 'Please enter a valid phone number.';
        header('Location: signup.php');
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confPassword) {
        $_SESSION['signup_error'] = 'Passwords do not match.';
        header('Location: signup.php');
        exit;
    }
    
    // Check password strength
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $_SESSION['signup_error'] = 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number.';
        header('Location: signup.php');
        exit;
    }
    
    // Check terms agreement
    if (!$terms) {
        $_SESSION['signup_error'] = 'You must agree to the Terms and Conditions.';
        header('Location: signup.php');
        exit;
    }
    
    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            if ($existingUser['username'] == $username) {
                // Suggest available usernames
                $suggestedUsernames = [];
                for ($i = 1; $i <= 3; $i++) {
                    $suggestedUsernames[] = $username . $i;
                }
                $_SESSION['signup_error'] = "Username already exists. Try: " . implode(", ", $suggestedUsernames);
            } else {
                $_SESSION['signup_error'] = "Email already exists.";
            }
            header('Location: signup.php');
            exit;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $phone, $hashedPassword]);
        
        // Get the new user's ID
        $userId = $conn->lastInsertId();
        
        // Log the user in
        $_SESSION['user_id'] = $userId;
        
        // Redirect to the home page
        header('Location: ../index.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['signup_error'] = 'Registration failed. Please try again later.';
        error_log('Registration error: ' . $e->getMessage());
        header('Location: signup.php');
        exit;
    }
} else {
    // If not POST request, redirect to signup page
    header('Location: signup.php');
    exit;
}
