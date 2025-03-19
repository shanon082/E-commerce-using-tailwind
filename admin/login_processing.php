<?php
session_start();
require_once '../db.php';

// Clear any existing error
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit;
    }
    
    try {
        // Check if admin user exists and password is correct
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $adminUser = $stmt->fetch();
        
        if ($adminUser && password_verify($password, $adminUser['password'])) {
            // Password is correct, create session
            $_SESSION['admin_id'] = $adminUser['id'];
            $_SESSION['admin_username'] = $adminUser['username'];
            
            // Redirect to admin dashboard
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Login failed. Please try again later.';
        error_log('Admin login error: ' . $e->getMessage());
        header('Location: login.php');
        exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: login.php');
    exit;
}
