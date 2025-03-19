<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./assets/images/favicon.ico" type="image/x-icon" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
    <!-- Enhanced header styles -->
    <style>
        /* Dropdown menu animations and styling */
        .dropdown-menu {
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .dropdown:hover .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Mobile menu animations */
        .mobile-menu-enter {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
        }
        
        .mobile-menu-enter-active {
            max-height: 500px;
            opacity: 1;
        }
        
        /* Nav link hover effects */
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        /* Search bar focus effect */
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        /* Better sticky header experience */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        
        /* Cart counter animation */
        @keyframes cartBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .cart-count {
            animation: cartBounce 0.6s;
        }
    </style>
</head>
<body>

<!-- PHP backend integration will handle:
1. User authentication status checks
2. Dynamic category loading from database
3. Cart item count from session
4. Search functionality
5. Mobile responsiveness detection
6. User profile management links
-->
    <header class="bg-white shadow-sm">
        <!-- Top Banner -->
        <div class="bg-blue-600 text-white py-2 px-4">
            <div class="container mx-auto flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="flex items-center space-x-4 mb-2 md:mb-0">
                    <a href="#" class="hover:underline">Download App</a>
                    <a href="#" class="hover:underline">Sell on TUKOLE</a>
                    <a href="#" class="hover:underline">Track Order</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span>TECH SALE: UP TO 60% OFF!</span>
                    <span>CALL: 0200 804 020</span>
                </div>
            </div>
        </div>
        
        <!-- Main Navigation -->
        <nav class="container mx-auto py-3 px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <!-- Logo and Mobile Menu -->
                <div class="flex items-center justify-between w-full md:w-auto mb-4 md:mb-0">
                    <a href="index.php" class="flex items-center">
                        <h2 class="text-2xl font-bold text-blue-500">TUKOLE <span class="text-blue-700 text-sm">business</span></h2>
                    </a>
                    <button class="md:hidden" id="mobile-menu-button">
                        <i class="fas fa-bars text-gray-600 text-xl"></i>
                    </button>
                </div>
                
                <!-- Search Bar -->
                <div class="w-full md:w-2/5 mb-4 md:mb-0">
                    <form action="search.php" method="GET" class="flex">
                        <input 
                            type="search" 
                            name="q" 
                            placeholder="Search product, brand and category" 
                            class="w-full border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-md transition">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-6" id="navigation-links">
                    <!-- Account Dropdown -->
                    <div class="dropdown relative group">
                        <?php if (isset($_SESSION['username'])): ?>
                            <a href="#" class="flex items-center text-gray-700 hover:text-blue-500 transition">
                                <i class="fas fa-user-circle mr-1 text-blue-500"></i>
                                <span>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </a>
                            <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 py-2">
                                <li><a href="myAccount.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">My Account</a></li>
                                <li><a href="orders.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Orders</a></li>
                                <li><a href="wishlist.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Wishlist</a></li>
                                <li><a href="login_and_signup/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Logout</a></li>
                            </ul>
                        <?php else: ?>
                            <a href="#" class="flex items-center text-gray-700 hover:text-blue-500 transition">
                                <i class="fas fa-user-circle mr-1 text-blue-500"></i>
                                <span>Account</span>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </a>
                            <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 py-2">
                                <li><a href="login_and_signup/login.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Login</a></li>
                                <li><a href="login_and_signup/signup.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Sign Up</a></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Help Dropdown -->
                    <div class="dropdown relative group">
                        <a href="#" class="flex items-center text-gray-700 hover:text-blue-500 transition">
                            <i class="fas fa-question-circle mr-1 text-blue-500"></i>
                            <span>Help</span>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 py-2">
                            <li><a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Help Center</a></li>
                            <li><a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Place an Order</a></li>
                            <li><a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Payment Options</a></li>
                            <li><a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Track Order</a></li>
                            <li><a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-500">Returns & Refunds</a></li>
                        </ul>
                    </div>
                    
                    <!-- Cart Link -->
                    <a href="cart.php" class="flex items-center text-gray-700 hover:text-blue-500 transition">
                        <i class="fas fa-shopping-cart mr-1 text-blue-500"></i>
                        <span>Cart</span>
                        <?php 
                        // Display cart count if exists
                        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): 
                            $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
                        ?>
                            <span class="ml-1 bg-blue-500 text-white text-xs rounded-full px-2 py-0.5"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="md:hidden mt-4 hidden" id="mobile-menu">
                <div class="flex flex-col space-y-2">
                    <?php if (isset($_SESSION['username'])): ?>
                        <a href="myAccount.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">My Account</a>
                        <a href="orders.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Orders</a>
                        <a href="wishlist.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Wishlist</a>
                        <a href="login_and_signup/logout.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Logout</a>
                    <?php else: ?>
                        <a href="login_and_signup/login.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Login</a>
                        <a href="login_and_signup/signup.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Sign Up</a>
                    <?php endif; ?>
                    <a href="#" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Help Center</a>
                    <a href="cart.php" class="py-2 px-4 text-gray-700 hover:bg-blue-100 rounded">Cart</a>
                </div>
            </div>
        </nav>
        
        <!-- Category Navigation -->
        <div class="bg-gray-100 py-2 border-t border-gray-200">
            <div class="container mx-auto px-4">
                <div class="flex overflow-x-auto scrollbar-hide" style="scrollbar-width: none;">
                    <a href="index.php" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Home</a>
                    <a href="category.php?id=1" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Electronics</a>
                    <a href="category.php?id=2" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Fashion</a>
                    <a href="category.php?id=3" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Home & Living</a>
                    <a href="category.php?id=4" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Health & Beauty</a>
                    <a href="category.php?id=5" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Baby Products</a>
                    <a href="category.php?id=6" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Phones & Tablets</a>
                    <a href="category.php?id=7" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Computing</a>
                    <a href="category.php?id=8" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Gaming</a>
                    <a href="category.php?id=9" class="whitespace-nowrap px-3 py-1 text-sm text-gray-600 hover:text-blue-500">Supermarket</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
