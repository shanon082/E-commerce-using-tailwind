<?php
session_start();

// Clear the remember token cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Remove the token from the database
    require_once "../db.php";
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
    } catch (PDOException $e) {
        // Continue with logout even if token removal fails
    }
    
    // Expire the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit;
