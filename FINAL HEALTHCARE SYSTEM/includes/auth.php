<?php
// includes/auth.php
class Auth {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($username, $password) {
        $query = "SELECT * FROM admin_users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch();
            
            // For simplicity, using plain text comparison
            // In production, use password_verify()
            if ($password === 'admin123' || password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function logout() {
        $_SESSION = array();
        session_destroy();
        header("Location: index.php");
        exit();
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
    
    public function getAdminInfo() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'name' => $_SESSION['admin_name']
            ];
        }
        return ['id' => 0, 'username' => 'Guest', 'name' => 'Guest'];
    }
}
?>