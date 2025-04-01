<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login_and_signup/login.php?redirect=check-out.php');
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Redirect to cart page
    header('Location: cart.php');
    exit;
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch();

// Get user addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$addresses = $stmt->fetchAll();

// Process order submission
if (isset($_POST['place_order'])) {
    $address_id = $_POST['address_id'];
    $payment_method = $_POST['payment_method'];
    $mobile_number = $_POST['mobile_number'] ?? null;
    
    // Calculate order total
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Add shipping if less than $100
    if ($total < 100) {
        $total += 10;
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Create order record
        $stmt = $conn->prepare("INSERT INTO orders (user_id, address_id, total_amount, payment_method) 
                               VALUES (:user_id, :address_id, :total_amount, :payment_method)");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':address_id', $address_id);
        $stmt->bindParam(':total_amount', $total);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->execute();
        
        // Get the order ID
        $order_id = $conn->lastInsertId();
        
        // Add order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES (:order_id, :product_id, :quantity, :price)");
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
            
            // Update product stock
            $update = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");
            $update->bindParam(':quantity', $item['quantity']);
            $update->bindParam(':id', $product_id);
            $update->execute();
        }
        
        // if ($payment_method === 'mobile_money') {
        //     // Example: Initiate mobile money payment using an API
        //     try {
        //         $response = initiateMobileMoneyPayment($mobile_number, $total, $order_id);
        //         if (!$response['success']) {
        //             throw new Exception("Mobile money payment failed: " . $response['message']);
        //         }
        //     } catch (Exception $e) {
        //         $conn->rollBack();
        //         $error = "There was a problem processing your mobile money payment. Please try again.";
        //     }
        // }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to order confirmation
        header('Location: order_confirmation.php?id=' . $order_id);
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = "There was a problem processing your order. Please try again.";
    }
}

// Example function to initiate mobile money payment
// function initiateMobileMoneyPayment($mobile_number, $amount, $order_id) {
//     // Replace with actual API integration
//     $api_url = "https://api.mobilemoneyprovider.com/initiate-payment";
//     $api_key = "your_api_key_here";

//     $data = [
//         'mobile_number' => $mobile_number,
//         'amount' => $amount,
//         'order_id' => $order_id,
//         'callback_url' => "https://yourwebsite.com/payment-callback.php"
//     ];

//     $options = [
//         'http' => [
//             'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key",
//             'method'  => 'POST',
//             'content' => json_encode($data),
//         ],
//     ];

//     $context  = stream_context_create($options);
//     $result = file_get_contents($api_url, false, $context);

//     if ($result === FALSE) {
//         return ['success' => false, 'message' => 'API request failed'];
//     }

//     return json_decode($result, true);
// }

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
    <!-- Custom styles for checkout page -->
    <style>
        /* Progress indicator */
        .checkout-step {
            position: relative;
        }
        
        .checkout-step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 100%;
            height: 2px;
            background-color: #e5e7eb;
            transform: translateY(-50%);
            z-index: 0;
        }
        
        .checkout-step:last-child::after {
            display: none;
        }
        
        .checkout-step.active .step-number {
            background-color: #3b82f6;
            color: white;
        }
        
        .checkout-step.completed .step-number {
            background-color: #22c55e;
            color: white;
        }
        
        /* Radio button custom styling */
        .custom-radio input:checked + .radio-label {
            border-color: #3b82f6;
        }
        
        .custom-radio input:checked + .radio-label .check-icon {
            display: flex;
        }
        
        /* Address card hover effect */
        .address-card {
            transition: all 0.2s ease;
        }
        
        .address-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
        }
        
        /* Payment method card effect */
        .payment-card {
            transition: all 0.2s ease;
        }
        
        .payment-card:hover {
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <!-- PHP backend integration will handle:
    1. User authentication and redirection if not logged in
    2. Session management for cart items
    3. Address retrieval and management from database
    4. Order processing with database transaction
    5. Payment method processing
    6. Post-order inventory updates
    7. Order confirmation email sending
    -->

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> <?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="check-out.php" method="post">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Checkout Details -->
                <div class="lg:w-2/3">
                    <!-- Shipping Address -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-medium">1. Delivery Address</h2>
                            <?php if (count($addresses) > 0): ?>
                                <a href="edit_address.php" class="text-blue-500 hover:underline text-sm">Add New Address</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <?php if (count($addresses) > 0): ?>
                                <div class="grid gap-4">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="border border-gray-200 rounded-md p-4 <?php echo $address['is_default'] ? 'border-blue-500' : ''; ?>">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input 
                                                        id="address-<?php echo $address['id']; ?>" 
                                                        name="address_id" 
                                                        type="radio" 
                                                        value="<?php echo $address['id']; ?>" 
                                                        <?php echo $address['is_default'] ? 'checked' : ''; ?>
                                                        class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="address-<?php echo $address['id']; ?>" class="font-medium text-gray-700">
                                                        <?php echo $address['is_default'] ? 'Default Address' : 'Saved Address'; ?>
                                                    </label>
                                                    <div class="text-gray-500 mt-1">
                                                        <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                                        <p><?php echo htmlspecialchars($address['address']); ?></p>
                                                        <p>
                                                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['region'] . 
                                                            ($address['postal_code'] ? ' ' . $address['postal_code'] : '') . ', ' . $address['country']); ?>
                                                        </p>
                                                        <p>Phone: <?php echo htmlspecialchars($user['phone']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-gray-600 mb-4">You don't have any saved addresses.</p>
                                    <a href="edit_address.php" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block transition">
                                        Add New Address
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Delivery Method -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium">2. Delivery Method</h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid gap-4">
                                <div class="border border-gray-200 rounded-md p-4 border-blue-500">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="delivery-standard" 
                                                name="delivery_method" 
                                                type="radio" 
                                                value="standard"
                                                checked
                                                class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="delivery-standard" class="font-medium text-gray-700">Standard Delivery</label>
                                            <div class="text-gray-500 mt-1">
                                                <p>3-5 business days</p>
                                                <?php if ($shipping > 0): ?>
                                                    <p>$<?php echo number_format($shipping, 2); ?></p>
                                                <?php else: ?>
                                                    <p class="text-green-600">Free</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="delivery-express" 
                                                name="delivery_method" 
                                                type="radio" 
                                                value="express"
                                                class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="delivery-express" class="font-medium text-gray-700">Express Delivery</label>
                                            <div class="text-gray-500 mt-1">
                                                <p>1-2 business days</p>
                                                <p>$15.00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium">3. Payment Method</h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="grid gap-4">
                                <!-- Cash on Delivery -->
                                <div class="border border-gray-200 rounded-md p-4 border-blue-500">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="payment-cash" 
                                                name="payment_method" 
                                                type="radio" 
                                                value="cash_on_delivery"
                                                checked
                                                class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="payment-cash" class="font-medium text-gray-700">Cash on Delivery</label>
                                            <div class="text-gray-500 mt-1">
                                                <p>Pay when you receive your order</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Credit/Debit Card -->
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="payment-card" 
                                                name="payment_method" 
                                                type="radio" 
                                                value="credit_card"
                                                class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                                onclick="togglePaymentDetails('card')"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="payment-card" class="font-medium text-gray-700">Credit/Debit Card</label>
                                            <div class="text-gray-500 mt-1">
                                                <p>Pay securely with your card</p>
                                                <div class="flex space-x-2 mt-2">
                                                    <i class="fab fa-cc-visa text-blue-900 text-xl"></i>
                                                    <i class="fab fa-cc-mastercard text-red-500 text-xl"></i>
                                                    <i class="fab fa-cc-amex text-blue-500 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="card-details" class="mt-4 hidden">
                                        <div class="mb-4">
                                            <label for="card-number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                            <input type="text" id="card-number" name="card_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                                <input type="text" id="expiry-date" name="expiry_date" placeholder="MM/YY" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label for="cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                                                <input type="text" id="cvv" name="cvv" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Mobile Money -->
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="payment-mobile" 
                                                name="payment_method" 
                                                type="radio" 
                                                value="mobile_money"
                                                class="h-4 w-4 text-blue-500 focus:ring-blue-500"
                                                onclick="togglePaymentDetails('mobile')"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="payment-mobile" class="font-medium text-gray-700">Mobile Money</label>
                                            <div class="text-gray-500 mt-1">
                                                <p>Pay using your mobile money account. You will receive a prompt on your phone to complete the payment.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="mobile-details" class="mt-4 hidden">
                                        <div class="mb-4">
                                            <label for="mobile-number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                                            <input 
                                                type="text" 
                                                id="mobile-number" 
                                                name="mobile_number" 
                                                value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                                readonly 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500"
                                            >
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">A payment request will be sent to this number. Please approve it on your phone.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-6">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium">Order Summary</h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="max-h-60 overflow-y-auto mb-4">
                                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                                    <div class="flex py-2 border-b border-gray-100 last:border-b-0">
                                        <div class="w-16 h-16">
                                            <img 
                                                src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                class="w-full h-full object-cover rounded"
                                            >
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-medium truncate"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <div class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></div>
                                            <div class="text-sm font-medium">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4">
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
                                <div class="border-t border-gray-200 pt-2 mb-4">
                                    <div class="flex justify-between font-bold">
                                        <span>Total</span>
                                        <span>$<?php echo number_format($total, 2); ?></span>
                                    </div>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    name="place_order" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md flex items-center justify-center transition w-full mb-4"
                                    <?php echo (count($addresses) === 0) ? 'disabled' : ''; ?>
                                >
                                    Place Order
                                </button>
                                
                                <?php if (count($addresses) === 0): ?>
                                    <p class="text-red-500 text-xs text-center mb-4">Please add a delivery address to continue.</p>
                                <?php endif; ?>
                                
                                <a 
                                    href="cart.php" 
                                    class="text-blue-500 hover:text-blue-600 flex items-center justify-center"
                                >
                                    <i class="fas fa-arrow-left mr-2"></i> Back to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function togglePaymentDetails(method) {
            document.getElementById('card-details').classList.add('hidden');
            document.getElementById('mobile-details').classList.add('hidden');
            if (method === 'card') {
                document.getElementById('card-details').classList.remove('hidden');
            } else if (method === 'mobile') {
                document.getElementById('mobile-details').classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
