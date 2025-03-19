<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Get user data if logged in
$userData = getUserData($conn);
$cartCount = 0;

if ($userData) {
    $cartCount = getCartCount($conn, $userData['id']);
}

// Get all main categories for the navigation menu
$mainCategories = getMainCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jumia Clone - Online Shopping for Electronics, Phones, Fashion & more</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="https://www.jumia.com/favicon.ico" type="image/x-icon">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Top banner -->
    <div class="bg-jumia-blue text-white text-center py-2 text-sm">
        <div class="container mx-auto">
            <p class="font-semibold">Free shipping on orders above $50 | Shop now!</p>
        </div>
    </div>
    
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top header with logo and search -->
            <div class="flex items-center justify-between py-4">
                <!-- Mobile menu toggle -->
                <button class="md:hidden menu-toggle text-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Logo -->
                <a href="index.php" class="flex items-center">
                    <h1 class="text-2xl font-bold">
                        <span class="text-jumia-orange">JUMIA</span>
                        <span class="text-jumia-blue hidden sm:inline">CLONE</span>
                    </h1>
                </a>
                
                <!-- Search bar -->
                <div class="hidden md:flex flex-1 mx-8">
                    <form action="search.php" method="GET" class="w-full flex">
                        <input type="text" name="q" placeholder="Search products, brands and categories" 
                               class="border border-gray-300 rounded-l-md px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <button type="submit" class="bg-jumia-orange text-white px-6 py-2 rounded-r-md hover:bg-orange-600 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- User actions -->
                <div class="flex items-center space-x-4">
                    <!-- Account dropdown -->
                    <div class="hidden sm:block relative dropdown">
                        <a href="#" class="flex items-center text-gray-700 hover:text-jumia-orange">
                            <i class="fas fa-user-circle mr-1"></i>
                            <span class="text-sm">
                                <?php if ($userData): ?>
                                    Hi, <?= htmlspecialchars($userData['username']) ?>
                                <?php else: ?>
                                    Account
                                <?php endif; ?>
                            </span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden">
                            <?php if ($userData): ?>
                                <a href="myAccount.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Account</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Orders</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
                                <div class="border-t border-gray-100"></div>
                                <a href="login_and_signup/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            <?php else: ?>
                                <a href="login_and_signup/login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Login</a>
                                <a href="login_and_signup/signup.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign Up</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Help dropdown -->
                    <div class="hidden sm:block relative dropdown">
                        <a href="#" class="flex items-center text-gray-700 hover:text-jumia-orange">
                            <i class="fas fa-question-circle mr-1"></i>
                            <span class="text-sm">Help</span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Help Center</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Place an Order</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Payment Options</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Track Order</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Returns & Refunds</a>
                        </div>
                    </div>
                    
                    <!-- Cart -->
                    <a href="cart.php" class="flex items-center text-gray-700 hover:text-jumia-orange relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-jumia-orange text-white text-xs rounded-full w-5 h-5 flex items-center justify-center cart-count">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                        <span class="ml-1 text-sm hidden sm:inline">Cart</span>
                    </a>
                </div>
            </div>
            
            <!-- Mobile search bar (hidden on desktop) -->
            <div class="md:hidden pb-4">
                <form action="search.php" method="GET" class="w-full flex">
                    <input type="text" name="q" placeholder="Search products, brands and categories" 
                           class="border border-gray-300 rounded-l-md px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <button type="submit" class="bg-jumia-orange text-white px-4 py-2 rounded-r-md hover:bg-orange-600 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Navigation bar -->
        <nav class="bg-white border-t border-gray-200">
            <div class="container mx-auto px-4">
                <ul class="hidden md:flex space-x-8 py-3 overflow-x-auto" id="category-nav">
                    <li>
                        <a href="index.php" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Home</a>
                    </li>
                    <?php foreach($mainCategories as $category): ?>
                        <li class="relative dropdown">
                            <a href="categories.php?slug=<?= htmlspecialchars($category['slug']) ?>" 
                               class="text-gray-700 hover:text-jumia-orange text-sm font-medium flex items-center">
                                <?= htmlspecialchars($category['name']) ?>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </a>
                            <?php
                            // Get subcategories
                            $subCategories = getSubCategories($conn, $category['id']);
                            if (count($subCategories) > 0):
                            ?>
                                <div class="dropdown-menu absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg z-10 hidden">
                                    <?php foreach($subCategories as $subCategory): ?>
                                        <a href="categories.php?slug=<?= htmlspecialchars($subCategory['slug']) ?>" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <?= htmlspecialchars($subCategory['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="#" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Deals</a>
                    </li>
                </ul>
                
                <!-- Mobile navigation menu (hidden on desktop) -->
                <div class="md:hidden">
                    <ul class="hidden flex-col py-3 border-t border-gray-200" id="nav-links">
                        <li class="py-2">
                            <a href="index.php" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Home</a>
                        </li>
                        <?php foreach($mainCategories as $category): ?>
                            <li class="py-2">
                                <a href="categories.php?slug=<?= htmlspecialchars($category['slug']) ?>" 
                                   class="text-gray-700 hover:text-jumia-orange text-sm font-medium">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php
                            // Get subcategories
                            $subCategories = getSubCategories($conn, $category['id']);
                            if (count($subCategories) > 0):
                                foreach($subCategories as $subCategory):
                            ?>
                                <li class="py-2 pl-4">
                                    <a href="categories.php?slug=<?= htmlspecialchars($subCategory['slug']) ?>" 
                                       class="text-gray-600 hover:text-jumia-orange text-sm">
                                        <?= htmlspecialchars($subCategory['name']) ?>
                                    </a>
                                </li>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        <?php endforeach; ?>
                        <li class="py-2">
                            <a href="#" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Deals</a>
                        </li>
                        <li class="py-2 border-t border-gray-200 mt-2">
                            <?php if ($userData): ?>
                                <a href="myAccount.php" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">My Account</a>
                            <?php else: ?>
                                <a href="login_and_signup/login.php" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Login</a>
                            <?php endif; ?>
                        </li>
                        <li class="py-2">
                            <a href="#" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Help</a>
                        </li>
                        <?php if ($userData): ?>
                            <li class="py-2">
                                <a href="login_and_signup/logout.php" class="text-gray-700 hover:text-jumia-orange text-sm font-medium">Logout</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main content container -->
    <main class="flex-grow">
