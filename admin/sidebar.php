<aside class="w-64 bg-gray-800 text-white h-screen sticky top-0">
    <div class="p-4">
        <h2 class="text-xl font-bold">Admin Panel</h2>
    </div>
    <nav class="mt-6">
        <ul>
            <li class="px-4 py-2 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-gray-700' : ''; ?>">
                <a href="index.php" class="flex items-center">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="px-4 py-2 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' || basename($_SERVER['PHP_SELF']) === 'add_product.php' || basename($_SERVER['PHP_SELF']) === 'edit_product.php' ? 'bg-gray-700' : ''; ?>">
                <a href="products.php" class="flex items-center">
                    <i class="fas fa-box w-6"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="px-4 py-2 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-gray-700' : ''; ?>">
                <a href="categories.php" class="flex items-center">
                    <i class="fas fa-tag w-6"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="px-4 py-2 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' || basename($_SERVER['PHP_SELF']) === 'order_details.php' ? 'bg-gray-700' : ''; ?>">
                <a href="orders.php" class="flex items-center">
                    <i class="fas fa-shopping-cart w-6"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="px-4 py-2 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'bg-gray-700' : ''; ?>">
                <a href="users.php" class="flex items-center">
                    <i class="fas fa-users w-6"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="border-t border-gray-700 mt-4 pt-4 px-4 py-2 hover:bg-gray-700">
                <a href="../index.php" class="flex items-center">
                    <i class="fas fa-store w-6"></i>
                    <span>View Store</span>
                </a>
            </li>
            <li class="px-4 py-2 hover:bg-gray-700">
                <a href="../login_and_signup/logout.php" class="flex items-center">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
