<?php
session_start();

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Check if there's a redirect URL
$redirectUrl = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '../index.php';

// Check for error messages from login processing
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jumia Clone</title>
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
<body class="bg-gray-100 min-h-screen">
    <!-- Header with logo -->
    <header class="bg-white shadow-md py-4 mb-8">
        <div class="container mx-auto px-4">
            <a href="../index.php" class="flex items-center justify-center">
                <h1 class="text-2xl font-bold">
                    <span class="text-jumia-orange">JUMIA</span>
                    <span class="text-jumia-blue">CLONE</span>
                </h1>
            </a>
        </div>
    </header>

    <!-- Login Form -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome back</h2>
                <p class="text-gray-600 mb-6">Log back into your account</p>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="login_processing.php" method="post">
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirectUrl) ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                               placeholder="Enter your email" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" 
                                   class="h-4 w-4 text-jumia-orange focus:ring-jumia-orange border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="text-sm text-jumia-orange hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-jumia-orange text-white px-4 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        Login
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        New user? 
                        <a href="signup.php" class="text-jumia-orange hover:underline font-medium">
                            Create an account
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-600 text-sm">&copy; <?= date('Y') ?> Jumia Clone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
