<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <a href="../index.php" class="inline-block">
                    <h2 class="text-3xl font-bold text-orange-500">TUKOLE <span class="text-blue-600 text-sm">business</span></h2>
                </a>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Reset Your Password</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Enter your new password below.
                </p>
            </div>
            <form class="mt-8 space-y-6" action="reset_password.php" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="password" class="sr-only">New Password</label>
                        <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" placeholder="New Password">
                    </div>
                    <div>
                        <label for="confirm_password" class="sr-only">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" placeholder="Confirm Password">
                    </div>
                </div>
                <?php if (isset($_SESSION["error"])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION["error"]; ?></span>
                    </div>
                    <?php unset($_SESSION["error"]); ?>
                <?php endif; ?>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Reset Password
                    </button>
                </div>
            </form>
            <div class="text-center mt-6">
                <a href="../index.php" class="text-sm text-gray-600 hover:text-orange-500">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>