<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/mUsuario.php';

class daoUsuario{
    private $conn;
    private $table = "usuarios";

    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function crearUsuario(Usuario $usuario){
        $query = "INSERT INTO " . $this->table . "
        (username, password, nombres, apellidos, dni, email, rolId, estado)
        VALUES (:username, :password, :nombres, :apellidos, :dni, :email, :rolId, :estado)";

        $stmt = $this->conn->prepare($query);

        $username = $usuario->getUsername();
        $passwordHash = password_hash($usuario->getPassword(), PASSWORD_BCRYPT);
        $nombres = $usuario->getNombres();
        $apellidos = $usuario->getApellidos();
        $dni = $usuario->getDni();
        $email = $usuario->getEmail();
        $rolId = $usuario->getRolid();
        $estado = $usuario->getEstado();

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":dni", $dni);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":rolId", $rolId);
        $stmt->bindParam(":estado", $estado);

        if ($stmt->execute()){
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function listarUsuario($pagina = 1, $porPagina = 10){
        $inicio = ($pagina - 1) * $porPagina;
        
        $query = "SELECT u.*, r.nombreRol 
                  FROM " . $this->table . " u 
                  INNER JOIN roles r ON u.rolId = r.idRol
                  WHERE u.estado = 'Activo' 
                  ORDER BY u.fecha_creacion DESC
                  LIMIT :inicio, :porPagina";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $stmt->bindParam(":porPagina", $porPagina, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarUsuarios() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE estado = 'Activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function obtenerporIdUsuario($id){
        $query = "SELECT u.*, r.nombreRol 
                  FROM " . $this->table . " u 
                  INNER JOIN roles r ON u.rolId = r.idRol 
                  WHERE u.idUsuario = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUsuario(Usuario $usuario) {
        $query = "UPDATE " . $this->table . " 
                  SET username = :username, 
                      nombres = :nombres, 
                      apellidos = :apellidos,
                      dni = :dni, 
                      email = :email, 
                      rolId = :rolId, 
                      estado = :estado";
        
        if (!empty($usuario->getPassword())) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE idUsuario = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $username = $usuario->getUsername();
        $nombres = $usuario->getNombres();
        $apellidos = $usuario->getApellidos();
        $dni = $usuario->getDni();
        $email = $usuario->getEmail();
        $rolId = $usuario->getRolid();
        $estado = $usuario->getEstado();
        $id = $usuario->getIdUsuario();
        
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":dni", $dni);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":rolId", $rolId);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if (!empty($usuario->getPassword())) {
            $passwordHash = password_hash($usuario->getPassword(), PASSWORD_BCRYPT);
            $stmt->bindParam(":password", $passwordHash);
        }
        
        return $stmt->execute();
    }

    public function desactivarUsuario($id) {
        $query = "UPDATE " . $this->table . " SET estado = 'Inactivo' WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarPorUsername($username) {
        $query = "SELECT u.*, r.nombreRol 
                  FROM " . $this->table . " u 
                  INNER JOIN roles r ON u.rolId = r.idRol 
                  WHERE u.username = :username AND u.estado = 'Activo'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeUsername($username, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE username = :username";
        
        if ($excluirId) {
            $query .= " AND idUsuario != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        
        if ($excluirId) {
            $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }

    public function obtenerRoles() {
        $query = "SELECT * FROM roles ORDER BY nombreRol";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}