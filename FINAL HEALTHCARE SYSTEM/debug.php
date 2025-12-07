<?php
// debug.php
session_start();
echo "<h2>Session Debug Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if database is connected
try {
    require_once 'includes/config.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Check admin users table
    $stmt = $db->query("SELECT * FROM admin_users");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Admin Users Table:</h3>";
    echo "<pre>";
    print_r($admin);
    echo "</pre>";
    
    echo "<p>Default admin credentials:</p>";
    echo "<p>Username: admin</p>";
    echo "<p>Password: admin123</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>