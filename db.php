<?php
$dbHost = "localhost";
$dbName = "ecommerce";
$dbUsername = "root";
$dbPassword = "";

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Create tables if they don't exist
function setupDatabase($conn) {
    // Users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Addresses table
    $conn->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        alternate_phone VARCHAR(20),
        address VARCHAR(255) NOT NULL,
        city VARCHAR(50) NOT NULL,
        region VARCHAR(50) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        is_default BOOLEAN DEFAULT 1,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        parent_id INT(11) UNSIGNED DEFAULT NULL,
        image VARCHAR(255),
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Products table
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2),
        stock INT(11) NOT NULL DEFAULT 0,
        category_id INT(11) UNSIGNED,
        image VARCHAR(255),
        featured BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Product images table
    $conn->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT(11) UNSIGNED NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // Orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_address_id INT(11) UNSIGNED NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        order_status VARCHAR(20) NOT NULL DEFAULT 'processing',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (shipping_address_id) REFERENCES addresses(id) ON DELETE RESTRICT
    )");

    // Order items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
    )");

    // Cart table
    $conn->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY user_product (user_id, product_id)
    )");

    // Admin users table
    $conn->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default admin user if it doesn't exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $adminUsername = "admin";
        $adminEmail = "admin@example.com";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
    }

    // Insert default categories if they don't exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $categoryCount = $stmt->fetchColumn();

    if ($categoryCount == 0) {
        $categories = [
            ['Electronics', 'electronics', 'All electronic devices and gadgets'],
            ['Fashion', 'fashion', 'Clothing, shoes, and accessories'],
            ['Home & Kitchen', 'home-kitchen', 'Home and kitchen products'],
            ['Phones & Tablets', 'phones-tablets', 'Mobile phones and tablets', 1],
            ['Computers', 'computers', 'Laptops, desktops, and computer accessories', 1],
            ['TVs & Audio', 'tvs-audio', 'Televisions and audio equipment', 1],
            ['Men\'s Fashion', 'mens-fashion', 'Men\'s clothing and accessories', 2],
            ['Women\'s Fashion', 'womens-fashion', 'Women\'s clothing and accessories', 2],
            ['Furniture', 'furniture', 'Home furniture', 3],
            ['Kitchen Appliances', 'kitchen-appliances', 'Kitchen appliances and utensils', 3]
        ];

        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, parent_id) VALUES (?, ?, ?, ?)");
        
        foreach ($categories as $category) {
            $parentId = isset($category[3]) ? $category[3] : null;
            $stmt->execute([$category[0], $category[1], $category[2], $parentId]);
        }
    }

    // Sample products data (add only if no products exist)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $productCount = $stmt->fetchColumn();

    if ($productCount == 0) {
        // Sample products
        $products = [
            // Electronics - Phones & Tablets
            [
                'iPhone 13 Pro', 
                'iphone-13-pro', 
                'Apple iPhone 13 Pro with 128GB storage and A15 Bionic chip',
                999.99,
                899.99,
                10,
                4,
                'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9',
                1
            ],
            [
                'Samsung Galaxy S21', 
                'samsung-galaxy-s21', 
                'Samsung Galaxy S21 with 256GB storage and 5G capability',
                799.99,
                749.99,
                15,
                4,
                'https://images.unsplash.com/photo-1610945264803-c22b62d2a7b3',
                1
            ],
            // Electronics - Computers
            [
                'MacBook Pro 16"', 
                'macbook-pro-16', 
                'Apple MacBook Pro 16" with M1 Pro chip and 16GB RAM',
                2499.99,
                null,
                5,
                5,
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8',
                1
            ],
            // Electronics - TVs & Audio
            [
                'Sony 65" 4K Smart TV', 
                'sony-65-4k-smart-tv', 
                'Sony 65" 4K Ultra HD Smart LED TV with HDR',
                1299.99,
                1099.99,
                8,
                6,
                'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1',
                1
            ],
            [
                'Bose QuietComfort 45', 
                'bose-quietcomfort-45', 
                'Bose QuietComfort 45 Bluetooth wireless noise cancelling headphones',
                329.99,
                299.99,
                20,
                6,
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
                0
            ],
            // Fashion - Men's
            [
                'Classic Oxford Shirt', 
                'classic-oxford-shirt', 
                'Men\'s classic Oxford button-down shirt in various colors',
                49.99,
                39.99,
                30,
                7,
                'https://images.unsplash.com/3/www.madebyvadim.com.jpg',
                0
            ],
            [
                'Slim Fit Jeans', 
                'slim-fit-jeans', 
                'Men\'s slim fit stretch denim jeans',
                59.99,
                null,
                25,
                7,
                'https://images.unsplash.com/photo-1523464771852-de9293765f7a',
                0
            ],
            // Fashion - Women's
            [
                'Summer Maxi Dress', 
                'summer-maxi-dress', 
                'Women\'s floral print summer maxi dress',
                69.99,
                49.99,
                15,
                8,
                'https://images.unsplash.com/photo-1523194258983-4ef0203f0c47',
                1
            ],
            [
                'High Waist Yoga Pants', 
                'high-waist-yoga-pants', 
                'Women\'s high waist yoga pants with pockets',
                39.99,
                29.99,
                40,
                8,
                'https://images.unsplash.com/photo-1626947346165-4c2288dadc2a',
                0
            ],
            // Home & Kitchen - Furniture
            [
                'Modern Coffee Table', 
                'modern-coffee-table', 
                'Modern wooden coffee table with metal legs',
                199.99,
                179.99,
                10,
                9,
                'https://images.unsplash.com/photo-1524634126442-357e0eac3c14',
                1
            ],
            [
                'Ergonomic Office Chair', 
                'ergonomic-office-chair', 
                'Ergonomic mesh office chair with lumbar support',
                249.99,
                219.99,
                12,
                9,
                'https://images.unsplash.com/photo-1592136957897-b2b6ca21e10d',
                0
            ],
            // Home & Kitchen - Kitchen Appliances
            [
                'Stand Mixer', 
                'stand-mixer', 
                '5-quart stand mixer with 10 speeds and dough hook',
                349.99,
                299.99,
                8,
                10,
                'https://images.unsplash.com/photo-1597817109745-c418f4875230',
                1
            ],
            [
                'Air Fryer', 
                'air-fryer', 
                '5.8-quart digital air fryer with 8 preset cooking functions',
                129.99,
                99.99,
                18,
                10,
                'https://images.unsplash.com/photo-1542435503-956c469947f6',
                0
            ]
        ];

        $stmt = $conn->prepare("INSERT INTO products (name, slug, description, price, sale_price, stock, category_id, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($products as $product) {
            $stmt->execute($product);
        }
    }
}

// Call the setup function
setupDatabase($conn);

// Helper functions
function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function getProductById($conn, $productId) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

function getFeaturedProducts($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.featured = 1 
                           ORDER BY p.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getLatestProducts($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getProductsByCategory($conn, $categoryId, $limit = 12, $offset = 0) {
    // Get all subcategory IDs
    $subCategoryIds = [];
    
    $stmt = $conn->prepare("SELECT id FROM categories WHERE parent_id = ?");
    $stmt->execute([$categoryId]);
    $subCategories = $stmt->fetchAll();
    
    foreach ($subCategories as $subCategory) {
        $subCategoryIds[] = $subCategory['id'];
    }
    
    // Include the main category and all subcategories
    $allCategoryIds = array_merge([$categoryId], $subCategoryIds);
    $placeholders = str_repeat('?,', count($allCategoryIds) - 1) . '?';
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id IN ($placeholders) 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params = array_merge($allCategoryIds, [$limit, $offset]);
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function searchProducts($conn, $keyword, $limit = 12, $offset = 0) {
    $keyword = "%$keyword%";
    
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.name LIKE ? OR p.description LIKE ? 
                           ORDER BY p.created_at DESC 
                           LIMIT ? OFFSET ?");
    $stmt->execute([$keyword, $keyword, $limit, $offset]);
    return $stmt->fetchAll();
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function getCartItems($conn, $userId) {
    $stmt = $conn->prepare("SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.sale_price, p.image 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCartTotal($conn, $userId) {
    $stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) as total 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getCartCount($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getAllCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMainCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSubCategories($conn, $parentId) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

function getCategoryById($conn, $categoryId) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetch();
}

function getCategoryBySlug($conn, $slug) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getUserAddresses($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getDefaultAddress($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

// Session management functions
function checkUserSession() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}

function getUserData($conn) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, phone FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Add a function to generate slugs
function generateSlug($text) {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim
    $text = trim($text, '-');
    
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    
    // Lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}
?>
