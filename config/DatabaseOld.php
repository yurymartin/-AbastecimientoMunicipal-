<?php
require_once __DIR__ .'/env.php';

class Database{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct(){
        $this -> host = $_ENV['DB_HOST'];
        $this -> db_name = $_ENV['DB_NAME'];
        $this -> username = $_ENV['DB_USER'];
        $this -> password = $_ENV['DB_PASS'];
    }

    public function getConnection(){
        $this -> conn = null;

        try {
            $this -> conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$_ENV['DB_CHARSET'])
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
        }

        return $this->conn;
    }
}