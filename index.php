<?php
session_start();
require_once 'db.php';

// Get featured products
$featuredProducts = getFeaturedProducts($conn, 8);

// Get latest products
$latestProducts = getLatestProducts($conn, 8);

// Get main categories with images for display
$mainCategories = getMainCategories($conn);
?>

<?php include 'header.php'; ?>

<!-- Hero Banner Slideshow -->
<section class="relative bg-gray-900 overflow-hidden">
    <div class="slideshow-container max-h-96">
        <div class="slide fade block">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1500&h=500&q=80" 
                     alt="Electronics" class="w-full object-cover h-96">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent flex items-center">
                    <div class="container mx-auto px-4">
                        <div class="max-w-lg text-white">
                            <h2 class="text-4xl font-bold mb-4">Latest Electronics & Gadgets</h2>
                            <p class="mb-6">Find the best deals on the newest tech products.</p>
                            <a href="categories.php?slug=electronics" 
                               class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="slide fade hidden">
            <div class="relative">
                <img src="https://images.unsplash.com/3/www.madebyvadim.com.jpg?ixlib=rb-1.2.1&auto=format&fit=crop&w=1500&h=500&q=80" 
                     alt="Fashion" class="w-full object-cover h-96">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent flex items-center">
                    <div class="container mx-auto px-4">
                        <div class="max-w-lg text-white">
                            <h2 class="text-4xl font-bold mb-4">Trendy Fashion & Clothing</h2>
                            <p class="mb-6">Shop the latest trends at the best prices.</p>
                            <a href="categories.php?slug=fashion" 
                               class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="slide fade hidden">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1524634126442-357e0eac3c14?ixlib=rb-1.2.1&auto=format&fit=crop&w=1500&h=500&q=80" 
                     alt="Home & Kitchen" class="w-full object-cover h-96">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent flex items-center">
                    <div class="container mx-auto px-4">
                        <div class="max-w-lg text-white">
                            <h2 class="text-4xl font-bold mb-4">Home & Kitchen Essentials</h2>
                            <p class="mb-6">Enhance your living space with our premium collection.</p>
                            <a href="categories.php?slug=home-kitchen" 
                               class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-10">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Shop by Categories</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach($mainCategories as $category): ?>
                <a href="categories.php?slug=<?= htmlspecialchars($category['slug']) ?>" 
                   class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105 group">
                    <div class="h-32 bg-gray-200 flex items-center justify-center">
                        <?php
                        // Default image based on category name for demo
                        $categoryImage = "https://via.placeholder.com/200x200.png?text=" . urlencode($category['name']);
                        
                        // Use specific images for main categories if available
                        if ($category['slug'] == 'electronics') {
                            $categoryImage = "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&h=200&q=80";
                        } elseif ($category['slug'] == 'fashion') {
                            $categoryImage = "https://images.unsplash.com/photo-1526948128573-703ee1aeb6fa?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&h=200&q=80";
                        } elseif ($category['slug'] == 'home-kitchen') {
                            $categoryImage = "https://images.unsplash.com/photo-1524634126442-357e0eac3c14?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&h=200&q=80";
                        }
                        ?>
                        <img src="<?= $categoryImage ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="max-h-full max-w-full">
                    </div>
                    <div class="p-3 text-center group-hover:bg-jumia-orange group-hover:text-white transition-colors">
                        <h3 class="font-medium"><?= htmlspecialchars($category['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Featured Products</h2>
            <a href="products.php?featured=1" class="text-jumia-orange hover:underline font-medium flex items-center">
                View All <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach($featuredProducts as $product): ?>
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
                            
                            <!-- Star rating placeholder -->
                            <div class="flex items-center text-yellow-400 text-sm mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1 text-gray-500">(4.5)</span>
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

<!-- Flash Deals Banner -->
<section class="py-10">
    <div class="container mx-auto px-4">
        <div class="bg-jumia-blue rounded-lg overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                    <h2 class="text-white text-3xl font-bold mb-4">Flash Deals</h2>
                    <p class="text-blue-100 mb-6">Limited time offers on top brands. Hurry up while stocks last!</p>
                    <div class="flex space-x-4 mb-8">
                        <div class="bg-white rounded p-3 text-center w-16">
                            <div class="text-2xl font-bold text-jumia-blue">24</div>
                            <div class="text-xs text-gray-600">Hours</div>
                        </div>
                        <div class="bg-white rounded p-3 text-center w-16">
                            <div class="text-2xl font-bold text-jumia-blue">18</div>
                            <div class="text-xs text-gray-600">Mins</div>
                        </div>
                        <div class="bg-white rounded p-3 text-center w-16">
                            <div class="text-2xl font-bold text-jumia-blue">45</div>
                            <div class="text-xs text-gray-600">Secs</div>
                        </div>
                    </div>
                    <a href="products.php?sale=1" class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block self-start">
                        Shop Now
                    </a>
                </div>
                <div class="md:w-1/2 bg-blue-800 p-6 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&h=400&q=80" 
                         alt="Flash Deals" class="rounded-lg max-h-full max-w-full">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Latest Products</h2>
            <a href="products.php" class="text-jumia-orange hover:underline font-medium flex items-center">
                View All <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach($latestProducts as $product): ?>
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
                            
                            <!-- Star rating placeholder -->
                            <div class="flex items-center text-yellow-400 text-sm mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="ml-1 text-gray-500">(4.5)</span>
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

<!-- Brand Showcase -->
<section class="py-10">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Shop by Brand</h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">Apple</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">Samsung</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">Nike</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">Adidas</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">Sony</span>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-center h-24 transition-transform hover:scale-105">
                <span class="text-xl font-bold">LG</span>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews -->
<section class="py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">What Our Customers Say</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center text-yellow-400 mb-4">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 mb-4">"Excellent quality products and great customer service! I've been shopping here for years and never been disappointed."</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div>
                        <h4 class="font-semibold">John Doe</h4>
                        <p class="text-sm text-gray-500">Loyal Customer</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center text-yellow-400 mb-4">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 mb-4">"Fast delivery and amazing deals! Will definitely shop again. The product quality exceeded my expectations."</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div>
                        <h4 class="font-semibold">Sarah Smith</h4>
                        <p class="text-sm text-gray-500">New Customer</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center text-yellow-400 mb-4">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p class="text-gray-600 mb-4">"The best shopping experience I have ever had! User-friendly website and the customer support is always helpful."</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div>
                        <h4 class="font-semibold">Michael Johnson</h4>
                        <p class="text-sm text-gray-500">Regular Shopper</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
