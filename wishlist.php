<?php
session_start();
require_once 'db.php';

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Remove item from wishlist
if (isset($_GET['remove_item']) && isset($_SESSION['wishlist'][$_GET['remove_item']])) {
    unset($_SESSION['wishlist'][$_GET['remove_item']]);
    header('Location: wishlist.php');
    exit;
}

// Move item back to cart
if (isset($_GET['move_to_cart']) && isset($_SESSION['wishlist'][$_GET['move_to_cart']])) {
    $productId = $_GET['move_to_cart'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Add the item back to the cart
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = $_SESSION['wishlist'][$productId];
    }
    unset($_SESSION['wishlist'][$productId]);
    header('Location: wishlist.php');
    exit;
}

// Ensure product details are fetched correctly for wishlist items
foreach ($_SESSION['wishlist'] as $productId => &$item) {
    if (!isset($item['name']) || !isset($item['price']) || !isset($item['image'])) {
        $stmt = $conn->prepare("SELECT name, price, image_url AS image FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($productDetails) {
            $item = array_merge($item, $productDetails);
        }
    }
}
unset($item); // Break reference to avoid unexpected behavior
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">My Wishlist</h1>

        <?php if (empty($_SESSION['wishlist'])): ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="far fa-heart"></i>
                </div>
                <h2 class="text-xl font-medium mb-4">Your wishlist is empty</h2>
                <p class="text-gray-600 mb-6">Save your favorite products here for later.</p>
                <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-md inline-block transition">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($_SESSION['wishlist'] as $product_id => $item): ?>
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                        <a href="product_description.php?id=<?php echo $product_id; ?>">
                            <img 
                                src="<?php echo htmlspecialchars($item['image']); ?>" 
                                alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                class="w-full h-40 object-cover"
                            >
                            <div class="p-3">
                                <h3 class="text-sm font-medium truncate"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="flex items-center mt-1">
                                    <span class="text-blue-500 font-bold">$<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                        </a>
                        <div class="px-3 pb-3 flex justify-between">
                            <a 
                                href="wishlist.php?remove_item=<?php echo $product_id; ?>" 
                                class="text-red-500 hover:text-red-700 text-sm flex items-center"
                            >
                                <i class="fas fa-trash-alt mr-1"></i> Remove
                            </a>
                            <a 
                                href="wishlist.php?move_to_cart=<?php echo $product_id; ?>" 
                                class="text-blue-500 hover:text-blue-700 text-sm flex items-center"
                            >
                                <i class="fas fa-shopping-cart mr-1"></i> Move to Cart
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
