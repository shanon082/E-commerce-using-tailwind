<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!checkUserSession()) {
    // Save current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login_and_signup/login.php");
    exit;
}

// Get user data
$userData = getUserData($conn);
$cartItems = getCartItems($conn, $userData['id']);
$cartTotal = getCartTotal($conn, $userData['id']);

// Get recommended products
$recommendedProducts = getLatestProducts($conn, 4);

// Handle remove item action
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $cartItemId = (int)$_GET['remove'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartItemId, $userData['id']]);
    
    header("Location: cart.php");
    exit;
}

// Handle update quantity action
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartItemId => $quantity) {
        $cartItemId = (int)$cartItemId;
        $quantity = (int)$quantity;
        
        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cartItemId, $userData['id']]);
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cartItemId, $userData['id']]);
        }
    }
    
    header("Location: cart.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium">Shopping Cart</span>
        </nav>
    </div>
</div>

<!-- Cart Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Your Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-shopping-cart text-6xl"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Looks like you haven't added any products to your cart yet.</p>
                <a href="index.php" class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <form method="post" action="cart.php">
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
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($cartItems as $item): ?>
                                        <?php
                                        $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                                        $subtotal = $price * $item['quantity'];
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-md overflow-hidden">
                                                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                                             class="h-full w-full object-contain">
                                                    </div>
                                                    <div class="ml-4">
                                                        <a href="product_description.php?id=<?= $item['product_id'] ?>" 
                                                           class="text-sm font-medium text-gray-900 hover:text-jumia-orange">
                                                            <?= htmlspecialchars($item['name']) ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                    <div class="text-sm font-medium text-jumia-orange">
                                                        <?= formatPrice($item['sale_price']) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 line-through">
                                                        <?= formatPrice($item['price']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-sm font-medium text-jumia-orange">
                                                        <?= formatPrice($item['price']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="quantity flex border border-gray-300 rounded-md overflow-hidden w-32">
                                                    <button type="button" class="quantity-decrease px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 focus:outline-none">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" name="quantity[<?= $item['id'] ?>]" min="1" max="99" value="<?= $item['quantity'] ?>" 
                                                           class="quantity-input w-full text-center border-none focus:outline-none" 
                                                           data-product-id="<?= $item['id'] ?>">
                                                    <button type="button" class="quantity-increase px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 focus:outline-none">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 item-subtotal-<?= $item['id'] ?>">
                                                    <?= formatPrice($subtotal) ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="cart.php?remove=<?= $item['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900" 
                                                   onclick="return confirm('Are you sure you want to remove this item?')">
                                                    <i class="fas fa-trash-alt"></i> Remove
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="px-6 py-4 border-t border-gray-200">
                                <button type="submit" name="update_cart" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                                    Update Cart
                                </button>
                                <a href="index.php" class="ml-2 text-jumia-orange hover:underline">
                                    <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-800"><?= formatPrice($cartTotal) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium text-gray-800">
                                    <?php 
                                    $shipping = $cartTotal > 50 ? 0 : 5.99;
                                    echo $shipping > 0 ? formatPrice($shipping) : 'Free';
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium text-gray-800">
                                    <?php 
                                    $tax = $cartTotal * 0.1; // 10% tax
                                    echo formatPrice($tax);
                                    ?>
                                </span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-800">Total</span>
                                    <span class="text-xl font-bold text-jumia-orange cart-total-amount">
                                        <?= formatPrice($cartTotal + $shipping + $tax) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label for="promo-code" class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                            <div class="flex">
                                <input type="text" id="promo-code" placeholder="Enter promo code" 
                                       class="flex-1 border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-r-md hover:bg-gray-300 transition-colors">
                                    Apply
                                </button>
                            </div>
                        </div>
                        
                        <a href="check-out.php" class="block text-center bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recommended Products -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">You Might Also Like</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach($recommendedProducts as $product): ?>
                <div class="product-card group">
                    <a href="product_description.php?id=<?= $product['id'] ?>" class="block product-image-hover">
                        <div class="relative h-48 bg-gray-200">
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="w-full h-full object-contain">
                            
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                    SALE
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-800 mb-1 group-hover:text-jumia-orange transition-colors line-clamp-2">
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>
                            
                            <div class="text-sm text-gray-500 mb-2">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </div>
                            
                            <div class="flex items-baseline mb-2">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <span class="text-lg font-bold text-jumia-orange">
                                        <?= formatPrice($product['sale_price']) ?>
                                    </span>
                                    <span class="ml-2 text-sm text-gray-500 line-through">
                                        <?= formatPrice($product['price']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-jumia-orange">
                                        <?= formatPrice($product['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    
                    <div class="px-4 pb-4 mt-auto">
                        <button class="add-to-cart w-full bg-jumia-orange text-white py-2 rounded-md hover:bg-orange-600 transition-colors flex items-center justify-center" data-product-id="<?= $product['id'] ?>">
                            <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
