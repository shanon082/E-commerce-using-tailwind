<?php
session_start();
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!checkUserSession()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to update your cart.'
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
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

// Validate product ID and quantity
if ($productId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID.'
    ]);
    exit;
}

// Get the cart item
$stmt = $conn->prepare("SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.sale_price, p.stock, p.image 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? AND c.product_id = ?");
$stmt->execute([$userId, $productId]);
$cartItem = $stmt->fetch();

if (!$cartItem) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found in your cart.'
    ]);
    exit;
}

try {
    // If quantity is 0 or less, remove the item from cart
    if ($quantity <= 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartItem['id'], $userId]);
        
        $message = 'Product removed from cart.';
    } else {
        // Make sure we don't exceed available stock
        if ($quantity > $cartItem['stock']) {
            $quantity = $cartItem['stock'];
            $message = 'Quantity updated to maximum available stock (' . $cartItem['stock'] . ').';
        } else {
            $message = 'Cart updated successfully.';
        }
        
        // Update cart quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cartItem['id'], $userId]);
    }
    
    // Calculate the item's subtotal
    $price = $cartItem['sale_price'] ? $cartItem['sale_price'] : $cartItem['price'];
    $itemTotal = $price * $quantity;
    
    // Get updated cart total
    $cartTotal = getCartTotal($conn, $userId);
    
    // Calculate shipping, tax, and final total for display
    $shipping = $cartTotal > 50 ? 0 : 5.99;
    $tax = $cartTotal * 0.1; // 10% tax
    $finalTotal = $cartTotal + $shipping + $tax;
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'itemTotal' => formatPrice($itemTotal),
        'total' => formatPrice($finalTotal),
        'cartTotal' => formatPrice($cartTotal),
        'shipping' => $shipping > 0 ? formatPrice($shipping) : 'Free',
        'tax' => formatPrice($tax)
    ]);
} catch (PDOException $e) {
    error_log('Update cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating your cart. Please try again.'
    ]);
}
