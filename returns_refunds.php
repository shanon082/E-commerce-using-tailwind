<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns & Refunds - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-6 text-orange-600">Returns & Refunds</h1>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 mb-4">We offer a hassle-free return and refund policy:</p>
            <ul class="list-disc pl-6 space-y-2 text-gray-700">
                <li>Items can be returned within 30 days of delivery.</li>
                <li>Ensure the item is in its original condition and packaging.</li>
                <li>Contact our support team to initiate a return.</li>
                <li>Refunds will be processed within 7 business days after the return is received.</li>
            </ul>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
