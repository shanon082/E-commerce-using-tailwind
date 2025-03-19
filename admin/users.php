<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login_and_signup/login.php');
    exit;
}

// Process user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Don't allow deletion of the current admin user
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } else {
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Delete user's addresses
                $stmt = $conn->prepare("DELETE FROM addresses WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Update reviews to anonymous
                $stmt = $conn->prepare("UPDATE reviews SET user_id = NULL WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Update orders to maintain history but without user association
                $stmt = $conn->prepare("UPDATE orders SET user_id = NULL WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Delete user
                $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                
                $_SESSION['success'] = "User deleted successfully.";
            } else {
                $_SESSION['error'] = "User not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: users.php');
    exit;
}

// Process admin status toggle
if (isset($_GET['toggle_admin']) && is_numeric($_GET['toggle_admin'])) {
    $user_id = $_GET['toggle_admin'];
    
    // Don't allow changing admin status of the current admin user
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot change your own admin status.";
    } else {
        try {
            // Get current admin status
            $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                // Toggle admin status
                $new_status = $user['is_admin'] ? 0 : 1;
                
                $stmt = $conn->prepare("UPDATE users SET is_admin = :is_admin WHERE id = :id");
                $stmt->bindParam(':is_admin', $new_status);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                
                $_SESSION['success'] = "User admin status updated successfully.";
            } else {
                $_SESSION['error'] = "User not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating user: " . $e->getMessage();
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: users.php');
    exit;
}

// Handle filtering and pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$params = [];
$where = [];

// Build the WHERE clause based on filters
if ($filter === 'admin') {
    $where[] = "is_admin = 1";
} elseif ($filter === 'customer') {
    $where[] = "is_admin = 0";
}

if (!empty($search)) {
    $where[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Get total number of users
$countQuery = "SELECT COUNT(*) FROM users $whereClause";
$stmt = $conn->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Get users with order count
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count 
          FROM users u 
          $whereClause 
          ORDER BY u.id DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - TUKOLE Business</title>
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
            <h1 class="text-2xl font-bold mb-6">Manage Users</h1>
            
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
            
            <!-- User Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Users</p>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM users");
                            $totalUsersCount = $stmt->fetchColumn();
                            ?>
                            <p class="text-xl font-bold"><?php echo $totalUsersCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Admin Users</p>
                            <?php
                            $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
                            $adminCount = $stmt->fetchColumn();
                            ?>
                            <p class="text-xl font-bold"><?php echo $adminCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
                <form action="users.php" method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            value="<?php echo htmlspecialchars($search); ?>" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search by username, email, name..."
                        >
                    </div>
                    
                    <div>
                        <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                        <select 
                            id="filter" 
                            name="filter" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="" <?php echo $filter === '' ? 'selected' : ''; ?>>All Users</option>
                            <option value="admin" <?php echo $filter === 'admin' ? 'selected' : ''; ?>>Admins Only</option>
                            <option value="customer" <?php echo $filter === 'customer' ? 'selected' : ''; ?>>Customers Only</option>
                        </select>
                    </div>
                    
                    <div class="self-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                            <i class="fas fa-search mr-2"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $user['id']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                                $name = trim($user['first_name'] . ' ' . $user['last_name']);
                                                echo $name ? htmlspecialchars($name) : '-'; 
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($user['order_count'] > 0): ?>
                                                <a href="orders.php?search=<?php echo urlencode($user['email']); ?>" class="text-blue-500 hover:underline">
                                                    <?php echo $user['order_count']; ?> orders
                                                </a>
                                            <?php else: ?>
                                                0
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($user['is_admin']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Admin
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Customer
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center">
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a 
                                                        href="users.php?toggle_admin=<?php echo $user['id']; ?>" 
                                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                                        onclick="return confirm('Are you sure you want to <?php echo $user['is_admin'] ? 'remove admin privileges from' : 'make admin'; ?> this user?');"
                                                    >
                                                        <?php if ($user['is_admin']): ?>
                                                            <i class="fas fa-user-minus"></i> Remove Admin
                                                        <?php else: ?>
                                                            <i class="fas fa-user-shield"></i> Make Admin
                                                        <?php endif; ?>
                                                    </a>
                                                    <a 
                                                        href="#" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>');"
                                                    >
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Current User</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No users found
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
                            <a href="?page=<?php echo ($page - 1); ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
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
                                <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
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

    <script>
        function confirmDelete(id, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This will remove all their personal data but maintain order history.`)) {
                window.location.href = `users.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>
