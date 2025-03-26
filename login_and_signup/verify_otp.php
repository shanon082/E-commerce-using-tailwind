<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        header("Location: reset_passwordhtml.php");
    } else {
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid OTP - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <h2 class="text-3xl font-bold text-orange-500">TUKOLE <span class="text-blue-600 text-sm">business</span></h2>
            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">Invalid OTP</h3>
            <p class="mt-2 text-sm text-gray-600">The OTP you entered is incorrect. Please try again.</p>
            <a href="verify_otp.html" class="mt-6 inline-block px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                Try Again
            </a>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
?>
