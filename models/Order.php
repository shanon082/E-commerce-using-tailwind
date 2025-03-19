<?php
/**
 * Order Model
 * 
 * This class represents the Order entity and handles database operations related to orders.
 */
class Order {
    private $conn;
    
    // Order properties
    public $id;
    public $user_id;
    public $address_id;
    public $total_amount;
    public $payment_method;
    public $payment_status;
    public $order_status;
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
     * Get all orders
     *
     * @param int $limit Limit the number of orders returned
     * @param int $offset Offset for pagination
     * @param array $filters Optional filters
     * @return array Array of orders
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['user_id'])) {
            $whereClause[] = "o.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (isset($filters['order_status'])) {
            $whereClause[] = "o.order_status = :order_status";
            $params[':order_status'] = $filters['order_status'];
        }
        
        if (isset($filters['payment_status'])) {
            $whereClause[] = "o.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(o.id LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        if (isset($filters['date_start'])) {
            $whereClause[] = "o.created_at >= :date_start";
            $params[':date_start'] = $filters['date_start'] . " 00:00:00";
        }
        
        if (isset($filters['date_end'])) {
            $whereClause[] = "o.created_at <= :date_end";
            $params[':date_end'] = $filters['date_end'] . " 23:59:59";
        }
        
        $sql = "SELECT o.*, u.username, u.email, COUNT(oi.id) as item_count 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN order_items oi ON o.id = oi.order_id";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Get total count of orders with applied filters
     *
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function getCount($filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['user_id'])) {
            $whereClause[] = "o.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (isset($filters['order_status'])) {
            $whereClause[] = "o.order_status = :order_status";
            $params[':order_status'] = $filters['order_status'];
        }
        
        if (isset($filters['payment_status'])) {
            $whereClause[] = "o.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(o.id LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        if (isset($filters['date_start'])) {
            $whereClause[] = "o.created_at >= :date_start";
            $params[':date_start'] = $filters['date_start'] . " 00:00:00";
        }
        
        if (isset($filters['date_end'])) {
            $whereClause[] = "o.created_at <= :date_end";
            $params[':date_end'] = $filters['date_end'] . " 23:59:59";
        }
        
        $sql = "SELECT COUNT(DISTINCT o.id) FROM orders o LEFT JOIN users u ON o.user_id = u.id";
        
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
     * Get a single order by ID
     *
     * @param int $id Order ID
     * @return array|false Order data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT o.*, u.username, u.email, u.first_name, u.last_name, u.phone,
                a.address, a.city, a.region, a.postal_code, a.country
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN addresses a ON o.address_id = a.id 
                WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get order items for a specific order
     *
     * @param int $order_id Order ID
     * @return array Array of order items
     */
    public function getOrderItems($order_id) {
        $sql = "SELECT oi.*, p.name, p.image_url, p.slug 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new order
     *
     * @return int|false Order ID on success or false on failure
     */
    public function create() {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Create the order
            $sql = "INSERT INTO orders (
                    user_id, address_id, total_amount, payment_method, 
                    payment_status, order_status, created_at
                ) VALUES (
                    :user_id, :address_id, :total_amount, :payment_method, 
                    :payment_status, :order_status, NOW()
                )";
            
            $stmt = $this->conn->prepare($sql);
            
            // Clean data
            $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
            
            // Set default values if not provided
            if (!isset($this->payment_status)) $this->payment_status = 'pending';
            if (!isset($this->order_status)) $this->order_status = 'processing';
            
            // Bind parameters
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':address_id', $this->address_id);
            $stmt->bindParam(':total_amount', $this->total_amount);
            $stmt->bindParam(':payment_method', $this->payment_method);
            $stmt->bindParam(':payment_status', $this->payment_status);
            $stmt->bindParam(':order_status', $this->order_status);
            
            $stmt->execute();
            
            $order_id = $this->conn->lastInsertId();
            
            // Commit transaction
            $this->conn->commit();
            
            return $order_id;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Add items to an order
     *
     * @param int $order_id Order ID
     * @param array $items Array of items to add [product_id, quantity, price]
     * @return boolean Success or failure
     */
    public function addOrderItems($order_id, $items) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (:order_id, :product_id, :quantity, :price)";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($items as $item) {
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
                
                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Update product stock after order
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity to subtract from stock
     * @return boolean Success or failure
     */
    private function updateProductStock($product_id, $quantity) {
        $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Update order status
     *
     * @param int $order_id Order ID
     * @param string $order_status New order status
     * @param string $payment_status New payment status (optional)
     * @return boolean Success or failure
     */
    public function updateStatus($order_id, $order_status, $payment_status = null) {
        $sql = "UPDATE orders SET order_status = :order_status";
        
        if ($payment_status !== null) {
            $sql .= ", payment_status = :payment_status";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_status', $order_status);
        
        if ($payment_status !== null) {
            $stmt->bindParam(':payment_status', $payment_status);
        }
        
        $stmt->bindParam(':id', $order_id);
        
        return $stmt->execute();
    }
    
    /**
     * Cancel an order and restore product stock
     *
     * @param int $order_id Order ID
     * @return boolean Success or failure
     */
    public function cancelOrder($order_id) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Get order items
            $items = $this->getOrderItems($order_id);
            
            // Restore product stock for each item
            foreach ($items as $item) {
                $sql = "UPDATE products SET stock = stock + :quantity WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':id', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            // Update order status
            $sql = "UPDATE orders SET order_status = 'cancelled' WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $order_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Get order statistics
     *
     * @return array Order statistics
     */
    public function getStatistics() {
        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0
        ];
        
        // Total Orders
        $sql = "SELECT COUNT(*) FROM orders";
        $stmt = $this->conn->query($sql);
        $stats['total_orders'] = $stmt->fetchColumn();
        
        // Total Revenue
        $sql = "SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetchColumn();
        $stats['total_revenue'] = $result ? $result : 0;
        
        // Pending Orders
        $sql = "SELECT COUNT(*) FROM orders WHERE order_status = 'processing'";
        $stmt = $this->conn->query($sql);
        $stats['pending_orders'] = $stmt->fetchColumn();
        
        // Completed Orders
        $sql = "SELECT COUNT(*) FROM orders WHERE order_status = 'delivered'";
        $stmt = $this->conn->query($sql);
        $stats['completed_orders'] = $stmt->fetchColumn();
        
        // Cancelled Orders
        $sql = "SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'";
        $stmt = $this->conn->query($sql);
        $stats['cancelled_orders'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get monthly revenue data for charts
     *
     * @param int $months Number of months to include
     * @return array Monthly revenue data
     */
    public function getMonthlyRevenue($months = 6) {
        $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                SUM(total_amount) as revenue
                FROM orders
                WHERE payment_status = 'paid'
                AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
                GROUP BY month
                ORDER BY month ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top selling products
     *
     * @param int $limit Number of products to return
     * @return array Top selling products
     */
    public function getTopSellingProducts($limit = 5) {
        $sql = "SELECT p.id, p.name, p.image_url, p.slug, 
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.payment_status = 'paid'
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent orders for dashboard
     *
     * @param int $limit Number of orders to return
     * @return array Recent orders
     */
    public function getRecentOrders($limit = 5) {
        $sql = "SELECT o.*, u.username, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
