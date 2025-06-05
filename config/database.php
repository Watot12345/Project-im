<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'inventory_system';
    private $username = 'root';
    private $password = '';
    public $conn;   

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    // If you really need this method (though it's redundant since you have getConnection)
    public function getPDO() {
        return $this->getConnection(); // Better to use the existing connection method
    }
}
?>