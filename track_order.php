<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-6 text-orange-600">Track Your Order</h1>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 mb-4">Enter your order ID to track your order:</p>
            <form action="track_order_results.php" method="GET" class="flex">
                <input 
                    type="text" 
                    name="order_id" 
                    placeholder="Enter Order ID" 
                    class="w-full border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-md transition">
                    Track
                </button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
