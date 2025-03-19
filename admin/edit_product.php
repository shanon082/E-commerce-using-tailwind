<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: products.php");
    exit;
}

$productId = (int)$_GET['id'];

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error_message'] = "Product not found.";
    header("Location: products.php");
    exit;
}

// Get all categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $imageUrl = trim($_POST['image_url'] ?? '');
    
    // Generate slug from name if name has changed
    $slug = $product['slug'];
    if ($name !== $product['name']) {
        $slug = generateSlug($name);
        
        // Check if new slug already exists for another product
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $productId]);
        $slugExists = $stmt->fetchColumn();
        
        if ($slugExists) {
            $slug = $slug . '-' . uniqid();
        }
    }
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Product name is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Product description is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero';
    }
    
    if ($salePrice !== null && $salePrice >= $price) {
        $errors[] = 'Sale price must be less than regular price';
    }
    
    if ($stock < 0) {
        $errors[] = 'Stock cannot be negative';
    }
    
    if (empty($imageUrl)) {
        $errors[] = 'Product image URL is required';
    }
    
    // If no errors, update product
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE products SET 
                                  name = ?, 
                                  slug = ?, 
                                  description = ?, 
                                  price = ?, 
                                  sale_price = ?, 
                                  stock = ?, 
                                  category_id = ?, 
                                  image = ?, 
                                  featured = ? 
                                  WHERE id = ?");
            $stmt->execute([
                $name,
                $slug,
                $description,
                $price,
                $salePrice,
                $stock,
                $categoryId ?: null,
                $imageUrl,
                $featured,
                $productId
            ]);
            
            // Set success message
            $_SESSION['success_message'] = "Product updated successfully!";
            
            // Redirect to refresh the page with updated data
            header("Location: edit_product.php?id=$productId");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Error updating product: ' . $e->getMessage();
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Product</h1>
        <a href="products.php" class="text-jumia-orange hover:underline flex items-center">
            <i class="fas fa-arrow-left mr-1"></i> Back to Products
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc pl-4">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="edit_product.php?id=<?= $productId ?>" method="post" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="font-semibold text-lg text-gray-800">Product Information</h2>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name*</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($product['name']) ?>" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                           required>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                    <textarea id="description" name="description" rows="5" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                              required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="0">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="featured" name="featured" 
                           <?= $product['featured'] ? 'checked' : '' ?> 
                           class="h-4 w-4 text-jumia-orange focus:ring-jumia-orange border-gray-300 rounded">
                    <label for="featured" class="ml-2 block text-sm text-gray-700">
                        Featured Product
                    </label>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Product Image URL*</label>
                    <input type="url" id="image_url" name="image_url" 
                           value="<?= htmlspecialchars($product['image']) ?>" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                           required>
                    <p class="mt-1 text-xs text-gray-500">Enter a direct URL to the product image</p>
                    
                    <div class="mt-2 border border-gray-200 rounded-md overflow-hidden h-48 flex items-center justify-center bg-gray-100">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Preview" class="max-h-full max-w-full object-contain">
                    </div>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Regular Price*</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" id="price" name="price" min="0.01" step="0.01" 
                               value="<?= htmlspecialchars($product['price']) ?>" 
                               class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                               required>
                    </div>
                </div>
                
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" id="sale_price" name="sale_price" min="0.01" step="0.01" 
                               value="<?= $product['sale_price'] ? htmlspecialchars($product['sale_price']) : '' ?>" 
                               class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Leave empty for no sale price</p>
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity*</label>
                    <input type="number" id="stock" name="stock" min="0" 
                           value="<?= htmlspecialchars($product['stock']) ?>" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" 
                           required>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
            <a href="products.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-md font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </a>
            <div>
                <a href="../product_description.php?id=<?= $productId ?>" target="_blank" 
                   class="inline-block bg-jumia-blue text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition-colors mr-2">
                    View Product
                </a>
                <button type="submit" class="bg-jumia-orange text-white px-6 py-2 rounded-md font-semibold hover:bg-orange-600 transition-colors">
                    Update Product
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../footer.php'; ?>
