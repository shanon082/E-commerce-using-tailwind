<?php
session_start();
require_once 'db.php';

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['id'];

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id");
$stmt->bindParam(':id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = :order_id");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Order Confirmation</h1>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Thank you for your order!</h2>
            <p class="text-gray-700 mb-4">Your order ID is <strong>#<?php echo htmlspecialchars($order['id']); ?></strong>.</p>
            <p class="text-gray-700 mb-4">Order Total: <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></p>

            <h3 class="text-lg font-medium mt-6 mb-4">Order Items</h3>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($order_items as $item): ?>
                    <li class="py-4 flex">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded">
                        <div class="ml-4">
                            <h4 class="text-sm font-medium"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="text-sm text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                            <p class="text-sm font-medium">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="mt-6 text-center">
            <form action="confirm_order.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Confirm Order
                </button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
