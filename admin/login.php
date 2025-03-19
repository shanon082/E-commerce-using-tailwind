<?php
session_start();

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Check for error messages from login processing
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Jumia Clone</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="https://www.jumia.com/favicon.ico" type="image/x-icon">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">
                <span class="text-jumia-orange">JUMIA</span>
                <span class="text-jumia-blue">ADMIN</span>
            </h1>
            <p class="text-gray-600 mt-2">Administration Panel</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Admin Login</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="login_processing.php" method="post">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="username" name="username" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                               placeholder="Enter your username" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-jumia-blue text-white px-4 py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Admin Panel
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="../index.php" class="text-jumia-orange hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
