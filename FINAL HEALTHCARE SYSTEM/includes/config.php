<?php
// includes/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host = "localhost";
    private $db_name = "healthcare_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
                    <h3>Database Connection Error</h3>
                    <p><strong>Error:</strong> " . $exception->getMessage() . "</p>
                    <p>Please check:</p>
                    <ul>
                        <li>MySQL service is running</li>
                        <li>Database 'healthcare_system' exists</li>
                        <li>Username and password are correct</li>
                    </ul>
                  </div>";
            die();
        }
        return $this->conn;
    }
}

$database = new Database();
$db = $database->getConnection();
?>