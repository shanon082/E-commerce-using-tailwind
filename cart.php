<?php
session_start();
require_once 'db.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove item from cart
if (isset($_GET['remove_item']) && isset($_SESSION['cart'][$_GET['remove_item']])) {
    unset($_SESSION['cart'][$_GET['remove_item']]);
    header('Location: cart.php');
    exit;
}

// Update item quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if (isset($_SESSION['cart'][$product_id])) {
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// Calculate cart totals
$subtotal = 0;
$totalItems = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

// Shipping cost (free over $100)
$shipping = ($subtotal >= 100) ? 0 : 10;

// Total cost
$total = $subtotal + $shipping;

// Get recommended products
$recommendedProducts = [];
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $categoryIds = [];
    
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $conn->prepare("SELECT category_id FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        if ($product && !in_array($product['category_id'], $categoryIds)) {
            $categoryIds[] = $product['category_id'];
        }
    }
    
    if (!empty($categoryIds)) {
        $catPlaceholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $productIds = array_keys($_SESSION['cart']);
        $productPlaceholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $query = "SELECT * FROM products 
                 WHERE category_id IN ($catPlaceholders) 
                 AND id NOT IN ($productPlaceholders)
                 ORDER BY rating DESC LIMIT 4";
        
        $stmt = $conn->prepare($query);
        $params = array_merge($categoryIds, $productIds);
        $stmt->execute($params);
        $recommendedProducts = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
    <!-- Custom styles for cart page -->
    <style>
        /* Animated button effect */
        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        /* Quantity input styling */
        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* Cart item hover effect */
        .cart-item {
            transition: all 0.2s ease;
        }
        
        .cart-item:hover {
            background-color: #f9fafb;
        }
        
        /* Price animation on update */
        @keyframes priceUpdate {
            0% { color: #3b82f6; }
            100% { color: inherit; }
        }
        
        .price-updated {
            animation: priceUpdate 1.5s ease;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <!-- PHP backend integration will handle:
    1. Session management for cart items storage
    2. Update quantity functionality with stock validation
    3. Remove item functionality
    4. Price calculations including discounts
    5. Save for later functionality (wishlist integration)
    6. Recommended products based on cart items
    -->

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="text-xl font-medium mb-4">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Looks like you haven't added anything to your cart yet.</p>
                <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-md inline-block transition">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium">Cart Items (<?php echo $totalItems; ?>)</h2>
                        </div>
                        
                        <form action="cart.php" method="post">
                            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                                <div class="border-b border-gray-200 last:border-b-0">
                                    <div class="flex flex-col sm:flex-row p-4">
                                        <div class="sm:w-24 h-24 mb-4 sm:mb-0">
                                            <img 
                                                src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                class="w-full h-full object-cover rounded"
                                            >
                                        </div>
                                        <div class="flex-1 sm:ml-4">
                                            <div class="flex flex-col sm:flex-row sm:justify-between">
                                                <div>
                                                    <h3 class="font-medium">
                                                        <a href="product_description.php?id=<?php echo $product_id; ?>" class="hover:text-blue-500">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    </h3>
                                                    <div class="text-sm text-gray-500 mb-2">
                                                        Unit Price: $<?php echo number_format($item['price'], 2); ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center mb-4 sm:mb-0">
                                                    <div class="flex items-center border border-gray-300 rounded-md mr-4">
                                                        <button 
                                                            type="button" 
                                                            class="px-3 py-1 bg-gray-100" 
                                                            onclick="decrementQuantity('quantity-<?php echo $product_id; ?>')"
                                                        >-</button>
                                                        <input 
                                                            id="quantity-<?php echo $product_id; ?>" 
                                                            name="quantity[<?php echo $product_id; ?>]" 
                                                            type="number" 
                                                            min="1" 
                                                            value="<?php echo $item['quantity']; ?>" 
                                                            class="w-12 text-center border-none focus:outline-none py-1"
                                                        >
                                                        <button 
                                                            type="button" 
                                                            class="px-3 py-1 bg-gray-100" 
                                                            onclick="incrementQuantity('quantity-<?php echo $product_id; ?>')"
                                                        >+</button>
                                                    </div>
                                                    <div class="font-medium">
                                                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex justify-between mt-2">
                                                <a 
                                                    href="cart.php?remove_item=<?php echo $product_id; ?>" 
                                                    class="text-red-500 hover:text-red-700 text-sm flex items-center"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i> Remove
                                                </a>
                                                <a 
                                                    href="#" 
                                                    class="text-blue-500 hover:text-blue-700 text-sm flex items-center"
                                                >
                                                    <i class="far fa-heart mr-1"></i> Save for Later
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="p-4 bg-gray-50">
                                <button 
                                    type="submit" 
                                    name="update_cart" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md transition"
                                >
                                    Update Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-6">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium">Order Summary</h2>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between mb-2">
                                <span>Subtotal (<?php echo $totalItems; ?> items)</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Shipping</span>
                                <?php if ($shipping > 0): ?>
                                    <span>$<?php echo number_format($shipping, 2); ?></span>
                                <?php else: ?>
                                    <span class="text-green-600">Free</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($shipping > 0): ?>
                                <div class="text-xs text-gray-500 mb-4">
                                    Free shipping on orders over $100
                                </div>
                            <?php endif; ?>
                            <div class="border-t border-gray-200 pt-2 mb-4">
                                <div class="flex justify-between font-bold">
                                    <span>Total</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                            <a 
                                href="check-out.php" 
                                class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center justify-center transition w-full mb-4"
                            >
                                Proceed to Checkout
                            </a>
                            <a 
                                href="index.php" 
                                class="text-blue-500 hover:text-blue-600 flex items-center justify-center"
                            >
                                <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Products -->
            <?php if (!empty($recommendedProducts)): ?>
            <div class="mt-10">
                <h2 class="text-xl font-bold mb-4">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($recommendedProducts as $product): ?>
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                        <a href="product_description.php?id=<?php echo $product['id']; ?>">
                            <img 
                                src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-40 object-cover"
                            >
                            <div class="p-3">
                                <h3 class="text-sm font-medium truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="flex items-center mt-1">
                                    <span class="text-blue-500 font-bold">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['old_price'] > $product['price']): ?>
                                        <span class="ml-2 text-gray-400 text-xs line-through">$<?php echo number_format($product['old_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center mt-1 text-yellow-400 text-xs">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $product['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $product['rating']): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </a>
                        <div class="px-3 pb-3">
                            <button 
                                onclick="addToCart(<?php echo $product['id']; ?>)" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 rounded transition"
                            >
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function decrementQuantity(inputId) {
            const input = document.getElementById(inputId);
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }
        
        function incrementQuantity(inputId) {
            const input = document.getElementById(inputId);
            const currentValue = parseInt(input.value);
            input.value = currentValue + 1;
        }
        
        function addToCart(productId) {
            window.location.href = `product_description.php?id=${productId}`;
        }
    </script>
</body>
</html>
