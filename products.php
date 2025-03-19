<?php
session_start();
require_once 'db.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Filters
$featured = isset($_GET['featured']) && $_GET['featured'] == '1';
$sale = isset($_GET['sale']) && $_GET['sale'] == '1';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build the query
$params = [];
$whereClause = [];

if ($featured) {
    $whereClause[] = "p.featured = 1";
}

if ($sale) {
    $whereClause[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price";
}

$whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';

// Add sorting
$orderBy = 'p.created_at DESC'; // default is newest
if ($sort === 'price-asc') {
    $orderBy = 'COALESCE(p.sale_price, p.price) ASC';
} elseif ($sort === 'price-desc') {
    $orderBy = 'COALESCE(p.sale_price, p.price) DESC';
} elseif ($sort === 'name-asc') {
    $orderBy = 'p.name ASC';
} elseif ($sort === 'name-desc') {
    $orderBy = 'p.name DESC';
}

// Get total products count for pagination
$countQuery = "SELECT COUNT(*) FROM products p $whereStr";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

$totalPages = ceil($totalProducts / $perPage);

// Get products with pagination
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $whereStr 
          ORDER BY $orderBy 
          LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($query);
$stmt->execute($allParams);
$products = $stmt->fetchAll();

// Page title
$pageTitle = "All Products";
if ($featured) {
    $pageTitle = "Featured Products";
} elseif ($sale) {
    $pageTitle = "Sale Products";
}

// Get all categories for filter
$stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium"><?= htmlspecialchars($pageTitle) ?></span>
        </nav>
    </div>
</div>

<!-- Products Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($pageTitle) ?></h1>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">Filters</h2>
                    
                    <form action="products.php" method="get" id="filter-form">
                        <!-- Keep existing parameters -->
                        <?php if ($featured): ?>
                            <input type="hidden" name="featured" value="1">
                        <?php endif; ?>
                        
                        <?php if ($sale): ?>
                            <input type="hidden" name="sale" value="1">
                        <?php endif; ?>
                        
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        
                        <!-- Categories -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-700 mb-2">Categories</h3>
                            <div class="space-y-2">
                                <?php foreach ($categories as $category): ?>
                                    <div>
                                        <a href="categories.php?slug=<?= $category['slug'] ?>" class="text-gray-600 hover:text-jumia-orange transition-colors">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-700 mb-2">Price Range</h3>
                            <div class="flex space-x-2">
                                <input type="number" name="min_price" placeholder="Min" min="0" 
                                       class="w-1/2 border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-1 focus:ring-orange-500 text-sm"
                                       value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                                <input type="number" name="max_price" placeholder="Max" min="0" 
                                       class="w-1/2 border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-1 focus:ring-orange-500 text-sm"
                                       value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                            </div>
                        </div>
                        
                        <!-- Special Filters -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-700 mb-2">Special Offers</h3>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="on_sale" name="sale" value="1" 
                                           <?= $sale ? 'checked' : '' ?> 
                                           class="h-4 w-4 text-jumia-orange focus:ring-jumia-orange border-gray-300 rounded">
                                    <label for="on_sale" class="ml-2 text-sm text-gray-600">
                                        On Sale
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="featured_products" name="featured" value="1" 
                                           <?= $featured ? 'checked' : '' ?> 
                                           class="h-4 w-4 text-jumia-orange focus:ring-jumia-orange border-gray-300 rounded">
                                    <label for="featured_products" class="ml-2 text-sm text-gray-600">
                                        Featured Products
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-jumia-orange text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors text-sm font-medium">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <!-- Product count and sorting options -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 bg-white rounded-lg shadow-md p-4">
                    <p class="text-gray-600 mb-4 sm:mb-0">
                        <?= $totalProducts ?> product<?= $totalProducts != 1 ? 's' : '' ?> found
                    </p>
                    
                    <div class="flex items-center">
                        <label for="sort-by" class="text-gray-600 mr-2">Sort by:</label>
                        <select id="sort-by" class="border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                            <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                            <option value="name-asc" <?= $sort === 'name-asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                            <option value="name-desc" <?= $sort === 'name-desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        </select>
                    </div>
                </div>
                
                <?php if (count($products) > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach($products as $product): ?>
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
                                        
                                        <?php if ($product['featured']): ?>
                                            <div class="absolute top-2 <?= $product['sale_price'] && $product['sale_price'] < $product['price'] ? 'right-2' : 'left-2' ?> bg-jumia-blue text-white text-xs font-bold px-2 py-1 rounded">
                                                FEATURED
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
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="mt-8 flex justify-center">
                            <nav class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?><?= $featured ? '&featured=1' : '' ?><?= $sale ? '&sale=1' : '' ?>&sort=<?= urlencode($sort) ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <a href="?page=<?= $i ?><?= $featured ? '&featured=1' : '' ?><?= $sale ? '&sale=1' : '' ?>&sort=<?= urlencode($sort) ?>" 
                                       class="px-3 py-1 rounded-md border <?= $i == $page ? 'bg-jumia-orange text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?><?= $featured ? '&featured=1' : '' ?><?= $sale ? '&sale=1' : '' ?>&sort=<?= urlencode($sort) ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-md p-8 text-center">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-box-open text-6xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">No products found</h2>
                        <p class="text-gray-600 mb-6">Try adjusting your filters or search criteria.</p>
                        <a href="products.php" class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Sort products functionality
    document.getElementById('sort-by').addEventListener('change', function() {
        const sortValue = this.value;
        const currentUrl = new URL(window.location.href);
        
        // Add or update the sort parameter
        currentUrl.searchParams.set('sort', sortValue);
        
        // Reset to page 1 when sorting changes
        currentUrl.searchParams.set('page', '1');
        
        window.location.href = currentUrl.toString();
    });
</script>

<?php include 'footer.php'; ?>
