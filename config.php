<?php
/**
 * Configuration File - OOP Database Connection
 * 
 * WHY OOP CONFIGURATION?
 * - Single point of configuration
 * - Easy to switch between environments (dev/prod)
 * - Centralized database access
 */

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Get database instance (Singleton pattern)
// Wrap in try-catch to handle MySQL connection errors gracefully
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Initialize Data Processor
    $dataProcessor = new DataProcessor($db);
} catch (Exception $e) {
    // MySQL is not running or connection failed
    // Set variables to null so pages don't crash
    $db = null;
    $conn = null;
    $dataProcessor = null;
    
    // Only show error if we're on a test/debug page
    if (basename($_SERVER['PHP_SELF']) == 'test_connection.php' || 
        basename($_SERVER['PHP_SELF']) == 'test_simple.php') {
        echo "⚠️ Database Connection Error: " . $e->getMessage() . "<br>";
        echo "Please make sure MySQL is running in XAMPP.<br>";
    }
}

// Initialize API (if needed)
// $api = new API($db, $dataProcessor);