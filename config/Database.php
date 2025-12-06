<?php
require_once __DIR__ .'/env.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct(){
        $this->host     = $_ENV['DB_HOST'];
        $this->db_name  = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    public function getConnection(){
        $this->conn = null;

        try {

            // 🚀 DSN compatible con Railway (con charset incluido)
            $dsn = "mysql:host={$this->host};port=13178;dbname={$this->db_name};charset={$_ENV['DB_CHARSET']}";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );

        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            die("Error de conexión DB: " . $e->getMessage());
        }

        return $this->conn;
    }
}