<?php 
session_start(); 
require_once 'db.php';

// Fetch categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products by category with average rating
function fetchProductsByCategory($conn, $categoryId, $limit = 4) {
    $stmt = $conn->prepare("
        SELECT p.*, 
               COALESCE(AVG(r.rating), 0) AS avg_rating 
        FROM products p
        LEFT JOIN reviews r ON p.id = r.product_id
        WHERE p.category_id = :category_id
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch products for specific sections
$electronicsProducts = fetchProductsByCategory($conn, 1); // Electronics category
$fashionProducts = fetchProductsByCategory($conn, 2); // Fashion category
$homeProducts = fetchProductsByCategory($conn, 3); // Home Products category

// Fetch featured products
$stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 LIMIT 6");
$stmt->execute();
$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest products
$stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Fetch latest customer reviews
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.profile_image, p.name AS product_name 
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC
    LIMIT 3
");
$stmt->execute();
$latestReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TUKOLE Business - Your Online Shopping Destination</title>
  <link rel="stylesheet" href="assets/css/tailwind.css" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100">
  <?php include 'header.php'; ?>
  
  <!-- Hero Banner Slider -->
  <section class="relative overflow-hidden bg-white">
    <div class="slideshow-container w-full">
      <div class="slide fade">
        <img src="./assets/images/electronics.png" alt="Electronics" class="w-full h-64 md:h-80 lg:h-96 object-cover" />
        <div class="absolute inset-0 flex items-center bg-gradient-to-r from-black/60 to-transparent p-8">
          <div class="text-white max-w-md">
            <h3 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">Latest Electronics <br />& Gadgets</h3>
            <p class="text-sm md:text-base mb-4">Find the best deals on the newest tech products.</p>
            <a href="#electronics" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md inline-block transition">Shop Now</a>
          </div>
        </div>
      </div>
      <div class="slide fade hidden">
        <img src="./assets/images/fashion.png" alt="Fashion" class="w-full h-64 md:h-80 lg:h-96 object-cover" />
        <div class="absolute inset-0 flex items-center bg-gradient-to-r from-black/60 to-transparent p-8">
          <div class="text-white max-w-md">
            <h3 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">Trendy Fashion <br />& Clothing</h3>
            <p class="text-sm md:text-base mb-4">Shop the latest trends at the best prices.</p>
            <a href="#fashion" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md inline-block transition">Shop Now</a>
          </div>
        </div>
      </div>
      <div class="slide fade hidden">
        <img src="./assets/images/quality.png" alt="Home Products" class="w-full h-64 md:h-80 lg:h-96 object-cover" />
        <div class="absolute inset-0 flex items-center bg-gradient-to-r from-black/60 to-transparent p-8">
          <div class="text-white max-w-md">
            <h3 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">Quality Home <br />Products</h3>
            <p class="text-sm md:text-base mb-4">Stylish and durable products for your home.</p>
            <a href="#home" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md inline-block transition">Shop Now</a>
          </div>
        </div>
      </div>
      <button class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black/30 text-white p-2 rounded-full" onclick="changeSlide(-1)">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black/30 text-white p-2 rounded-full" onclick="changeSlide(1)">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </section>

  <!-- Category Navigation -->
  <section class="py-6 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-xl md:text-2xl font-bold mb-4">Shop by Categories</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($categories as $category): ?>
          <a href="category.php?id=<?php echo $category['id']; ?>" class="bg-gray-100 rounded-lg p-4 text-center hover:shadow-md transition">
            <div class="w-12 h-12 mx-auto mb-2 flex items-center justify-center">
              <i class="<?php echo $category['icon']; ?> text-2xl text-blue-500"></i>
            </div>
            <h3 class="text-sm font-medium"><?php echo htmlspecialchars($category['name']); ?></h3>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Featured Deals Banner -->
  <section class="bg-blue-600 py-6">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="text-white mb-4 md:mb-0">
          <h2 class="text-2xl md:text-3xl font-bold">Tech Masavu</h2>
          <p class="text-xl font-bold">UP TO 60% OFF</p>
          <p class="text-sm">Limited time offer - 17-30 MARCH</p>
        </div>
        <a href="#deals" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md inline-block transition">SHOP NOW</a>
      </div>
    </div>
  </section>

  <!-- Featured Products Section -->
  <section id="featured" class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold">Featured Products</h2>
        <a href="#" class="text-blue-500 hover:underline text-sm">See All</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <?php foreach ($featuredProducts as $product): ?>
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
            <a href="product_description.php?id=<?php echo $product['id']; ?>">
              <img 
                src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                class="w-full h-40 object-cover"
              />
              <div class="p-3">
                <h3 class="text-sm font-medium truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="flex items-center mt-1">
                  <span class="text-blue-500 font-bold">$<?php echo number_format($product['price'], 2); ?></span>
                  <?php if ($product['old_price'] > $product['price']): ?>
                    <span class="ml-2 text-gray-400 text-xs line-through">$<?php echo number_format($product['old_price'], 2); ?></span>
                  <?php endif; ?>
                </div>
                <!-- <div class="flex items-center mt-1 text-yellow-400 text-xs">
                  <?php ////for ($i = 1; $i <= 5; $i++): ?>
                    <?php ////if ($i <= round($product['rating'])): ?>
                      <i class="fas fa-star"></i>
                    <?php ////elseif ($i - 0.5 <= $product['rating']): ?>
                      <i class="fas fa-star-half-alt"></i>
                    <?php // else: ?>
                      <i class="far fa-star"></i>
                    <?php ////endif; ?>
                  <?php //endfor; ?>
                </div> -->
              </div>
            </a>
            <div class="px-3 pb-3">
              <button 
                onclick="addToCart(<?php echo $product['id']; ?>)" 
                class="w-full bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 rounded transition"
              >
                Add to Cart
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Electronics Section -->
  <section id="electronics" class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold">Electronics</h2>
        <a href="category.php?id=1" class="text-blue-500 hover:underline text-sm">See All</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($electronicsProducts as $product): ?>
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

  <!-- Fashion Section -->
  <section id="fashion" class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold">Fashion</h2>
        <a href="category.php?id=2" class="text-blue-500 hover:underline text-sm">See All</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($fashionProducts as $product): ?>
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

  <!-- Home Products Section -->
  <section id="home" class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold">Home Products</h2>
        <a href="category.php?id=3" class="text-blue-500 hover:underline text-sm">See All</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($homeProducts as $product): ?>
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

  <!-- About Us Section -->
  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-8">
        <div class="md:w-1/2">
          <img src="./assets/images/digitalcam.png" alt="About Us" class="rounded-lg shadow-md w-full" />
        </div>
        <div class="md:w-1/2">
          <p class="text-blue-500 font-semibold">About Us</p>
          <h2 class="text-2xl md:text-3xl font-bold mb-4">We Provide High-Quality Products</h2>
          <p class="text-gray-600 mb-4">
            We ensure that our products meet top-notch quality standards to satisfy our customers. 
            Our carefully selected range offers the best value for your money.
          </p>
          <p class="mb-1"><span class="font-medium">Same-day Delivery</span> in selected areas</p>
          <p class="mb-1"><span class="font-medium">Secure Payments</span> with multiple options</p>
          <p class="mb-1"><span class="font-medium">24/7 Customer Support</span> for your convenience</p>
          <p class="mb-4"><span class="font-medium">Easy Returns</span> within 30 days</p>
          <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md inline-block transition">Learn More</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Customer Reviews Section -->
  <section class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">What Our Customers Say</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <?php if (count($latestReviews) > 0): ?>
                <?php foreach ($latestReviews as $review): ?>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full mr-4">
                                <img 
                                    src="<?php echo htmlspecialchars($review['profile_image'] ?? './assets/images/default-avatar.png'); ?>" 
                                    alt="User Avatar" 
                                    class="w-full h-full object-cover rounded-full" 
                                />
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
                <div class="text-gray-500 italic text-center col-span-3">No customer reviews available yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

  <!-- Newsletter Section -->
  <section class="py-10 bg-blue-600 text-white">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-2xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">Sign Up for Updates & Newsletter</h2>
        <p class="mb-6">Stay updated with our latest offers and promotions</p>
        <div class="flex flex-col sm:flex-row gap-2 justify-center">
          <input type="email" placeholder="Enter your Email" class="px-4 py-2 rounded-md text-gray-800 w-full sm:w-auto" />
          <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded-md transition">Subscribe Now</button>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
  
  <script src="assets/js/main.js"></script>
</body>
</html>
