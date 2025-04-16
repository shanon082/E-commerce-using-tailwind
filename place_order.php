<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place an Order - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-6 text-orange-600">How to Place an Order</h1>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 mb-4">Follow these steps to place an order:</p>
            <ol class="list-decimal pl-6 space-y-2 text-gray-700">
                <li>Browse products and add them to your cart.</li>
                <li>Go to your cart and review your selected items.</li>
                <li>Click "Proceed to Checkout" and fill in your shipping details.</li>
                <li>Choose your preferred payment method and complete the payment.</li>
                <li>Receive a confirmation email with your order details.</li>
            </ol>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
