<?php
session_start();
require_once 'db.php';

// Get the search query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch matching products
$products = [];
if (!empty($searchQuery)) {
    $stmt = $conn->prepare("
        SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating 
        FROM products p
        LEFT JOIN reviews r ON p.id = r.product_id
        WHERE p.name LIKE :searchQuery OR p.description LIKE :searchQuery
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bindParam(':searchQuery', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <section class="py-8 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
            <?php if (!empty($products)): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                            <a href="product_description.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 object-cover" />
                                <div class="p-3">
                                    <h3 class="text-sm font-medium"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="flex items-center mt-1">
                                        <span class="text-blue-500 font-bold">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php if ($product['old_price'] > $product['price']): ?>
                                            <span class="ml-2 text-gray-400 text-xs line-through">$<?php echo number_format($product['old_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center mt-1 text-yellow-400 text-xs">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= round($product['avg_rating'])): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i - 0.5 <= $product['avg_rating']): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 italic">No products found matching your search query.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>
