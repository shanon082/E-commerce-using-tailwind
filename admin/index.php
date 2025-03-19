<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login_and_signup/login.php');
    exit;
}

// Get dashboard statistics
$stats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'pending_orders' => 0,
    'low_stock' => 0,
    'total_products' => 0,
    'total_users' => 0
];

try {
    // Total Orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    // Total Revenue
    $stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Pending Orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'");
    $stats['pending_orders'] = $stmt->fetchColumn();
    
    // Low Stock Products
    $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE stock < 5 AND stock > 0");
    $stats['low_stock'] = $stmt->fetchColumn();
    
    // Total Products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = $stmt->fetchColumn();
    
    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = 0");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Recent Orders (last 5)
    $stmt = $conn->query("SELECT o.*, u.username, u.email FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    
    // Top Selling Products
    $stmt = $conn->query("SELECT p.*, SUM(oi.quantity) as total_sold 
                         FROM products p 
                         JOIN order_items oi ON p.id = oi.product_id 
                         GROUP BY p.id 
                         ORDER BY total_sold DESC 
                         LIMIT 5");
    $topProducts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Orders Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Orders</p>
                            <p class="text-2xl font-bold"><?php echo number_format($stats['total_orders']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-blue-500 hover:underline text-sm">View all orders</a>
                    </div>
                </div>
                
                <!-- Revenue Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Revenue</p>
                            <p class="text-2xl font-bold">$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php" class="text-green-500 hover:underline text-sm">View sales reports</a>
                    </div>
                </div>
                
                <!-- Pending Orders Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Pending Orders</p>
                            <p class="text-2xl font-bold"><?php echo number_format($stats['pending_orders']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="orders.php?status=processing" class="text-yellow-500 hover:underline text-sm">Process pending orders</a>
                    </div>
                </div>
                
                <!-- Low Stock Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Low Stock Items</p>
                            <p class="text-2xl font-bold"><?php echo number_format($stats['low_stock']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="products.php?filter=low_stock" class="text-red-500 hover:underline text-sm">View low stock items</a>
                    </div>
                </div>
                
                <!-- Products Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Products</p>
                            <p class="text-2xl font-bold"><?php echo number_format($stats['total_products']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="products.php" class="text-purple-500 hover:underline text-sm">Manage products</a>
                    </div>
                </div>
                
                <!-- Users Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Users</p>
                            <p class="text-2xl font-bold"><?php echo number_format($stats['total_users']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="users.php" class="text-orange-500 hover:underline text-sm">Manage users</a>
                    </div>
                </div>
            </div>
            
            <!-- Charts & Tables Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Sales Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-medium mb-4">Sales Overview</h2>
                    <div class="h-64">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>
                
                <!-- Top Products Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-medium mb-4">Top Selling Products</h2>
                    <div class="h-64">
                        <canvas id="products-chart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm mb-8">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium">Recent Orders</h2>
                    <a href="orders.php" class="text-blue-500 hover:underline text-sm">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (isset($recentOrders) && count($recentOrders) > 0): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($order['username'] ?? $order['email'] ?? 'Guest'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
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
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="hover:underline">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No recent orders found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sales Chart
        const salesCtx = document.getElementById('sales-chart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [1200, 1900, 1500, 2500, 2200, 3000],
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
        
        // Top Products Chart
        const productsCtx = document.getElementById('products-chart').getContext('2d');
        const productsChart = new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php if (isset($topProducts)): ?>
                        <?php foreach ($topProducts as $product): ?>
                            '<?php echo htmlspecialchars(substr($product['name'], 0, 15)) . (strlen($product['name']) > 15 ? '...' : ''); ?>',
                        <?php endforeach; ?>
                    <?php else: ?>
                        'No Data'
                    <?php endif; ?>
                ],
                datasets: [{
                    label: 'Units Sold',
                    data: [
                        <?php if (isset($topProducts)): ?>
                            <?php foreach ($topProducts as $product): ?>
                                <?php echo $product['total_sold']; ?>,
                            <?php endforeach; ?>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    ],
                    backgroundColor: 'rgba(245, 158, 11, 0.5)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
