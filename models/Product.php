<?php
/**
 * Product Model
 * 
 * This class represents the Product entity and handles database operations related to products.
 */
class Product {
    private $conn;
    
    // Product properties
    public $id;
    public $category_id;
    public $name;
    public $slug;
    public $description;
    public $price;
    public $old_price;
    public $image_url;
    public $rating;
    public $stock;
    public $featured;
    public $created_at;
    
    /**
     * Constructor with DB connection
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all products
     *
     * @param int $limit Limit the number of products returned
     * @param int $offset Offset for pagination
     * @param array $filters Optional filters (category_id, featured, etc.)
     * @return array Array of products
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['category_id'])) {
            $whereClause[] = "category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['featured'])) {
            $whereClause[] = "featured = :featured";
            $params[':featured'] = $filters['featured'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(name LIKE :search OR description LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        if (isset($filters['min_price'])) {
            $whereClause[] = "price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $whereClause[] = "price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['in_stock'])) {
            $whereClause[] = "stock > 0";
        }
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        // Apply ordering
        if (isset($filters['order_by'])) {
            switch ($filters['order_by']) {
                case 'price_asc':
                    $sql .= " ORDER BY price ASC";
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY price DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY created_at DESC";
                    break;
                case 'name':
                    $sql .= " ORDER BY name ASC";
                    break;
                case 'rating':
                    $sql .= " ORDER BY rating DESC";
                    break;
                default:
                    $sql .= " ORDER BY id DESC";
            }
        } else {
            $sql .= " ORDER BY id DESC";
        }
        
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total count of products with applied filters
     *
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function getCount($filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['category_id'])) {
            $whereClause[] = "category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['featured'])) {
            $whereClause[] = "featured = :featured";
            $params[':featured'] = $filters['featured'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(name LIKE :search OR description LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        if (isset($filters['min_price'])) {
            $whereClause[] = "price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $whereClause[] = "price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['in_stock'])) {
            $whereClause[] = "stock > 0";
        }
        
        $sql = "SELECT COUNT(*) FROM products";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get a single product by ID
     *
     * @param int $id Product ID
     * @return array|false Product data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Load product ratings
            $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count 
                   FROM reviews 
                   WHERE product_id = :product_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $product['avg_rating'] = $rating_data['avg_rating'] ?? 0;
            $product['rating_count'] = $rating_data['count'] ?? 0;
        }
        
        return $product;
    }
    
    /**
     * Get a single product by slug
     *
     * @param string $slug Product slug
     * @return array|false Product data or false if not found
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.slug = :slug";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get featured products
     *
     * @param int $limit Number of products to return
     * @return array Array of featured products
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE featured = 1 
                ORDER BY id DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get related products based on category
     *
     * @param int $product_id Current product ID to exclude
     * @param int $category_id Category ID to match
     * @param int $limit Number of products to return
     * @return array Array of related products
     */
    public function getRelated($product_id, $category_id, $limit = 4) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category_id AND p.id != :product_id 
                ORDER BY RAND() 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new product
     *
     * @return boolean Success or failure
     */
    public function create() {
        $sql = "INSERT INTO products (
                category_id, name, slug, description, price, old_price, 
                image_url, stock, featured, created_at
            ) VALUES (
                :category_id, :name, :slug, :description, :price, :old_price, 
                :image_url, :stock, :featured, NOW()
            )";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        
        // Bind parameters
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':old_price', $this->old_price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':featured', $this->featured);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update an existing product
     *
     * @return boolean Success or failure
     */
    public function update() {
        $sql = "UPDATE products SET 
                category_id = :category_id,
                name = :name,
                slug = :slug,
                description = :description,
                price = :price,
                old_price = :old_price,
                image_url = :image_url,
                stock = :stock,
                featured = :featured
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        
        // Bind parameters
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':old_price', $this->old_price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':featured', $this->featured);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete a product
     *
     * @return boolean Success or failure
     */
    public function delete() {
        $sql = "DELETE FROM products WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update product stock
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity to subtract from stock
     * @return boolean Success or failure
     */
    public function updateStock($product_id, $quantity) {
        $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get product reviews
     *
     * @param int $product_id Product ID
     * @param int $limit Limit the number of reviews
     * @param int $offset Offset for pagination
     * @return array Array of reviews
     */
    public function getReviews($product_id, $limit = 5, $offset = 0) {
        $sql = "SELECT r.*, u.username 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = :product_id 
                ORDER BY r.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add a review to a product
     *
     * @param int $product_id Product ID
     * @param int $user_id User ID
     * @param int $rating Rating (1-5)
     * @param string $comment Review comment
     * @return boolean Success or failure
     */
    public function addReview($product_id, $user_id, $rating, $comment) {
        $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
                VALUES (:product_id, :user_id, :rating, :comment, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment);
        
        // Execute query
        if ($stmt->execute()) {
            // Update product rating
            $this->updateProductRating($product_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Update product rating based on reviews
     *
     * @param int $product_id Product ID
     * @return boolean Success or failure
     */
    private function updateProductRating($product_id) {
        $sql = "UPDATE products p 
                SET rating = (
                    SELECT AVG(rating) 
                    FROM reviews 
                    WHERE product_id = :product_id
                ) 
                WHERE p.id = :product_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>
