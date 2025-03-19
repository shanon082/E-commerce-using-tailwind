<?php
session_start();
require_once 'db.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = getProductById($conn, $productId);

// If product doesn't exist, redirect to homepage
if (!$product) {
    header("Location: index.php");
    exit;
}

// Get related products (products in the same category)
$relatedProducts = getProductsByCategory($conn, $product['category_id'], 4);

// Remove current product from related products (if present)
$relatedProducts = array_filter($relatedProducts, function($item) use ($productId) {
    return $item['id'] != $productId;
});

// Get random products if no related products are found
if (count($relatedProducts) < 3) {
    $latestProducts = getLatestProducts($conn, 4);
    $latestProducts = array_filter($latestProducts, function($item) use ($productId) {
        return $item['id'] != $productId;
    });
    $relatedProducts = array_merge($relatedProducts, $latestProducts);
    $relatedProducts = array_slice($relatedProducts, 0, 4);
}
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="categories.php?slug=<?= htmlspecialchars($product['category_slug']) ?>" class="text-gray-600 hover:text-jumia-orange">
                <?= htmlspecialchars($product['category_name']) ?>
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium"><?= htmlspecialchars($product['name']) ?></span>
        </nav>
    </div>
</div>

<!-- Product Details Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="md:flex">
                <!-- Product Images Gallery -->
                <div class="md:w-2/5 p-6 bg-gray-50">
                    <!-- Main Image -->
                    <div class="mb-4 border border-gray-200 rounded-lg bg-white flex items-center justify-center h-80">
                        <img id="main-product-image" src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="max-h-full max-w-full object-contain">
                    </div>
                    
                    <!-- Thumbnails -->
                    <div class="grid grid-cols-4 gap-2">
                        <div class="border border-orange-500 rounded-md overflow-hidden cursor-pointer product-thumbnail" data-image="<?= htmlspecialchars($product['image']) ?>">
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="Thumbnail 1" 
                                 class="w-full h-20 object-contain">
                        </div>
                        <!-- Additional thumbnails would go here, using sample images for now -->
                        <div class="border border-gray-200 rounded-md overflow-hidden cursor-pointer product-thumbnail" data-image="https://images.unsplash.com/photo-1611186871348-b1ce696e52c9">
                            <img src="https://images.unsplash.com/photo-1611186871348-b1ce696e52c9" 
                                 alt="Thumbnail 2" 
                                 class="w-full h-20 object-contain">
                        </div>
                        <div class="border border-gray-200 rounded-md overflow-hidden cursor-pointer product-thumbnail" data-image="https://images.unsplash.com/photo-1505740420928-5e560c06d30e">
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e" 
                                 alt="Thumbnail 3" 
                                 class="w-full h-20 object-contain">
                        </div>
                        <div class="border border-gray-200 rounded-md overflow-hidden cursor-pointer product-thumbnail" data-image="https://images.unsplash.com/photo-1699796990049-3406a9991baa">
                            <img src="https://images.unsplash.com/photo-1699796990049-3406a9991baa" 
                                 alt="Thumbnail 4" 
                                 class="w-full h-20 object-contain">
                        </div>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="md:w-3/5 p-6 border-l border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <!-- Brand & Rating -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm text-gray-600">Brand: <a href="#" class="text-jumia-orange hover:underline">Brand Name</a></div>
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="ml-1 text-sm text-gray-600">(4.5) Reviews</span>
                        </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-6">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <div class="flex items-center">
                                <span class="text-3xl font-bold text-jumia-orange"><?= formatPrice($product['sale_price']) ?></span>
                                <span class="ml-3 text-lg text-gray-500 line-through"><?= formatPrice($product['price']) ?></span>
                                <?php 
                                $discount = round(($product['price'] - $product['sale_price']) / $product['price'] * 100);
                                ?>
                                <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Save <?= $discount ?>%</span>
                            </div>
                        <?php else: ?>
                            <span class="text-3xl font-bold text-jumia-orange"><?= formatPrice($product['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-6">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="inline-block bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i> In Stock (<?= $product['stock'] ?> available)
                            </span>
                        <?php else: ?>
                            <span class="inline-block bg-red-100 text-red-800 text-sm font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-times-circle mr-1"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quantity and Add to Cart -->
                    <?php if ($product['stock'] > 0): ?>
                        <div class="mb-6">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity:</label>
                            <div class="flex items-center">
                                <div class="quantity flex border border-gray-300 rounded-md overflow-hidden w-32">
                                    <button type="button" class="quantity-decrease px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 focus:outline-none">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" min="1" max="<?= $product['stock'] ?>" value="1" class="quantity-input w-full text-center border-none focus:outline-none">
                                    <button type="button" class="quantity-increase px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 focus:outline-none">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button class="add-to-cart ml-4 bg-jumia-orange text-white px-6 py-2 rounded-md font-semibold hover:bg-orange-600 transition-colors flex items-center" data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Buy Now button -->
                    <?php if ($product['stock'] > 0): ?>
                        <div class="mb-6">
                            <a href="check-out.php?buy_now=<?= $product['id'] ?>" class="block text-center bg-jumia-blue text-white px-6 py-3 rounded-md font-semibold hover:bg-blue-700 transition-colors">
                                Buy Now
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Short Description -->
                    <div class="mb-6 text-sm">
                        <h3 class="font-semibold text-gray-800 mb-2">Highlights:</h3>
                        <div class="text-gray-600">
                            <p><?= htmlspecialchars($product['description']) ?></p>
                        </div>
                    </div>
                    
                    <!-- Delivery & Returns -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-md">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-truck mr-2 text-jumia-orange"></i> Delivery & Returns
                        </h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Free delivery on orders above $50</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Express delivery available (2-3 business days)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Easy returns within 15 days</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Share -->
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-700 mr-3">Share:</span>
                        <div class="flex space-x-2">
                            <a href="#" class="text-blue-600 hover:text-blue-800">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="text-blue-400 hover:text-blue-600">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="text-red-600 hover:text-red-800">
                                <i class="fab fa-pinterest"></i>
                            </a>
                            <a href="#" class="text-green-600 hover:text-green-800">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Description & Details Tabs -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <ul class="flex">
                    <li class="flex-1">
                        <a href="#description" class="block text-center py-4 px-6 font-semibold text-jumia-orange border-b-2 border-jumia-orange">
                            Description
                        </a>
                    </li>
                    <li class="flex-1">
                        <a href="#specifications" class="block text-center py-4 px-6 font-semibold text-gray-600 hover:text-jumia-orange">
                            Specifications
                        </a>
                    </li>
                    <li class="flex-1">
                        <a href="#reviews" class="block text-center py-4 px-6 font-semibold text-gray-600 hover:text-jumia-orange">
                            Reviews
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Description Tab -->
            <div id="description" class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Product Description</h2>
                <div class="prose text-gray-600">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                    
                    <!-- Adding more detailed description for demo purposes -->
                    <p class="mt-4">
                        <?= htmlspecialchars($product['name']) ?> is a high-quality product designed to meet your needs. 
                        With its sleek design and durable construction, it's perfect for everyday use.
                    </p>
                    
                    <h3 class="text-lg font-semibold mt-6 mb-2">Key Features:</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Premium quality materials ensuring durability</li>
                        <li>Sleek and modern design</li>
                        <li>Easy to use and maintain</li>
                        <li>Energy efficient</li>
                        <li>1-year manufacturer warranty</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">You May Also Like</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach(array_slice($relatedProducts, 0, 4) as $relatedProduct): ?>
                <div class="product-card group">
                    <a href="product_description.php?id=<?= $relatedProduct['id'] ?>" class="block product-image-hover">
                        <div class="relative h-48 bg-gray-200">
                            <img src="<?= htmlspecialchars($relatedProduct['image']) ?>" 
                                 alt="<?= htmlspecialchars($relatedProduct['name']) ?>" 
                                 class="w-full h-full object-contain">
                            
                            <?php if ($relatedProduct['sale_price'] && $relatedProduct['sale_price'] < $relatedProduct['price']): ?>
                                <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                    SALE
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-800 mb-1 group-hover:text-jumia-orange transition-colors line-clamp-2">
                                <?= htmlspecialchars($relatedProduct['name']) ?>
                            </h3>
                            
                            <div class="flex items-baseline mb-2">
                                <?php if ($relatedProduct['sale_price'] && $relatedProduct['sale_price'] < $relatedProduct['price']): ?>
                                    <span class="text-lg font-bold text-jumia-orange">
                                        <?= formatPrice($relatedProduct['sale_price']) ?>
                                    </span>
                                    <span class="ml-2 text-sm text-gray-500 line-through">
                                        <?= formatPrice($relatedProduct['price']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-jumia-orange">
                                        <?= formatPrice($relatedProduct['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    
                    <div class="px-4 pb-4 mt-auto">
                        <button class="add-to-cart w-full bg-jumia-orange text-white py-2 rounded-md hover:bg-orange-600 transition-colors flex items-center justify-center" data-product-id="<?= $relatedProduct['id'] ?>">
                            <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
