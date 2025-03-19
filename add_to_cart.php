<?php
session_start();
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!checkUserSession()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to add items to your cart.',
        'redirect' => 'login_and_signup/login.php'
    ]);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get user data
$userData = getUserData($conn);
$userId = $userData['id'];

// Get product ID and quantity from POST data
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate product ID and quantity
if ($productId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID.'
    ]);
    exit;
}

if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantity must be greater than zero.'
    ]);
    exit;
}

// Check if product exists and has enough stock
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found.'
    ]);
    exit;
}

if ($product['stock'] < $quantity) {
    echo json_encode([
        'success' => false,
        'message' => 'Not enough stock available. Only ' . $product['stock'] . ' item(s) left.'
    ]);
    exit;
}

try {
    // Check if the product is already in the cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $cartItem = $stmt->fetch();
    
    if ($cartItem) {
        // Update quantity
        $newQuantity = $cartItem['quantity'] + $quantity;
        
        // Make sure we don't exceed available stock
        if ($newQuantity > $product['stock']) {
            $newQuantity = $product['stock'];
            $message = 'Cart updated to maximum available stock (' . $product['stock'] . ').';
        } else {
            $message = 'Product quantity updated in cart.';
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $cartItem['id']]);
    } else {
        // Add new cart item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity]);
        $message = 'Product added to cart.';
    }
    
    // Get updated cart count
    $cartCount = getCartCount($conn, $userId);
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cartCount' => $cartCount
    ]);
} catch (PDOException $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding the product to cart. Please try again.'
    ]);
}
