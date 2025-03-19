<?php
session_start();
require_once '../db.php';

// Clear any existing error
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectUrl = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '../index.php';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit;
    }
    
    // Check if email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'Please enter a valid email address.';
        header('Location: login.php');
        exit;
    }
    
    try {
        // Check if user exists and password is correct
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            
            // Remember me functionality (optional)
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                // Set a cookie that expires in 30 days
                setcookie('remember_user', $user['id'], time() + (86400 * 30), '/', '', false, true);
            }
            
            // Redirect to the specified URL or default to home page
            unset($_SESSION['redirect_after_login']); // Clear the redirect URL from session
            header("Location: $redirectUrl");
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Login failed. Please try again later.';
        error_log('Login error: ' . $e->getMessage());
        header('Location: login.php');
        exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: login.php');
    exit;
}
