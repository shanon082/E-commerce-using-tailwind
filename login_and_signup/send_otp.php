<?php
require_once '../db.php';

require 'PHPMailer/PHPMailer/PHPMailer.php';
require 'PHPMailer/PHPMailer/SMTP.php';
require 'PHPMailer/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $otp = rand(100000, 999999); 
        session_start();
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  
            $mail->SMTPAuth = true;
            $mail->Username = 'shanonsimon082@gmail.com';  
            $mail->Password = 'hrga hmmt yhkt wvhj';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('shanonsimon082@gmail.com', 'TUKOLE business recovery your password');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Recovery';
            $mail->Body = "Your OTP is: <strong>$otp</strong>";

            $mail->send();
            echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Sent - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <h2 class="text-3xl font-bold text-orange-500">TUKOLE <span class="text-blue-600 text-sm">business</span></h2>
            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">OTP Sent Successfully</h3>
            <p class="mt-2 text-sm text-gray-600">Please check your email for the OTP.</p>
            <a href="verify_otp.html" class="mt-6 inline-block px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                Verify OTP
            </a>
        </div>
    </div>
</body>
</html>
HTML;
        } catch (Exception $e) {
            echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <h2 class="text-3xl font-bold text-orange-500">TUKOLE <span class="text-blue-600 text-sm">business</span></h2>
            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">Error Sending OTP</h3>
            <p class="mt-2 text-sm text-gray-600">{$mail->ErrorInfo}</p>
            <a href="forgot_password.php" class="mt-6 inline-block px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                Try Again
            </a>
        </div>
    </div>
</body>
</html>
HTML;
        }
    } else {
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Not Found - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <h2 class="text-3xl font-bold text-orange-500">TUKOLE <span class="text-blue-600 text-sm">business</span></h2>
            <h3 class="mt-6 text-2xl font-extrabold text-gray-900">Email Not Found</h3>
            <p class="mt-2 text-sm text-gray-600">The email you entered is not registered.</p>
            <a href="forgot_password.php" class="mt-6 inline-block px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
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
