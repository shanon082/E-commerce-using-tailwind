<?php
session_start();
require_once 'db.php'; // Ensure this file contains your database connection logic

if (!isset($_SESSION['username'])) {
    header('Location: login_and_signup/login.php');
    exit();
}

$username = $_SESSION['username'];

// Fetch user_id from the users table
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bindValue(1, $username, PDO::PARAM_STR);
$userQuery->execute();
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

$userId = $user['id'];

// Fetch recent orders with product details
$query = $conn->prepare("
    SELECT 
        o.id AS order_id, 
        o.created_at, 
        o.total_amount, 
        o.payment_status, 
        o.order_status, 
        oi.quantity, 
        p.name AS product_name, 
        p.image_url AS product_image 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
$query->bindValue(1, $userId, PDO::PARAM_INT);
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
</head>
<body>
    <header>
        <!-- Include your header.php file -->
        <?php include 'header.php'; ?>
    </header>
    <main class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">My Recent Orders</h1>
        <?php if (count($result) > 0): ?>
            <div class="overflow-x-auto">
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-2">Order ID</th>
                            <th class="border border-gray-300 px-4 py-2">Date</th>
                            <th class="border border-gray-300 px-4 py-2">Product</th>
                            <th class="border border-gray-300 px-4 py-2">Quantity</th>
                            <th class="border border-gray-300 px-4 py-2">Total</th>
                            <th class="border border-gray-300 px-4 py-2">Payment</th>
                            <th class="border border-gray-300 px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $order): ?>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <div class="flex items-center">
                                        <img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="Product Image" class="w-12 h-12 object-cover mr-2">
                                        <span><?php echo htmlspecialchars($order['product_name']); ?></span>
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['total_amount']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['payment_status']); ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['order_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">You have no recent orders.</p>
        <?php endif; ?>
    </main>
    <footer>
        <!-- Include your footer.php file -->
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>
