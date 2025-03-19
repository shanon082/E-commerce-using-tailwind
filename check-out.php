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

// Get cart items
$cartItems = getCartItems($conn, $userData['id']);

// If cart is empty and not buying an item directly, redirect to cart
if (empty($cartItems) && !isset($_GET['buy_now'])) {
    header("Location: cart.php");
    exit;
}

// Handle buy now functionality
if (isset($_GET['buy_now']) && is_numeric($_GET['buy_now'])) {
    $productId = (int)$_GET['buy_now'];
    $product = getProductById($conn, $productId);
    
    if ($product) {
        // Create a temporary cart item
        $cartItems = [[
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ?: $product['price'],
            'image' => $product['image'],
            'quantity' => 1
        ]];
        
        $_SESSION['buy_now_product'] = $productId;
    }
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Calculate shipping, tax, and total
$shipping = $subtotal > 50 ? 0 : 5.99;
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $shipping + $tax;

// Get user addresses
$addresses = getUserAddresses($conn, $userData['id']);
$defaultAddress = getDefaultAddress($conn, $userData['id']);

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $addressId = (int)$_POST['address_id'];
    $paymentMethod = $_POST['payment_method'];
    $deliveryMethod = $_POST['delivery_method'];
    
    // Validate
    $errors = [];
    
    if ($addressId <= 0) {
        $errors[] = 'Please select a valid shipping address.';
    }
    
    if (empty($paymentMethod)) {
        $errors[] = 'Please select a payment method.';
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        // Start transaction
        $conn->beginTransaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address_id, payment_method, payment_status, order_status) 
                                    VALUES (?, ?, ?, ?, 'pending', 'processing')");
            $stmt->execute([$userData['id'], $total, $addressId, $paymentMethod]);
            
            $orderId = $conn->lastInsertId();
            
            // Add order items
            if (isset($_SESSION['buy_now_product'])) {
                // Buy now case
                $productId = $_SESSION['buy_now_product'];
                $product = getProductById($conn, $productId);
                
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                        VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $orderId,
                    $product['id'],
                    1,
                    $product['sale_price'] ?: $product['price']
                ]);
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - 1 WHERE id = ? AND stock > 0");
                $stmt->execute([$product['id']]);
                
                // Remove buy now session var
                unset($_SESSION['buy_now_product']);
            } else {
                // Regular cart checkout
                foreach ($cartItems as $item) {
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                           VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price']
                    ]);
                    
                    // Update product stock
                    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                }
                
                // Clear cart
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$userData['id']]);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?id=" . $orderId);
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $errors[] = 'There was an error processing your order. Please try again.';
        }
    }
}
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="cart.php" class="text-gray-600 hover:text-jumia-orange">Cart</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium">Checkout</span>
        </nav>
    </div>
</div>

<!-- Checkout Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc pl-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="check-out.php<?= isset($_GET['buy_now']) ? '?buy_now=' . (int)$_GET['buy_now'] : '' ?>">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Checkout Steps -->
                <div class="lg:w-2/3">
                    <!-- 1. Delivery Address -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="font-semibold text-lg text-gray-800">1. Delivery Address</h2>
                        </div>
                        
                        <div class="p-6">
                            <?php if (empty($addresses)): ?>
                                <div class="mb-4">
                                    <p class="text-gray-600 mb-4">You don't have any saved addresses. Please add a new address to continue.</p>
                                    <a href="edit_address.php" class="bg-jumia-orange text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors inline-block">
                                        Add New Address
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                            <input type="radio" name="address_id" id="address_<?= $address['id'] ?>" 
                                                   value="<?= $address['id'] ?>" 
                                                   <?= $address['is_default'] ? 'checked' : '' ?> 
                                                   class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                            
                                            <label for="address_<?= $address['id'] ?>" class="ml-3 flex-1">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-medium text-gray-800">
                                                            <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?>
                                                            <?php if ($address['is_default']): ?>
                                                                <span class="ml-2 text-xs bg-jumia-orange text-white px-2 py-0.5 rounded-full">Default</span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <p class="text-gray-600 mt-1"><?= htmlspecialchars($address['address']) ?></p>
                                                        <p class="text-gray-600"><?= htmlspecialchars($address['city'] . ', ' . $address['region'] . ' ' . $address['postal_code']) ?></p>
                                                        <p class="text-gray-600">Phone: <?= htmlspecialchars($address['phone']) ?></p>
                                                        <?php if ($address['alternate_phone']): ?>
                                                            <p class="text-gray-600">Alt. Phone: <?= htmlspecialchars($address['alternate_phone']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="edit_address.php?id=<?= $address['id'] ?>" class="text-jumia-orange hover:underline text-sm">
                                                        Edit
                                                    </a>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="mt-4">
                                        <a href="edit_address.php" class="text-jumia-orange hover:underline flex items-center">
                                            <i class="fas fa-plus-circle mr-2"></i> Add New Address
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- 2. Delivery Method -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="font-semibold text-lg text-gray-800">2. Delivery Method</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                    <input type="radio" name="delivery_method" id="standard_delivery" 
                                           value="standard" checked 
                                           class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    
                                    <label for="standard_delivery" class="ml-3 flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-gray-800">Standard Delivery</p>
                                                <p class="text-gray-600">3-5 business days</p>
                                            </div>
                                            <p class="font-medium text-gray-800">
                                                <?= $shipping > 0 ? formatPrice($shipping) : 'Free' ?>
                                            </p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                    <input type="radio" name="delivery_method" id="express_delivery" 
                                           value="express" 
                                           class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    
                                    <label for="express_delivery" class="ml-3 flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-gray-800">Express Delivery</p>
                                                <p class="text-gray-600">1-2 business days</p>
                                            </div>
                                            <p class="font-medium text-gray-800">$10.00</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. Payment Method -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="font-semibold text-lg text-gray-800">3. Payment Method</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                    <input type="radio" name="payment_method" id="pay_on_delivery" 
                                           value="pay_on_delivery" checked 
                                           class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    
                                    <label for="pay_on_delivery" class="ml-3">
                                        <p class="font-medium text-gray-800">Pay on Delivery</p>
                                        <p class="text-gray-600 text-sm">Pay with cash or card when your order is delivered</p>
                                    </label>
                                </div>
                                
                                <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                    <input type="radio" name="payment_method" id="credit_card" 
                                           value="credit_card" 
                                           class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    
                                    <label for="credit_card" class="ml-3">
                                        <p class="font-medium text-gray-800">Credit/Debit Card</p>
                                        <p class="text-gray-600 text-sm">Pay securely with your card</p>
                                        <div class="flex space-x-2 mt-2">
                                            <i class="fab fa-cc-visa text-2xl text-blue-700"></i>
                                            <i class="fab fa-cc-mastercard text-2xl text-red-500"></i>
                                            <i class="fab fa-cc-amex text-2xl text-blue-500"></i>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="border border-gray-200 rounded-md p-4 flex items-start">
                                    <input type="radio" name="payment_method" id="mobile_money" 
                                           value="mobile_money" 
                                           class="mt-1 rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    
                                    <label for="mobile_money" class="ml-3">
                                        <p class="font-medium text-gray-800">Mobile Money</p>
                                        <p class="text-gray-600 text-sm">Pay using your mobile money account</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-24">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="font-semibold text-lg text-gray-800">Order Summary</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4 mb-6">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-md overflow-hidden">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                 class="h-full w-full object-contain">
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-800 line-clamp-2">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </p>
                                            <div class="flex justify-between items-center mt-1">
                                                <p class="text-sm text-gray-600">Qty: <?= $item['quantity'] ?></p>
                                                <p class="text-sm font-medium text-gray-800">
                                                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium text-gray-800"><?= formatPrice($subtotal) ?></span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Shipping</span>
                                    <span class="font-medium text-gray-800">
                                        <?= $shipping > 0 ? formatPrice($shipping) : 'Free' ?>
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax</span>
                                    <span class="font-medium text-gray-800"><?= formatPrice($tax) ?></span>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-bold text-gray-800">Total</span>
                                        <span class="text-xl font-bold text-jumia-orange"><?= formatPrice($total) ?></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Including taxes and shipping fees</p>
                                </div>
                            </div>
                            
                            <!-- Promo Code -->
                            <div class="mb-6">
                                <div class="flex">
                                    <input type="text" placeholder="Enter promo code" 
                                           class="flex-1 border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-r-md hover:bg-gray-300 transition-colors">
                                        Apply
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Place Order Button -->
                            <button type="submit" name="place_order" class="w-full bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors flex items-center justify-center">
                                <i class="fas fa-lock mr-2"></i> Place Order
                            </button>
                            
                            <p class="text-xs text-gray-500 mt-4 text-center">
                                By placing your order, you agree to our 
                                <a href="#" class="text-jumia-orange hover:underline">Terms and Conditions</a> and 
                                <a href="#" class="text-jumia-orange hover:underline">Privacy Policy</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
