<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login_and_signup/login.php');
    exit;
}

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)));
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $image_url = trim($_POST['image_url']);
    $stock = intval($_POST['stock']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    
    if (!empty($old_price) && $old_price <= $price) {
        $errors[] = "Old price must be greater than current price";
    }
    
    if (empty($image_url)) {
        $errors[] = "Image URL is required";
    }
    
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative";
    }
    
    if (empty($errors)) {
        try {
            // Insert the product
            $stmt = $conn->prepare("INSERT INTO products (
                category_id, name, slug, description, price, old_price, image_url, stock, featured, created_at
            ) VALUES (
                :category_id, :name, :slug, :description, :price, :old_price, :image_url, :stock, :featured, NOW()
            )");
            
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':old_price', $old_price);
            $stmt->bindParam(':image_url', $image_url);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':featured', $featured);
            
            $stmt->execute();
            
            // Redirect to products page with success message
            $_SESSION['success'] = "Product added successfully.";
            header('Location: products.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - TUKOLE Business</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex items-center mb-6">
                <a href="products.php" class="text-blue-500 hover:underline mr-2">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <h1 class="text-2xl font-bold">Add New Product</h1>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                    <strong class="font-bold">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form action="add_product.php" method="post" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>
                        
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <select 
                                id="category_id" 
                                name="category_id" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($) <span class="text-red-500">*</span></label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                                step="0.01" 
                                min="0.01" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>
                        
                        <div>
                            <label for="old_price" class="block text-sm font-medium text-gray-700 mb-1">Old Price ($) <span class="text-gray-400">(optional)</span></label>
                            <input 
                                type="number" 
                                id="old_price" 
                                name="old_price" 
                                value="<?php echo isset($_POST['old_price']) ? htmlspecialchars($_POST['old_price']) : ''; ?>" 
                                step="0.01" 
                                min="0" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>
                        
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock <span class="text-red-500">*</span></label>
                            <input 
                                type="number" 
                                id="stock" 
                                name="stock" 
                                value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '1'; ?>" 
                                min="0" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>
                        
                        <div>
                            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL <span class="text-red-500">*</span></label>
                            <input 
                                type="url" 
                                id="image_url" 
                                name="image_url" 
                                value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                            <p class="mt-1 text-xs text-gray-500">Enter a URL for the product image (e.g., https://images.unsplash.com/...)</p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="6" 
                                class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required
                            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="featured" 
                                    name="featured" 
                                    value="1" 
                                    <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?> 
                                    class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label for="featured" class="ml-2 block text-sm text-gray-700">
                                    Feature this product on the homepage
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a 
                            href="products.php" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md mr-2"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md"
                        >
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
