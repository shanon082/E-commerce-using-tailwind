<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login_and_signup/login.php');
    exit;
}

// Check if category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid category ID.";
    header('Location: categories.php');
    exit;
}

$category_id = $_GET['id'];

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindParam(':id', $category_id);
$stmt->execute();
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['error'] = "Category not found.";
    header('Location: categories.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Category name is required";
    }
    
    if (empty($errors)) {
        try {
            // Update the category
            $stmt = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $category_id);
            $stmt->execute();
            
            // Redirect to categories page with success message
            $_SESSION['success'] = "Category updated successfully.";
            header('Location: categories.php');
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
    <title>Edit Category - TUKOLE Business</title>
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
                <a href="categories.php" class="text-blue-500 hover:underline mr-2">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
                <h1 class="text-2xl font-bold">Edit Category: <?php echo htmlspecialchars($category['name']); ?></h1>
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
                <form action="edit_category.php?id=<?php echo $category_id; ?>" method="post" class="p-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?php echo htmlspecialchars($category['name']); ?>" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a 
                            href="categories.php" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md mr-2"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md"
                        >
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
