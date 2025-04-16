<?php 
session_start();
require_once 'db.php';

// Fetch category details
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch products under the category
$stmt = $conn->prepare("
    SELECT p.*, COALESCE(AVG(r.rating), 0) AS avg_rating 
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.category_id = :category_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reviews for products in the category
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.profile_image, p.name AS product_name 
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    WHERE p.category_id = :category_id
    ORDER BY r.created_at DESC
");
$stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($category['name']); ?> - TUKOLE Business</title>
  <link rel="stylesheet" href="assets/css/tailwind.css">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-100">
  <?php include 'header.php'; ?>

  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($category['name']); ?></h2>
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
    </div>
  </section>

  <section class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
      <h2 class="text-2xl font-bold text-center mb-8">Customer Reviews</h2>
      <div class="grid md:grid-cols-3 gap-6">
        <?php if (count($reviews) > 0): ?>
          <?php foreach ($reviews as $review): ?>
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-200 rounded-full mr-4">
                  <img src="<?php echo htmlspecialchars($review['profile_image'] ?? './assets/images/default-avatar.png'); ?>" alt="User Avatar" class="w-full h-full object-cover rounded-full" />
                </div>
                <div>
                  <h4 class="font-medium"><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></h4>
                  <div class="flex text-yellow-400">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <?php if ($i <= $review['rating']): ?>
                        <i class="fas fa-star"></i>
                      <?php else: ?>
                        <i class="far fa-star"></i>
                      <?php endif; ?>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>
              <p class="text-gray-600 italic">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
              <p class="text-sm text-gray-500 mt-2">- Reviewed on <strong><?php echo htmlspecialchars($review['product_name']); ?></strong></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-gray-500 italic text-center col-span-3">No reviews available for this category.</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
</body>
</html>
