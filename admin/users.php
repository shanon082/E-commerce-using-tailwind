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

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the query
$params = [];
$whereClause = '';

if (!empty($search)) {
    $whereClause = "WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Get total users count for pagination
$countQuery = "SELECT COUNT(*) FROM users $whereClause";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();

$totalPages = ceil($totalUsers / $perPage);

// Get users with pagination
$query = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$allParams = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($query);
$stmt->execute($allParams);
$users = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Users</h1>
    </div>
    
    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="users.php" method="get" class="flex gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search by username, email, or phone..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div class="self-end">
                <button type="submit" class="bg-jumia-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                
                <?php if (!empty($search)): ?>
                    <a href="users.php" class="ml-2 text-gray-600 hover:text-gray-800">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($users) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Username
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Phone
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Registered Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $user['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($user['phone']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y H:i', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="user_details.php?id=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="user_orders.php?user_id=<?= $user['id'] ?>" class="text-jumia-orange hover:text-orange-700">
                                        <i class="fas fa-shopping-cart"></i> Orders
                                    </a>
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
                                <span class="font-medium"><?= min($offset + $perPage, $totalUsers) ?></span>
                                of 
                                <span class="font-medium"><?= $totalUsers ?></span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                                       class="px-3 py-1 rounded-md border <?= $i == $page ? 'bg-jumia-orange text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" 
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
                    <i class="fas fa-users text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No users found</h3>
                <p class="text-gray-500 mb-4">
                    <?php if (!empty($search)): ?>
                        Try adjusting your search criteria.
                    <?php else: ?>
                        No users have registered yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search)): ?>
                    <a href="users.php" class="inline-block bg-jumia-blue text-white px-4 py-2 rounded-md font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-times mr-1"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
