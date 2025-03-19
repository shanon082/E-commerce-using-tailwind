<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get admin data
$adminId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$adminId]);
$adminData = $stmt->fetch();

// Dashboard statistics
// Total orders
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetchColumn();

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$totalUsers = $stmt->fetchColumn();

// Total products
$stmt = $conn->prepare("SELECT COUNT(*) FROM products");
$stmt->execute();
$totalProducts = $stmt->fetchColumn();

// Revenue
$stmt = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'completed'");
$stmt->execute();
$totalRevenue = $stmt->fetchColumn();

// Recent orders
$stmt = $conn->prepare("SELECT o.*, u.username as customer_name 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC 
                      LIMIT 5");
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $conn->prepare("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Orders Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 text-sm">Total Orders</h3>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($totalOrders) ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="orders.php" class="text-blue-600 hover:underline text-sm">View all orders</a>
            </div>
        </div>
        
        <!-- Revenue Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 text-sm">Total Revenue</h3>
                    <p class="text-2xl font-bold text-gray-800"><?= formatPrice($totalRevenue ?: 0) ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="#" class="text-green-600 hover:underline text-sm">View report</a>
            </div>
        </div>
        
        <!-- Users Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 text-sm">Total Users</h3>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($totalUsers) ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="users.php" class="text-purple-600 hover:underline text-sm">View all users</a>
            </div>
        </div>
        
        <!-- Products Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-orange-100 p-3 mr-4">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 text-sm">Total Products</h3>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($totalProducts) ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="products.php" class="text-orange-600 hover:underline text-sm">View all products</a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h2 class="font-semibold text-lg text-gray-800">Recent Orders</h2>
                <a href="orders.php" class="text-jumia-orange hover:underline text-sm">View All</a>
            </div>
            <div class="overflow-x-auto">
                <?php if (count($recentOrders) > 0): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="order_details.php?id=<?= $order['id'] ?>" class="text-jumia-orange hover:underline">
                                            #<?= $order['id'] ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= htmlspecialchars($order['customer_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= formatPrice($order['total_amount']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusClass = '';
                                        switch ($order['order_status']) {
                                            case 'processing':
                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'shipped':
                                                $statusClass = 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'delivered':
                                                $statusClass = 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        No orders found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h2 class="font-semibold text-lg text-gray-800">Low Stock Products</h2>
                <a href="products.php" class="text-jumia-orange hover:underline text-sm">View All Products</a>
            </div>
            <div class="overflow-x-auto">
                <?php if (count($lowStockProducts) > 0): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md overflow-hidden">
                                                <img src="<?= htmlspecialchars($product['image']) ?>" 
                                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                                     class="h-full w-full object-contain">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($product['name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= formatPrice($product['price']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $product['stock'] === 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        No low stock products found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
