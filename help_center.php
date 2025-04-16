<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-6 text-orange-600">Help Center</h1>
        <p class="text-gray-600 text-center mb-8">Find answers to frequently asked questions and get assistance with your shopping experience.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="place_order.php" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-shopping-cart text-orange-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">How to Place an Order</h3>
                <p class="text-gray-500 text-sm">Learn how to place an order step by step.</p>
            </a>
            <a href="payment_options.php" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-credit-card text-orange-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">Payment Options</h3>
                <p class="text-gray-500 text-sm">Explore the available payment methods.</p>
            </a>
            <a href="track_order.php" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-truck text-orange-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">Track Your Order</h3>
                <p class="text-gray-500 text-sm">Track the status of your order in real-time.</p>
            </a>
            <a href="returns_refunds.php" class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-undo text-orange-500 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">Returns & Refunds</h3>
                <p class="text-gray-500 text-sm">Understand our return and refund policies.</p>
            </a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
