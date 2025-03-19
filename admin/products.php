<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build the query
$params = [];
$whereClause = [];

if (!empty($search)) {
    $whereClause[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryId > 0) {
    $whereClause[] = "p.category_id = ?";
    $params[] = $categoryId;
}

$whereStr = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';

// Get total products count for pagination
$countQuery = "SELECT COUNT(*) FROM products p $whereStr";
$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

$totalPages = ceil($totalProducts / $perPage);

// Get products with pagination
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $whereStr 
          ORDER BY p.id DESC 
          LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($query);
$stmt->execute($allParams);
$products = $stmt->fetchAll();

// Get all categories for filter
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Handle delete product
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // Delete product images
            $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            
            // Delete the product
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            $_SESSION['success_message'] = "Product deleted successfully.";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error_message'] = "Failed to delete product. " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
    }
    
    // Redirect to refresh the page and prevent form resubmission
    header("Location: products.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Products</h1>
        <a href="add_product.php" class="bg-jumia-orange text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="products.php" method="get" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search products..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            
            <div class="w-full md:w-48">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="category" name="category" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $categoryId == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="self-end">
                <button type="submit" class="bg-jumia-blue text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                
                <?php if (!empty($search) || $categoryId > 0): ?>
                    <a href="products.php" class="ml-2 text-gray-600 hover:text-gray-800">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <?php if (count($products) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Featured
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $product['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md overflow-hidden">
                                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                 class="h-full w-full object-contain">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php 
                                                $shortDesc = substr($product['description'], 0, 50);
                                                if (strlen($product['description']) > 50) {
                                                    $shortDesc .= '...';
                                                }
                                                echo htmlspecialchars($shortDesc);
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($product['category_name'] ?: 'Uncategorized') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                        <div class="text-sm font-medium text-jumia-orange">
                                            <?= formatPrice($product['sale_price']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 line-through">
                                            <?= formatPrice($product['price']) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= formatPrice($product['price']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of stock
                                        </span>
                                    <?php elseif ($product['stock'] <= 5): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low: <?= $product['stock'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= $product['stock'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($product['featured']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-star mr-1"></i> Yes
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="../product_description.php?id=<?= $product['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="products.php" method="post" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="delete_product" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing 
                                <span class="font-medium"><?= $offset + 1 ?></span>
                                to 
                                <span class="font-medium"><?= min($offset + $perPage, $totalProducts) ?></span>
                                of 
                                <span class="font-medium"><?= $totalProducts ?></span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                                       class="px-3 py-1 rounded-md border <?= $i == $page ? 'bg-jumia-orange text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $categoryId ?>" 
                                       class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="p-6 text-center">
                <div class="text-gray-500 mb-4">
                    <i class="fas fa-box-open text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No products found</h3>
                <p class="text-gray-500 mb-4">
                    <?php if (!empty($search) || $categoryId > 0): ?>
                        Try adjusting your search or filter criteria.
                    <?php else: ?>
                        Start adding products to your store.
                    <?php endif; ?>
                </p>
                <a href="add_product.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-jumia-orange hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <i class="fas fa-plus mr-2"></i> Add Product
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
