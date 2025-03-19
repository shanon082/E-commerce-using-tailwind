<?php
// Database connection settings
// Using MySQL-like configuration for simplicity
$host = "localhost";
$dbname = "ecommerce";
$username = "admin";
$password = "password";

try {
    // Create a PDO instance simulating a localhost XAMPP-like connection
    // But we'll actually use SQLite since it's more portable and doesn't need a server
    $conn = new PDO('sqlite::memory:');
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set fetch mode to associative array by default
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Check if tables exist and create them if they don't
    $result = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_name = 'categories'
    )");
    
    $tableExists = $result->fetchColumn();
    
    if (!$tableExists) {
        // Create tables
        createTables($conn);
        
        // Insert sample data
        insertSampleData($conn);
    }
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to create necessary tables
function createTables($conn) {
    // Categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL,
        icon TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Products table
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        name TEXT NOT NULL,
        slug TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        old_price REAL,
        image_url TEXT,
        rating REAL DEFAULT 0,
        stock INTEGER DEFAULT 0,
        featured INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        first_name TEXT,
        last_name TEXT,
        phone TEXT,
        is_admin INTEGER DEFAULT 0,
        newsletter INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Addresses table
    $conn->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        address TEXT NOT NULL,
        city TEXT,
        region TEXT,
        postal_code TEXT,
        country TEXT DEFAULT 'Uganda',
        is_default INTEGER DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        address_id INTEGER,
        total_amount REAL NOT NULL,
        payment_method TEXT,
        payment_status TEXT DEFAULT 'pending',
        order_status TEXT DEFAULT 'processing',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
    )");

    // Order Items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER,
        quantity INTEGER NOT NULL,
        price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )");

    // Reviews table
    $conn->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        user_id INTEGER,
        rating INTEGER NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
}

// Function to insert sample data
function insertSampleData($conn) {
    // Insert categories
    $categories = [
        ['name' => 'Electronics', 'slug' => 'electronics', 'icon' => 'fas fa-tv'],
        ['name' => 'Fashion', 'slug' => 'fashion', 'icon' => 'fas fa-tshirt'],
        ['name' => 'Home & Living', 'slug' => 'home-living', 'icon' => 'fas fa-home'],
        ['name' => 'Health & Beauty', 'slug' => 'health-beauty', 'icon' => 'fas fa-heart'],
        ['name' => 'Baby Products', 'slug' => 'baby-products', 'icon' => 'fas fa-baby'],
        ['name' => 'Phones & Tablets', 'slug' => 'phones-tablets', 'icon' => 'fas fa-mobile-alt'],
        ['name' => 'Computing', 'slug' => 'computing', 'icon' => 'fas fa-laptop'],
        ['name' => 'Gaming', 'slug' => 'gaming', 'icon' => 'fas fa-gamepad'],
        ['name' => 'Supermarket', 'slug' => 'supermarket', 'icon' => 'fas fa-shopping-basket']
    ];

    $categoryStmt = $conn->prepare("INSERT INTO categories (name, slug, icon) VALUES (:name, :slug, :icon)");
    
    foreach ($categories as $category) {
        $categoryStmt->bindParam(':name', $category['name']);
        $categoryStmt->bindParam(':slug', $category['slug']);
        $categoryStmt->bindParam(':icon', $category['icon']);
        $categoryStmt->execute();
    }

    // Insert sample products
    $products = [
        // Electronics (category_id: 1)
        [
            'category_id' => 1,
            'name' => 'Premium Headphones',
            'slug' => 'premium-headphones',
            'description' => 'High-quality headphones with noise cancellation and crystal clear sound.',
            'price' => 89.99,
            'old_price' => 129.99,
            'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
            'rating' => 4.5,
            'stock' => 25,
            'featured' => 1
        ],
        [
            'category_id' => 1,
            'name' => 'Smart Watch Pro',
            'slug' => 'smart-watch-pro',
            'description' => 'Advanced smartwatch with health monitoring and notification features.',
            'price' => 149.99,
            'old_price' => 199.99,
            'image_url' => 'https://images.unsplash.com/photo-1699796990049-3406a9991baa',
            'rating' => 4.0,
            'stock' => 15,
            'featured' => 1
        ],
        [
            'category_id' => 1,
            'name' => 'Portable Bluetooth Speaker',
            'slug' => 'portable-bluetooth-speaker',
            'description' => 'Compact and powerful Bluetooth speaker with 20 hours battery life.',
            'price' => 59.99,
            'old_price' => 79.99,
            'image_url' => 'https://images.unsplash.com/photo-1698440050363-1697e5f0277c',
            'rating' => 4.0,
            'stock' => 30,
            'featured' => 0
        ],
        [
            'category_id' => 1,
            'name' => 'Digital Camera 4K',
            'slug' => 'digital-camera-4k',
            'description' => 'Professional digital camera with 4K recording capabilities.',
            'price' => 399.99,
            'old_price' => 499.99,
            'image_url' => 'https://images.unsplash.com/photo-1712701815718-29f5fe510c0e',
            'rating' => 4.5,
            'stock' => 10,
            'featured' => 0
        ],
        [
            'category_id' => 1,
            'name' => 'Wireless Earbuds',
            'slug' => 'wireless-earbuds',
            'description' => 'True wireless earbuds with touch controls and charging case.',
            'price' => 69.99,
            'old_price' => 99.99,
            'image_url' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9',
            'rating' => 4.2,
            'stock' => 20,
            'featured' => 1
        ],
        
        // Fashion (category_id: 2)
        [
            'category_id' => 2,
            'name' => 'Men\'s Classic Watch',
            'slug' => 'mens-classic-watch',
            'description' => 'Elegant wristwatch with a genuine leather strap.',
            'price' => 129.99,
            'old_price' => 179.99,
            'image_url' => 'https://images.unsplash.com/photo-1626947346165-4c2288dadc2a',
            'rating' => 4.0,
            'stock' => 15,
            'featured' => 0
        ],
        [
            'category_id' => 2,
            'name' => 'Designer Handbag',
            'slug' => 'designer-handbag',
            'description' => 'High-quality handbag made from premium materials.',
            'price' => 89.99,
            'old_price' => 119.99,
            'image_url' => 'https://images.unsplash.com/photo-1523464771852-de9293765f7a',
            'rating' => 4.5,
            'stock' => 12,
            'featured' => 1
        ],
        [
            'category_id' => 2,
            'name' => 'Premium Sunglasses',
            'slug' => 'premium-sunglasses',
            'description' => 'Stylish sunglasses with UV protection.',
            'price' => 49.99,
            'old_price' => 69.99,
            'image_url' => 'https://images.unsplash.com/photo-1523194258983-4ef0203f0c47',
            'rating' => 4.0,
            'stock' => 25,
            'featured' => 0
        ],
        [
            'category_id' => 2,
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet',
            'description' => 'Compact leather wallet with multiple card slots and coin pocket.',
            'price' => 39.99,
            'old_price' => 59.99,
            'image_url' => 'https://images.unsplash.com/3/www.madebyvadim.com.jpg',
            'rating' => 5.0,
            'stock' => 30,
            'featured' => 0
        ],
        
        // Home Products (category_id: 3)
        [
            'category_id' => 3,
            'name' => 'Modern Table Lamp',
            'slug' => 'modern-table-lamp',
            'description' => 'Stylish table lamp with adjustable brightness.',
            'price' => 49.99,
            'old_price' => 69.99,
            'image_url' => 'https://images.unsplash.com/photo-1524634126442-357e0eac3c14',
            'rating' => 4.0,
            'stock' => 18,
            'featured' => 0
        ],
        [
            'category_id' => 3,
            'name' => 'Decorative Pillow Set',
            'slug' => 'decorative-pillow-set',
            'description' => 'Set of 4 decorative pillows with removable covers.',
            'price' => 29.99,
            'old_price' => 39.99,
            'image_url' => 'https://images.unsplash.com/photo-1592136957897-b2b6ca21e10d',
            'rating' => 4.5,
            'stock' => 25,
            'featured' => 0
        ],
        [
            'category_id' => 3,
            'name' => 'Designer Coffee Mug',
            'slug' => 'designer-coffee-mug',
            'description' => 'Elegant coffee mug with unique design.',
            'price' => 14.99,
            'old_price' => 19.99,
            'image_url' => 'https://images.unsplash.com/photo-1597817109745-c418f4875230',
            'rating' => 5.0,
            'stock' => 40,
            'featured' => 1
        ],
        [
            'category_id' => 3,
            'name' => 'Wooden Desk Organizer',
            'slug' => 'wooden-desk-organizer',
            'description' => 'Multifunctional desk organizer made from natural wood.',
            'price' => 24.99,
            'old_price' => 34.99,
            'image_url' => 'https://images.unsplash.com/photo-1542435503-956c469947f6',
            'rating' => 4.0,
            'stock' => 15,
            'featured' => 0
        ]
    ];

    $productStmt = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, old_price, image_url, rating, stock, featured) 
                                  VALUES (:category_id, :name, :slug, :description, :price, :old_price, :image_url, :rating, :stock, :featured)");
    
    foreach ($products as $product) {
        $productStmt->bindParam(':category_id', $product['category_id']);
        $productStmt->bindParam(':name', $product['name']);
        $productStmt->bindParam(':slug', $product['slug']);
        $productStmt->bindParam(':description', $product['description']);
        $productStmt->bindParam(':price', $product['price']);
        $productStmt->bindParam(':old_price', $product['old_price']);
        $productStmt->bindParam(':image_url', $product['image_url']);
        $productStmt->bindParam(':rating', $product['rating']);
        $productStmt->bindParam(':stock', $product['stock']);
        $productStmt->bindParam(':featured', $product['featured']);
        $productStmt->execute();
    }

    // Create admin user
    $adminUsername = 'admin';
    $adminEmail = 'admin@tukole.com';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $conn->exec("INSERT INTO users (username, email, password, first_name, last_name, is_admin) 
                VALUES ('$adminUsername', '$adminEmail', '$adminPassword', 'Admin', 'User', 1)");
}
?>
