<?php
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get admin user data
$adminId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$adminId]);
$adminData = $stmt->fetch();

// Get the current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Jumia Clone</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="https://www.jumia.com/favicon.ico" type="image/x-icon">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        .admin-menu-link.active {
            background-color: var(--jumia-orange);
            color: white;
        }
        
        .admin-menu-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Top header -->
    <header class="bg-jumia-blue text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-3">
                <!-- Logo and mobile menu toggle -->
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="mr-3 text-white lg:hidden focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <a href="index.php" class="flex items-center">
                        <h1 class="text-2xl font-bold">
                            <span class="text-jumia-orange">JUMIA</span>
                            <span class="text-white">ADMIN</span>
                        </h1>
                    </a>
                </div>
                
                <!-- Right side navigation -->
                <div class="flex items-center space-x-4">
                    <a href="../index.php" target="_blank" class="text-white hover:text-jumia-orange transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i> View Store
                    </a>
                    
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <span><?= htmlspecialchars($adminData['username']) ?></span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        
                        <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <div class="border-t border-gray-100"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-white w-64 shadow-md fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-30 pt-16">
            <div class="py-4">
                <nav class="mt-5 px-2">
                    <a href="index.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                        Dashboard
                    </a>
                    
                    <a href="orders.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'orders.php' ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart mr-3 text-lg"></i>
                        Orders
                    </a>
                    
                    <a href="products.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'products.php' || $currentPage == 'add_product.php' || $currentPage == 'edit_product.php' ? 'active' : '' ?>">
                        <i class="fas fa-box mr-3 text-lg"></i>
                        Products
                    </a>
                    
                    <a href="categories.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'categories.php' ? 'active' : '' ?>">
                        <i class="fas fa-tags mr-3 text-lg"></i>
                        Categories
                    </a>
                    
                    <a href="users.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'users.php' ? 'active' : '' ?>">
                        <i class="fas fa-users mr-3 text-lg"></i>
                        Users
                    </a>
                    
                    <div class="border-t border-gray-200 my-4"></div>
                    
                    <a href="settings.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md mb-2 <?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog mr-3 text-lg"></i>
                        Settings
                    </a>
                    
                    <a href="logout.php" class="admin-menu-link group flex items-center px-4 py-2 text-base font-medium rounded-md text-red-600 hover:text-red-800 hover:bg-red-100">
                        <i class="fas fa-sign-out-alt mr-3 text-lg"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </aside>
        
        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-20 hidden lg:hidden"></div>
        
        <!-- Main content -->
        <main class="flex-1 ml-0 lg:ml-64 p-0">
            <div class="py-6">
                <!-- Page content goes here -->

<script>
    // Toggle user dropdown menu
    document.getElementById('user-menu-button').addEventListener('click', function() {
        document.getElementById('user-menu-dropdown').classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    window.addEventListener('click', function(event) {
        if (!event.target.closest('#user-menu-button')) {
            document.getElementById('user-menu-dropdown').classList.add('hidden');
        }
    });
    
    // Mobile sidebar toggle
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.toggle('hidden');
    });
    
    // Close sidebar when clicking overlay
    document.getElementById('sidebar-overlay').addEventListener('click', function() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.add('hidden');
    });
</script>
