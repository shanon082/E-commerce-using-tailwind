<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the query
$params = [];
$whereClause = [];

if (!empty($search)) {
    $whereClause[] = "(o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $whereClause[] = "o.order_status = ?";
    $params[] = $status;
}

if (!empty($dateFrom)) {
    $whereClause[] = "o.created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
}

if (!empty($dateTo)) {
    $whereClause[] = "o.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
}

$whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';

// Get total orders count for pagination
$countQuery = "SELECT COUNT(*) FROM orders o 
               JOIN users u ON o.user_id = u.id 
               $whereStr";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalOrders = $stmt->fetchColumn();

$totalPages = ceil($totalOrders / $perPage);

// Get orders with pagination
$query = "SELECT o.*, u.username as customer_name, u.email as customer_email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          $whereStr 
          ORDER BY o.created_at DESC 
          LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($query);
$stmt->execute($allParams);
$orders = $stmt->fetchAll();

// Handle update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    $validStatuses = ['processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($newStatus, $validStatuses)) {
        try {
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            
            $_SESSION['success_message'] = "Order #$orderId status updated to " . ucfirst($newStatus);
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Failed to update order status. " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Invalid order status.";
    }
    
    // Redirect to refresh the page and prevent form resubmission
    header("Location: orders.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Orders</h1>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="orders.php" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Order ID, customer..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">All Statuses</option>
                    <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="bg-jumia-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                
                <?php if (!empty($search) || !empty($status) || !empty($dateFrom) || !empty($dateTo)): ?>
                    <a href="orders.php" class="ml-2 text-gray-600 hover:text-gray-800 flex items-center">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($orders) > 0): ?>
            <div class="overflow-x-auto">
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
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Payment Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="text-jumia-orange hover:underline">
                                        #<?= $order['id'] ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($order['customer_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($order['customer_email']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y H:i', strtotime($order['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= formatPrice($order['total_amount']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $paymentStatusClass = '';
                                    switch ($order['payment_status']) {
                                        case 'completed':
                                            $paymentStatusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'pending':
                                            $paymentStatusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'failed':
                                            $paymentStatusClass = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $paymentStatusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $paymentStatusClass ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $orderStatusClass = '';
                                    switch ($order['order_status']) {
                                        case 'processing':
                                            $orderStatusClass = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'shipped':
                                            $orderStatusClass = 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'delivered':
                                            $orderStatusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            $orderStatusClass = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $orderStatusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $orderStatusClass ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="text-jumia-orange hover:text-orange-700" 
                                            onclick="document.getElementById('update-status-modal-<?= $order['id'] ?>').classList.remove('hidden')">
                                        <i class="fas fa-edit"></i> Status
                                    </button>
                                    
                                    <!-- Update Status Modal -->
                                    <div id="update-status-modal-<?= $order['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
                                        <div class="bg-white rounded-lg max-w-md w-full">
                                            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                                <h3 class="text-lg font-semibold text-gray-800">Update Order Status</h3>
                                                <button type="button" class="text-gray-400 hover:text-gray-600" 
                                                        onclick="document.getElementById('update-status-modal-<?= $order['id'] ?>').classList.add('hidden')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <form action="orders.php" method="post" class="p-4">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <div class="mb-4">
                                                    <label for="new-status-<?= $order['id'] ?>" class="block text-sm font-medium text-gray-700 mb-2">
                                                        New Status for Order #<?= $order['id'] ?>
                                                    </label>
                                                    <select id="new-status-<?= $order['id'] ?>" name="new_status" 
                                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                                <div class="flex justify-end">
                                                    <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md mr-2" 
                                                            onclick="document.getElementById('update-status-modal-<?= $order['id'] ?>').classList.add('hidden')">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" name="update_status" class="bg-jumia-orange text-white px-4 py-2 rounded-md">
                                                        Update Status
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing 
                                <span class="font-medium"><?= $offset + 1 ?></span>
                                to 
                                <span class="font-medium"><?= min($offset + $perPage, $totalOrders) ?></span>
                                of 
                                <span class="font-medium"><?= $totalOrders ?></span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                       class="px-3 py-1 rounded-md border <?= $i == $page ? 'bg-jumia-orange text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="p-6 text-center">
                <div class="text-gray-500 mb-4">
                    <i class="fas fa-shopping-cart text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No orders found</h3>
                <p class="text-gray-500 mb-4">
                    <?php if (!empty($search) || !empty($status) || !empty($dateFrom) || !empty($dateTo)): ?>
                        Try adjusting your search or filter criteria.
                    <?php else: ?>
                        No orders have been placed yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || !empty($status) || !empty($dateFrom) || !empty($dateTo)): ?>
                    <a href="orders.php" class="inline-block bg-jumia-blue text-white px-4 py-2 rounded-md font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-times mr-1"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
