<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login_and_signup/login.php?redirect=myAccount.php');
    exit;
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch();

// Get user addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$addresses = $stmt->fetchAll();

// Get recent orders
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.user_id = :user_id 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC 
                       LIMIT 5");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">My Account</h1>
        
        <!-- Account Navigation -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="flex flex-wrap border-b border-gray-200">
                <a href="#" class="text-blue-500 border-b-2 border-blue-500 py-3 px-6 font-medium">Account Overview</a>
                <a href="orders.php" class="text-gray-500 hover:text-blue-500 py-3 px-6 font-medium">Orders</a>
                <a href="wishlist.php" class="text-gray-500 hover:text-blue-500 py-3 px-6 font-medium">Wishlist</a>
                <a href="edit_profile.php" class="text-gray-500 hover:text-blue-500 py-3 px-6 font-medium">Edit Profile</a>
            </div>
        </div>
        
        <!-- Account Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Account Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Account Information</h2>
                    <a href="edit_profile.php" class="text-blue-500 hover:underline text-sm">Edit</a>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 mb-1">Full Name</h3>
                        <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 mb-1">Email Address</h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 mb-1">Phone Number</h3>
                        <p><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm text-gray-500 mb-1">Password</h3>
                        <p>••••••••</p>
                    </div>
                </div>
            </div>
            
            <!-- Address Book -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Address Book</h2>
                    <a href="edit_address.php" class="text-blue-500 hover:underline text-sm">Add New Address</a>
                </div>
                <div class="p-6">
                    <?php if (count($addresses) > 0): ?>
                        <?php foreach ($addresses as $index => $address): ?>
                            <div class="mb-4 pb-4 <?php echo $index !== array_key_last($addresses) ? 'border-b border-gray-200' : ''; ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium">
                                        <?php echo $address['is_default'] ? 'Default Address' : 'Saved Address'; ?>
                                    </h3>
                                    <div>
                                        <a href="edit_address.php?id=<?php echo $address['id']; ?>" class="text-blue-500 hover:underline text-sm mr-2">Edit</a>
                                        <?php if (!$address['is_default']): ?>
                                            <a href="delete_address.php?id=<?php echo $address['id']; ?>" class="text-red-500 hover:underline text-sm" onclick="return confirm('Are you sure you want to delete this address?')">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p><?php echo htmlspecialchars($address['address']); ?></p>
                                <p>
                                    <?php echo htmlspecialchars($address['city'] . ', ' . $address['region'] . 
                                    ($address['postal_code'] ? ' ' . $address['postal_code'] : '') . ', ' . $address['country']); ?>
                                </p>
                                <p>Phone: <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-gray-600 mb-4">You don't have any saved addresses.</p>
                            <a href="edit_address.php" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block transition">
                                Add New Address
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden md:col-span-2">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Recent Orders</h2>
                    <a href="orders.php" class="text-blue-500 hover:underline text-sm">View All Orders</a>
                </div>
                <div class="overflow-x-auto">
                    <?php if (count($orders) > 0): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $order['item_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            $<?php echo number_format($order['total_amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColor = '';
                                            switch ($order['order_status']) {
                                                case 'processing':
                                                    $statusColor = 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'shipped':
                                                    $statusColor = 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'delivered':
                                                    $statusColor = 'bg-green-100 text-green-800';
                                                    break;
                                                case 'cancelled':
                                                    $statusColor = 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusColor; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-500">
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="hover:underline">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-600 mb-4">You haven't placed any orders yet.</p>
                            <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block transition">
                                Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Newsletter Preferences -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Newsletter Preferences</h2>
                    <a href="newsletter_pref.php" class="text-blue-500 hover:underline text-sm">Edit</a>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">Manage your email communications to stay updated with the latest news and offers.</p>
                    <?php if (isset($user['newsletter']) && $user['newsletter']): ?>
                        <div class="mt-4 flex items-center text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>You are currently subscribed to our newsletter</span>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 flex items-center text-gray-600">
                            <i class="fas fa-times-circle mr-2"></i>
                            <span>You are not subscribed to our newsletter</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Store Credit -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium">Store Credit</h2>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 mb-1">Current Balance</h3>
                        <p class="text-xl font-bold">$0.00</p>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Store credit is a payment method that can be used for future purchases. Store credit is issued when you return an item and can be used to purchase anything on the site.
                    </p>
                    <a href="#" class="text-blue-500 hover:underline text-sm mt-2 inline-block">Learn more</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
