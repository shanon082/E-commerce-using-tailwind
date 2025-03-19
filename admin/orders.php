<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login_and_signup/login.php');
    exit;
}

// Handle order status updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    
    try {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET order_status = :order_status, payment_status = :payment_status WHERE id = :id");
        $stmt->bindParam(':order_status', $order_status);
        $stmt->bindParam(':payment_status', $payment_status);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        
        // Set success message
        $_SESSION['success'] = "Order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . " status updated successfully.";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating order status: " . $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Filtering and Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

$params = [];
$where = [];

// Build the WHERE clause based on filters
if (!empty($status_filter)) {
    $where[] = "o.order_status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($payment_filter)) {
    $where[] = "o.payment_status = :payment";
    $params[':payment'] = $payment_filter;
}

if (!empty($search)) {
    $where[] = "(o.id LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($date_start)) {
    $where[] = "o.created_at >= :date_start";
    $params[':date_start'] = $date_start . " 00:00:00";
}

if (!empty($date_end)) {
    $where[] = "o.created_at <= :date_end";
    $params[':date_end'] = $date_end . " 23:59:59";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total number of orders
$countQuery = "SELECT COUNT(*) FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              $whereClause";
$stmt = $conn->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalOrders = $stmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Get orders with user information
$query = "SELECT o.*, u.username, u.email, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          $whereClause 
          GROUP BY o.id 
          ORDER BY o.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Manage Orders</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
                <form action="orders.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            value="<?php echo htmlspecialchars($search); ?>" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Order ID, customer..."
                        >
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="payment" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                        <select 
                            id="payment" 
                            name="payment" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Payment Statuses</option>
                            <option value="pending" <?php echo $payment_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $payment_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="refunded" <?php echo $payment_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="date_start" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input 
                            type="date" 
                            id="date_start" 
                            name="date_start" 
                            value="<?php echo htmlspecialchars($date_start); ?>" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <div>
                        <label for="date_end" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input 
                            type="date" 
                            id="date_end" 
                            name="date_end" 
                            value="<?php echo htmlspecialchars($date_end); ?>" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <div class="lg:col-span-5 flex justify-end">
                        <a href="orders.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md mr-2">
                            Reset
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                            <i class="fas fa-filter mr-2"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Orders Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-shipping-fast text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Processing Orders</p>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'");
                            $processingCount = $stmt->fetchColumn();
                            ?>
                            <p class="text-xl font-bold"><?php echo $processingCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <i class="fas fa-money-bill-wave text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Revenue</p>
                            <?php
                            $stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'");
                            $totalRevenue = $stmt->fetchColumn() ?: 0;
                            ?>
                            <p class="text-xl font-bold">$<?php echo number_format($totalRevenue, 2); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Orders This Month</p>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')");
                            $monthlyOrders = $stmt->fetchColumn();
                            ?>
                            <p class="text-xl font-bold"><?php echo $monthlyOrders; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($order['user_id']): ?>
                                                <?php echo htmlspecialchars($order['username'] ?? $order['email']); ?>
                                            <?php else: ?>
                                                Guest
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $paymentColor = '';
                                            switch ($order['payment_status']) {
                                                case 'pending':
                                                    $paymentColor = 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'paid':
                                                    $paymentColor = 'bg-green-100 text-green-800';
                                                    break;
                                                case 'refunded':
                                                    $paymentColor = 'bg-gray-100 text-gray-800';
                                                    break;
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $paymentColor; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <button 
                                                    type="button" 
                                                    class="text-gray-600 hover:text-gray-900" 
                                                    onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['order_status']; ?>', '<?php echo $order['payment_status']; ?>')"
                                                >
                                                    <i class="fas fa-edit"></i> Update
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No orders found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-6">
                    <nav class="inline-flex rounded-md shadow">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&search=<?php echo urlencode($search); ?>&date_start=<?php echo $date_start; ?>&date_end=<?php echo $date_end; ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 rounded-l-md cursor-not-allowed">
                                Previous
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-100 border border-gray-300">
                                    <?php echo $i; ?>
                                </span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&search=<?php echo urlencode($search); ?>&date_start=<?php echo $date_start; ?>&date_end=<?php echo $date_end; ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&search=<?php echo urlencode($search); ?>&date_start=<?php echo $date_start; ?>&date_end=<?php echo $date_end; ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 rounded-r-md cursor-not-allowed">
                                Next
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Update Order Status</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeStatusModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="orders.php" method="post">
                <input type="hidden" id="order_id" name="order_id" value="">
                
                <div class="mb-4">
                    <label for="order_status" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                    <select 
                        id="order_status" 
                        name="order_status" 
                        class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select 
                        id="payment_status" 
                        name="payment_status" 
                        class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md mr-2" onclick="closeStatusModal()">
                        Cancel
                    </button>
                    <button type="submit" name="update_status" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStatusModal(orderId, orderStatus, paymentStatus) {
            document.getElementById('order_id').value = orderId;
            document.getElementById('order_status').value = orderStatus;
            document.getElementById('payment_status').value = paymentStatus;
            document.getElementById('status-modal').classList.remove('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('status-modal').classList.add('hidden');
        }
    </script>
</body>
</html>
