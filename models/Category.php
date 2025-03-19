<?php
/**
 * Category Model
 * 
 * This class represents the Category entity and handles database operations related to categories.
 */
class Category {
    private $conn;
    
    // Category properties
    public $id;
    public $name;
    public $slug;
    public $icon;
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
     * Get all categories
     *
     * @param int $limit Limit the number of categories returned
     * @param int $offset Offset for pagination
     * @return array Array of categories
     */
    public function getAll($limit = 0, $offset = 0) {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                FROM categories c 
                ORDER BY c.name";
        
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($limit > 0) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total count of categories
     *
     * @return int Total count
     */
    public function getCount() {
        $sql = "SELECT COUNT(*) FROM categories";
        $stmt = $this->conn->query($sql);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get a single category by ID
     *
     * @param int $id Category ID
     * @return array|false Category data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single category by slug
     *
     * @param string $slug Category slug
     * @return array|false Category data or false if not found
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = :slug";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new category
     *
     * @return boolean Success or failure
     */
    public function create() {
        // Check if category with same name or slug already exists
        if ($this->isCategoryExists()) {
            return false;
        }
        
        $sql = "INSERT INTO categories (name, slug, icon, created_at) 
                VALUES (:name, :slug, :icon, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->icon = htmlspecialchars(strip_tags($this->icon));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':icon', $this->icon);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update an existing category
     *
     * @return boolean Success or failure
     */
    public function update() {
        $sql = "UPDATE categories SET 
                name = :name,
                slug = :slug,
                icon = :icon
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->icon = htmlspecialchars(strip_tags($this->icon));
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':icon', $this->icon);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete a category
     *
     * @return boolean Success or failure
     */
    public function delete() {
        // Check if category has products
        $sql = "SELECT COUNT(*) FROM products WHERE category_id = :category_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $this->id);
        $stmt->execute();
        
        $productCount = $stmt->fetchColumn();
        
        if ($productCount > 0) {
            // Category has products, don't delete
            return false;
        }
        
        // Delete the category
        $sql = "DELETE FROM categories WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if a category with the same name or slug exists
     *
     * @return boolean True if exists, false otherwise
     */
    private function isCategoryExists() {
        $sql = "SELECT id FROM categories WHERE name = :name OR slug = :slug";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get products in a category
     *
     * @param int $category_id Category ID
     * @param int $limit Limit the number of products
     * @param int $offset Offset for pagination
     * @param array $filters Optional filters
     * @return array Array of products in the category
     */
    public function getCategoryProducts($category_id, $limit = 12, $offset = 0, $filters = []) {
        $whereClause = ["p.category_id = :category_id"];
        $params = [':category_id' => $category_id];
        
        // Apply additional filters if provided
        if (isset($filters['min_price'])) {
            $whereClause[] = "p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $whereClause[] = "p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $whereClause[] = "p.stock > 0";
        }
        
        $sql = "SELECT p.* FROM products p WHERE " . implode(" AND ", $whereClause);
        
        // Apply ordering
        if (isset($filters['order_by'])) {
            switch ($filters['order_by']) {
                case 'price_asc':
                    $sql .= " ORDER BY p.price ASC";
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY p.price DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY p.created_at DESC";
                    break;
                case 'rating':
                    $sql .= " ORDER BY p.rating DESC";
                    break;
                default:
                    $sql .= " ORDER BY p.id DESC";
            }
        } else {
            $sql .= " ORDER BY p.id DESC";
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
     * Get count of products in a category with filters
     *
     * @param int $category_id Category ID
     * @param array $filters Optional filters
     * @return int Product count
     */
    public function getCategoryProductCount($category_id, $filters = []) {
        $whereClause = ["category_id = :category_id"];
        $params = [':category_id' => $category_id];
        
        // Apply additional filters if provided
        if (isset($filters['min_price'])) {
            $whereClause[] = "price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $whereClause[] = "price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $whereClause[] = "stock > 0";
        }
        
        $sql = "SELECT COUNT(*) FROM products WHERE " . implode(" AND ", $whereClause);
        
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
     * Get category statistics for admin dashboard
     *
     * @return array Category statistics
     */
    public function getCategoryStats() {
        $sql = "SELECT 
                c.id, 
                c.name, 
                COUNT(p.id) as product_count,
                COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.payment_status = 'paid'
                GROUP BY c.id
                ORDER BY total_sold DESC";
        
        $stmt = $this->conn->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Format a string into a valid slug
     *
     * @param string $string String to convert to slug
     * @return string Slug
     */
    public static function createSlug($string) {
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($string)));
        
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }
}
?>
