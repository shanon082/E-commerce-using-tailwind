<?php
session_start();

// Unset all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to the admin login page
header("Location: login.php");
exit;
