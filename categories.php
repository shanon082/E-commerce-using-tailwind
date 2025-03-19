<?php
session_start();
require_once 'db.php';

// Get category slug from URL
$categorySlug = isset($_GET['slug']) ? $_GET['slug'] : '';

// If no slug is provided, redirect to homepage
if (empty($categorySlug)) {
    header("Location: index.php");
    exit;
}

// Get category information
$stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$categorySlug]);
$category = $stmt->fetch();

// If category doesn't exist, redirect to homepage
if (!$category) {
    header("Location: index.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get products for this category
$products = getProductsByCategory($conn, $category['id'], $perPage, $offset);

// Get total products count for pagination
$stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? OR category_id IN (SELECT id FROM categories WHERE parent_id = ?)");
$stmt->execute([$category['id'], $category['id']]);
$totalProducts = $stmt->fetchColumn();

$totalPages = ceil($totalProducts / $perPage);

// Get subcategories if this is a parent category
$subcategories = [];
if ($category['parent_id'] === null) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
    $stmt->execute([$category['id']]);
    $subcategories = $stmt->fetchAll();
}

// Get breadcrumb data
$breadcrumb = [
    ['name' => 'Home', 'url' => 'index.php']
];

if (isset($category['parent_id']) && $category['parent_id'] !== null) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category['parent_id']]);
    $parentCategory = $stmt->fetch();
    
    if ($parentCategory) {
        $breadcrumb[] = [
            'name' => $parentCategory['name'],
            'url' => 'categories.php?slug=' . $parentCategory['slug']
        ];
    }
}

$breadcrumb[] = ['name' => $category['name'], 'url' => ''];
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <?php foreach ($breadcrumb as $index => $item): ?>
                <?php if (!empty($item['url'])): ?>
                    <a href="<?= $item['url'] ?>" class="text-gray-600 hover:text-jumia-orange">
                        <?= htmlspecialchars($item['name']) ?>
                    </a>
                <?php else: ?>
                    <span class="text-gray-800 font-medium">
                        <?= htmlspecialchars($item['name']) ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="mx-2 text-gray-400">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<!-- Category Header -->
<section class="py-6 bg-gradient-to-r from-jumia-blue to-blue-800 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($category['name']) ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="text-blue-100"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Subcategories (if this is a parent category) -->
<?php if (!empty($subcategories)): ?>
    <section class="py-6 bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Shop by Category</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach($subcategories as $subcat): ?>
                    <a href="categories.php?slug=<?= htmlspecialchars($subcat['slug']) ?>" 
                       class="bg-gray-100 rounded-lg overflow-hidden transition-transform hover:scale-105 group">
                        <div class="h-20 flex items-center justify-center bg-gray-200">
                            <?php if (!empty($subcat['image'])): ?>
                                <img src="<?= htmlspecialchars($subcat['image']) ?>" alt="<?= htmlspecialchars($subcat['name']) ?>" class="max-h-full max-w-full">
                            <?php else: ?>
                                <span class="text-gray-400 text-4xl">
                                    <i class="fas fa-folder"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-2 text-center group-hover:bg-jumia-orange group-hover:text-white transition-colors">
                            <h3 class="text-sm font-medium"><?= htmlspecialchars($subcat['name']) ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Products Grid -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <!-- Product count and sorting options -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <p class="text-gray-600 mb-4 sm:mb-0">
                <?= $totalProducts ?> product<?= $totalProducts != 1 ? 's' : '' ?> found
            </p>
            
            <div class="flex items-center">
                <label for="sort-by" class="text-gray-600 mr-2">Sort by:</label>
                <select id="sort-by" class="border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                    <option value="newest">Newest</option>
                    <option value="price-asc">Price (Low to High)</option>
                    <option value="price-desc">Price (High to Low)</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                </select>
            </div>
        </div>
        
        <?php if (count($products) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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
                            <a href="?slug=<?= urlencode($categorySlug) ?>&page=<?= $page - 1 ?>" 
                               class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <a href="?slug=<?= urlencode($categorySlug) ?>&page=<?= $i ?>" 
                               class="px-3 py-1 rounded-md border <?= $i == $page ? 'bg-jumia-orange text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?slug=<?= urlencode($categorySlug) ?>&page=<?= $page + 1 ?>" 
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
                <p class="text-gray-600 mb-6">We couldn't find any products in this category at the moment.</p>
                <a href="index.php" class="bg-jumia-orange text-white px-6 py-3 rounded-md font-semibold hover:bg-orange-600 transition-colors inline-block">
                    Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Category Description (if available) -->
<?php if (!empty($category['description'])): ?>
    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">About <?= htmlspecialchars($category['name']) ?></h2>
            <div class="prose max-w-none text-gray-600">
                <p><?= nl2br(htmlspecialchars($category['description'])) ?></p>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Related Categories -->
<?php
// Get sibling categories for related categories
if ($category['parent_id'] !== null) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? AND id != ? ORDER BY name LIMIT 6");
    $stmt->execute([$category['parent_id'], $category['id']]);
    $relatedCategories = $stmt->fetchAll();
} else {
    // If this is a parent category, show other main categories
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL AND id != ? ORDER BY name LIMIT 6");
    $stmt->execute([$category['id']]);
    $relatedCategories = $stmt->fetchAll();
}

if (count($relatedCategories) > 0):
?>
    <section class="py-8 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Related Categories</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                <?php foreach($relatedCategories as $relCat): ?>
                    <a href="categories.php?slug=<?= htmlspecialchars($relCat['slug']) ?>" 
                       class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:scale-105 group border border-gray-200">
                        <div class="p-4 text-center">
                            <h3 class="text-sm font-medium text-gray-800 group-hover:text-jumia-orange transition-colors">
                                <?= htmlspecialchars($relCat['name']) ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

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
    
    // Set the current sort value in the dropdown
    const urlParams = new URLSearchParams(window.location.search);
    const sortParam = urlParams.get('sort');
    if (sortParam) {
        document.getElementById('sort-by').value = sortParam;
    }
</script>

<?php include 'footer.php'; ?>
