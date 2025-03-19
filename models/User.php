<?php
/**
 * User Model
 * 
 * This class represents the User entity and handles database operations related to users.
 */
class User {
    private $conn;
    
    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $phone;
    public $is_admin;
    public $newsletter;
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
     * Get all users
     *
     * @param int $limit Limit the number of users returned
     * @param int $offset Offset for pagination
     * @param array $filters Optional filters
     * @return array Array of users
     */
    public function getAll($limit = 10, $offset = 0, $filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['is_admin'])) {
            $whereClause[] = "is_admin = :is_admin";
            $params[':is_admin'] = $filters['is_admin'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        $sql = "SELECT * FROM users";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }
        
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
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
     * Get total count of users with applied filters
     *
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function getCount($filters = []) {
        $whereClause = [];
        $params = [];
        
        // Apply filters if provided
        if (isset($filters['is_admin'])) {
            $whereClause[] = "is_admin = :is_admin";
            $params[':is_admin'] = $filters['is_admin'];
        }
        
        if (isset($filters['search'])) {
            $whereClause[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
            $params[':search'] = "%" . $filters['search'] . "%";
        }
        
        $sql = "SELECT COUNT(*) FROM users";
        
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
     * Get a single user by ID
     *
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single user by username
     *
     * @param string $username Username
     * @return array|false User data or false if not found
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single user by email
     *
     * @param string $email Email address
     * @return array|false User data or false if not found
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new user
     *
     * @return boolean Success or failure
     */
    public function create() {
        // Check if username or email already exists
        if ($this->isUsernameOrEmailExists()) {
            return false;
        }
        
        $sql = "INSERT INTO users (
                username, email, password, first_name, last_name, 
                phone, is_admin, newsletter, created_at
            ) VALUES (
                :username, :email, :password, :first_name, :last_name, 
                :phone, :is_admin, :newsletter, NOW()
            )";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Set default values if not provided
        if (!isset($this->is_admin)) $this->is_admin = 0;
        if (!isset($this->newsletter)) $this->newsletter = 0;
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':is_admin', $this->is_admin);
        $stmt->bindParam(':newsletter', $this->newsletter);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update an existing user
     *
     * @return boolean Success or failure
     */
    public function update() {
        $sql = "UPDATE users SET 
                username = :username,
                email = :email,
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                is_admin = :is_admin,
                newsletter = :newsletter
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Clean and sanitize data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':is_admin', $this->is_admin);
        $stmt->bindParam(':newsletter', $this->newsletter);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Update user password
     *
     * @param string $new_password New password
     * @return boolean Success or failure
     */
    public function updatePassword($new_password) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        return $stmt->execute();
    }
    
    /**
     * Delete a user
     *
     * @return boolean Success or failure
     */
    public function delete() {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Delete user's addresses
            $sql = "DELETE FROM addresses WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->id);
            $stmt->execute();
            
            // Update reviews to anonymous
            $sql = "UPDATE reviews SET user_id = NULL WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->id);
            $stmt->execute();
            
            // Update orders to maintain history but without user association
            $sql = "UPDATE orders SET user_id = NULL WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->id);
            $stmt->execute();
            
            // Delete user
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->id);
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
     * Verify if a password matches the stored hash
     *
     * @param string $password Password to verify
     * @return boolean True if password matches, false otherwise
     */
    public function verifyPassword($password) {
        $sql = "SELECT password FROM users WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return password_verify($password, $row['password']);
        }
        
        return false;
    }
    
    /**
     * Check if username or email already exists
     *
     * @return boolean True if exists, false otherwise
     */
    private function isUsernameOrEmailExists() {
        $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get user addresses
     *
     * @return array Array of addresses
     */
    public function getAddresses() {
        $sql = "SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add a new address for the user
     *
     * @param array $address_data Address data
     * @return boolean Success or failure
     */
    public function addAddress($address_data) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // If this is the default address, unset other defaults
            if (isset($address_data['is_default']) && $address_data['is_default']) {
                $sql = "UPDATE addresses SET is_default = 0 WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':user_id', $this->id);
                $stmt->execute();
            }
            
            // If this is the first address, make it default
            $sql = "SELECT COUNT(*) FROM addresses WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->id);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            if ($count === 0) {
                $address_data['is_default'] = 1;
            }
            
            // Insert the new address
            $sql = "INSERT INTO addresses (
                    user_id, address, city, region, postal_code, country, is_default
                ) VALUES (
                    :user_id, :address, :city, :region, :postal_code, :country, :is_default
                )";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':user_id', $this->id);
            $stmt->bindParam(':address', $address_data['address']);
            $stmt->bindParam(':city', $address_data['city']);
            $stmt->bindParam(':region', $address_data['region']);
            $stmt->bindParam(':postal_code', $address_data['postal_code']);
            $stmt->bindParam(':country', $address_data['country']);
            $stmt->bindParam(':is_default', $address_data['is_default']);
            
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return $this->conn->lastInsertId();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Update an existing address
     *
     * @param int $address_id Address ID
     * @param array $address_data Address data
     * @return boolean Success or failure
     */
    public function updateAddress($address_id, $address_data) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // If this is the default address, unset other defaults
            if (isset($address_data['is_default']) && $address_data['is_default']) {
                $sql = "UPDATE addresses SET is_default = 0 WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':user_id', $this->id);
                $stmt->execute();
            }
            
            // Update the address
            $sql = "UPDATE addresses SET 
                    address = :address,
                    city = :city,
                    region = :region,
                    postal_code = :postal_code,
                    country = :country,
                    is_default = :is_default
                    WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':address', $address_data['address']);
            $stmt->bindParam(':city', $address_data['city']);
            $stmt->bindParam(':region', $address_data['region']);
            $stmt->bindParam(':postal_code', $address_data['postal_code']);
            $stmt->bindParam(':country', $address_data['country']);
            $stmt->bindParam(':is_default', $address_data['is_default']);
            $stmt->bindParam(':id', $address_id);
            $stmt->bindParam(':user_id', $this->id);
            
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
     * Delete an address
     *
     * @param int $address_id Address ID
     * @return boolean Success or failure
     */
    public function deleteAddress($address_id) {
        // Don't allow deletion of default address
        $sql = "SELECT is_default FROM addresses WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $address_id);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->execute();
        
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($address && $address['is_default']) {
            return false;
        }
        
        // Delete the address
        $sql = "DELETE FROM addresses WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $address_id);
        $stmt->bindParam(':user_id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Get user orders with pagination
     *
     * @param int $limit Limit the number of orders
     * @param int $offset Offset for pagination
     * @return array Array of orders
     */
    public function getOrders($limit = 10, $offset = 0) {
        $sql = "SELECT o.*, COUNT(oi.id) as item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = :user_id 
                GROUP BY o.id 
                ORDER BY o.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get count of user orders
     *
     * @return int Order count
     */
    public function getOrderCount() {
        $sql = "SELECT COUNT(*) FROM orders WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Update user newsletter preference
     *
     * @param boolean $subscribe Whether to subscribe or unsubscribe
     * @return boolean Success or failure
     */
    public function updateNewsletter($subscribe) {
        $sql = "UPDATE users SET newsletter = :newsletter WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $newsletter = $subscribe ? 1 : 0;
        $stmt->bindParam(':newsletter', $newsletter);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
}
?>
