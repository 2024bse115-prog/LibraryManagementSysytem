<?php
/**
 * Database Class - OOP Database Connection Handler
 * 
 * WHY OOP?
 * - Encapsulation: Database credentials and connection logic are contained
 * - Reusability: Can be instantiated anywhere without code duplication
 * - Maintainability: Changes to connection logic only need to be made in one place
 * - Testability: Can be easily mocked for unit testing
 * - Security: Prepared statements are enforced through class methods
 */
class Database {
    private $host;
    private $user;
    private $password;
    private $database;
    private $conn;
    private static $instance = null;
    
    /**
     * WHY SINGLETON PATTERN?
     * - Prevents multiple database connections (resource efficiency)
     * - Ensures only one connection exists throughout the application
     * - Reduces memory usage and connection overhead
     */
    private function __construct() {
        $this->host = '10.48.20.7';  // Database server (on separate computer)
        $this->user = 'remote_user';//
        $this->password = 'jonah@1170';//
        $this->database = 'libraryms';
        $this->connect();
    }
    
    /**
     * Get singleton instance
     * WHY STATIC METHOD?
     * - Allows global access without creating multiple instances
     * - Follows singleton pattern for database connections
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * WHY PRIVATE METHOD?
     * - Connection logic is internal to the class
     * - Prevents external code from creating multiple connections
     */
    private function connect() {
        // Connect to MySQL on port 3307 (XAMPP default, or change to 3306 if needed)
        // If your MySQL uses default port 3306, remove the port parameter
        // Set connection timeout to prevent hanging
        ini_set('default_socket_timeout', 5); // 5 second timeout
        
        $this->conn = @new mysqli($this->host, $this->user, $this->password, $this->database, 3306);
        
        if ($this->conn->connect_error) {
            throw new Exception('Connection FAILED: ' . $this->conn->connect_error . ' (Check if MySQL is running on port 3307)');
        }
        
        // Set charset to prevent encoding issues
        $this->conn->set_charset("utf8mb4");
    }
    
    /**
     * Get connection object
     * WHY GETTER METHOD?
     * - Controlled access to connection
     * - Can add logging/validation before returning connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute prepared statement
     * WHY PREPARED STATEMENTS?
     * - SQL Injection Prevention: Parameters are escaped automatically
     * - Performance: Query is compiled once, executed multiple times
     * - Security: Primary defense against SQL injection attacks
     */
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // All params as strings
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Close connection
     * WHY EXPLICIT CLOSE?
     * - Resource management: Frees database connections
     * - Good practice for long-running scripts
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Prevent cloning (singleton pattern)
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization (singleton pattern)
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

