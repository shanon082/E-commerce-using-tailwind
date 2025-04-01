<?php
session_start();
require_once 'db.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    // Redirect to home page if invalid product ID
    header('Location: index.php');
    exit;
}

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = :id");
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$product = $stmt->fetch();

if (!$product) {
    // Redirect to home page if product not found
    header('Location: index.php');
    exit;
}

// Fetch product reviews
$stmt = $conn->prepare("SELECT r.*, u.username 
                       FROM reviews r 
                       LEFT JOIN users u ON r.user_id = u.id 
                       WHERE r.product_id = :product_id 
                       ORDER BY r.created_at DESC");
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();
$reviews = $stmt->fetchAll();

// Calculate average rating
$avgRating = 0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $totalRating = 0;
    foreach ($reviews as $review) {
        $totalRating += $review['rating'];
    }
    $avgRating = $totalRating / $reviewCount;
}

// Fetch similar products (same category)
$stmt = $conn->prepare("SELECT * FROM products 
                       WHERE category_id = :category_id AND id != :id 
                       ORDER BY rating DESC 
                       LIMIT 4");
$stmt->bindParam(':category_id', $product['category_id']);
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$similarProducts = $stmt->fetchAll();

// Add to cart functionality
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($quantity > 0) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image_url'],
                'quantity' => $quantity
            ];
        }
        
        // Redirect to the same page to prevent form resubmission
        header('Location: product_description.php?id=' . $product_id . '&added=1');
        exit;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $reviewError = "You must be logged in to submit a review.";
    } else {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if ($rating < 1 || $rating > 5) {
            $reviewError = "Please provide a valid rating between 1 and 5.";
        } elseif (empty($comment)) {
            $reviewError = "Please provide a comment for your review.";
        } else {
            // Insert review into the database
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
                                    VALUES (:product_id, :user_id, :rating, :comment, NOW())");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':comment', $comment);

            if ($stmt->execute()) {
                $reviewSuccess = "Your review has been submitted successfully.";
                // Refresh reviews
                $stmt = $conn->prepare("SELECT r.*, u.username 
                                       FROM reviews r 
                                       LEFT JOIN users u ON r.user_id = u.id 
                                       WHERE r.product_id = :product_id 
                                       ORDER BY r.created_at DESC");
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
                $reviews = $stmt->fetchAll();

                // Recalculate average rating
                $reviewCount = count($reviews);
                $totalRating = array_sum(array_column($reviews, 'rating'));
                $avgRating = $reviewCount > 0 ? $totalRating / $reviewCount : 0;
            } else {
                $reviewError = "An error occurred while submitting your review. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
    <!-- Additional meta tags for SEO -->
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?> - TUKOLE Business">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 160)); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($product['image_url']); ?>">
    <meta property="og:type" content="product">
    <!-- Custom styles for product page -->
    <style>
        /* Zoom effect for product image on hover */
        .product-image-container:hover img {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        
        /* Better styling for tabs */
        .tab-btn.active {
            color: #3b82f6;
            border-bottom: 2px solid #3b82f6;
        }
        
        /* Review stars hover effect */
        .rating-stars i:hover ~ i {
            color: #d1d5db;
        }
        
        /* Image gallery thumbnails */
        .thumbnail-item.active {
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <!-- PHP backend integration will handle:
    1. Product data fetching from database
    2. Reviews system with user authentication
    3. Add to cart functionality with session management
    4. Related/similar products recommendations
    5. Inventory tracking and stock updates
    -->

    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb Navigation -->
        <div class="flex items-center text-sm text-gray-500 mb-6">
            <a href="index.php" class="hover:text-blue-500">Home</a>
            <span class="mx-2">/</span>
            <a href="category.php?id=<?php echo $product['category_id']; ?>" class="hover:text-blue-500">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-800"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <!-- Success Message for Added to Cart -->
        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"> Product added to your cart.</span>
                <a href="cart.php" class="underline ml-2">View Cart</a>
            </div>
        <?php endif; ?>

        <!-- Product Details -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="flex flex-col md:flex-row">
                <!-- Product Image -->
                <div class="md:w-1/2 p-6">
                    <div class="bg-gray-100 rounded-lg p-4 flex items-center justify-center">
                        <img 
                            src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($product['name']); ?>" 
                            class="max-w-full max-h-96 object-contain"
                        >
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="md:w-1/2 p-6">
                    <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= round($avgRating)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $avgRating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="text-gray-600">(<?php echo $reviewCount; ?> reviews)</span>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-2xl font-bold text-blue-500">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                            <span class="ml-2 text-gray-500 line-through">$<?php echo number_format($product['old_price'], 2); ?></span>
                            <?php 
                                $discount = round(($product['old_price'] - $product['price']) / $product['old_price'] * 100);
                                echo '<span class="ml-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">SAVE ' . $discount . '%</span>';
                            ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </p>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <div class="mb-4">
                            <span class="text-green-600 flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> In Stock (<?php echo $product['stock']; ?> available)
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <span class="text-red-600 flex items-center">
                                <i class="fas fa-times-circle mr-2"></i> Out of Stock
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <form action="product_description.php?id=<?php echo $product_id; ?>" method="post" class="mb-6">
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-4">Quantity:</label>
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button type="button" class="px-3 py-1 bg-gray-100" onclick="decrementQuantity()">-</button>
                                <input 
                                    id="quantity" 
                                    name="quantity" 
                                    type="number" 
                                    min="1" 
                                    max="<?php echo $product['stock']; ?>" 
                                    value="1" 
                                    class="w-16 text-center border-none focus:outline-none py-1"
                                >
                                <button type="button" class="px-3 py-1 bg-gray-100" onclick="incrementQuantity(<?php echo $product['stock']; ?>)">+</button>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <button 
                                type="submit" 
                                name="add_to_cart" 
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition <?php echo $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                            >
                                <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                            </button>
                            
                            <button 
                                type="button" 
                                class="border border-blue-500 text-blue-500 hover:bg-blue-50 py-2 px-4 rounded-md transition"
                            >
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button id="tab-description" class="tab-btn text-blue-500 border-b-2 border-blue-500 py-4 px-6 font-medium">Description</button>
                    <button id="tab-specifications" class="tab-btn text-gray-500 py-4 px-6 font-medium">Specifications</button>
                    <button id="tab-reviews" class="tab-btn text-gray-500 py-4 px-6 font-medium">Reviews (<?php echo $reviewCount; ?>)</button>
                </nav>
            </div>
            
            <div id="tab-content-description" class="tab-content p-6">
                <h2 class="text-xl font-bold mb-4">Product Description</h2>
                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            
            <div id="tab-content-specifications" class="tab-content p-6 hidden">
                <h2 class="text-xl font-bold mb-4">Product Specifications</h2>
                <table class="w-full border-collapse">
                    <tbody>
                        <tr class="border-b">
                            <td class="py-2 font-medium">Brand</td>
                            <td>TUKOLE Brand</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 font-medium">Model</td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 font-medium">Weight</td>
                            <td>0.5 kg</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 font-medium">Dimensions</td>
                            <td>20 x 15 x 5 cm</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 font-medium">Category</td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div id="tab-content-reviews" class="tab-content p-6 hidden">
                <h2 class="text-xl font-bold mb-4">Customer Reviews</h2>

                <!-- Display success or error messages -->
                <?php if (isset($reviewSuccess)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 rounded relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline"><?php echo $reviewSuccess; ?></span>
                    </div>
                <?php elseif (isset($reviewError)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo $reviewError; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Review Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium mb-4">Write a Review</h3>
                    <form action="product_description.php?id=<?php echo $product_id; ?>" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <div class="mb-4">
                            <label class="block mb-2">Your Rating</label>
                            <div class="flex text-2xl text-gray-300 rating-stars">
                                <i class="far fa-star cursor-pointer" data-rating="1"></i>
                                <i class="far fa-star cursor-pointer" data-rating="2"></i>
                                <i class="far fa-star cursor-pointer" data-rating="3"></i>
                                <i class="far fa-star cursor-pointer" data-rating="4"></i>
                                <i class="far fa-star cursor-pointer" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="rating-value" value="0">
                        </div>
                        
                        <div class="mb-4">
                            <label for="review-comment" class="block mb-2">Your Review</label>
                            <textarea 
                                id="review-comment" 
                                name="comment" 
                                rows="4" 
                                class="w-full border border-gray-300 rounded-md px-4 py-2"
                                placeholder="Share your experience with this product..."
                            ></textarea>
                        </div>
                        
                        <button type="submit" name="submit_review" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition">
                            Submit Review
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="mb-8 bg-gray-50 p-4 rounded-lg text-center">
                    <p class="mb-2">Please <a href="login_and_signup/login.php" class="text-blue-500 hover:underline">login</a> to write a review.</p>
                </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-gray-200 py-4">
                        <div class="flex items-center mb-2">
                            <div class="font-medium mr-2"><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></div>
                            <div class="flex text-yellow-400 text-sm">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $review['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div class="text-xs text-gray-500 ml-2">
                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                            </div>
                        </div>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-gray-500 italic">No reviews yet. Be the first to review this product!</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Similar Products -->
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Similar Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($similarProducts as $similarProduct): ?>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <a href="product_description.php?id=<?php echo $similarProduct['id']; ?>">
                        <img 
                            src="<?php echo htmlspecialchars($similarProduct['image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($similarProduct['name']); ?>" 
                            class="w-full h-40 object-cover"
                        >
                        <div class="p-3">
                            <h3 class="text-sm font-medium truncate"><?php echo htmlspecialchars($similarProduct['name']); ?></h3>
                            <div class="flex items-center mt-1">
                                <span class="text-blue-500 font-bold">$<?php echo number_format($similarProduct['price'], 2); ?></span>
                                <?php if ($similarProduct['old_price'] > $similarProduct['price']): ?>
                                    <span class="ml-2 text-gray-400 text-xs line-through">$<?php echo number_format($similarProduct['old_price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center mt-1 text-yellow-400 text-xs">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $similarProduct['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $similarProduct['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </a>
                    <div class="px-3 pb-3">
                        <form action="product_description.php" method="get">
                            <input type="hidden" name="id" value="<?php echo $similarProduct['id']; ?>">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 rounded transition">
                                View Product
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Quantity input handling
        function decrementQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }
        
        function incrementQuantity(maxStock) {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            if (currentValue < maxStock) {
                input.value = currentValue + 1;
            }
        }
        
        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('text-blue-500', 'border-b-2', 'border-blue-500');
                    btn.classList.add('text-gray-500');
                });
                
                // Add active class to clicked button
                button.classList.remove('text-gray-500');
                button.classList.add('text-blue-500', 'border-b-2', 'border-blue-500');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show the corresponding tab content
                const tabId = button.id.replace('tab-', 'tab-content-');
                document.getElementById(tabId).classList.remove('hidden');
            });
        });
        
        // Rating stars
        const stars = document.querySelectorAll('.rating-stars .fa-star');
        const ratingInput = document.getElementById('rating-value');
        
        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.getAttribute('data-rating'));
                
                // Reset all stars
                stars.forEach(s => {
                    s.classList.remove('fas', 'text-yellow-400');
                    s.classList.add('far', 'text-gray-300');
                });
                
                // Fill stars up to the hovered one
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= rating) {
                        s.classList.remove('far', 'text-gray-300');
                        s.classList.add('fas', 'text-yellow-400');
                    }
                });
            });
            
            star.addEventListener('click', () => {
                const rating = parseInt(star.getAttribute('data-rating'));
                ratingInput.value = rating;
            });
        });
        
        // Reset stars when mouse leaves the container
        document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
            const currentRating = parseInt(ratingInput.value);
            
            // Reset all stars
            stars.forEach(s => {
                s.classList.remove('fas', 'text-yellow-400');
                s.classList.add('far', 'text-gray-300');
            });
            
            // Fill stars up to the selected rating
            if (currentRating > 0) {
                stars.forEach(s => {
                    const starRating = parseInt(s.getAttribute('data-rating'));
                    if (starRating <= currentRating) {
                        s.classList.remove('far', 'text-gray-300');
                        s.classList.add('fas', 'text-yellow-400');
                    }
                });
            }
        });
    </script>
</body>
</html>
