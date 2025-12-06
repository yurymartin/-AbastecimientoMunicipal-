<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/mProveedor.php';

class daoProveedor {
    private $conn;
    private $table = "proveedores";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function crearProveedor(Proveedor $proveedor) {
        $query = "INSERT INTO " . $this->table . "
                  (ruc, razon_social, nombre_comercial, direccion, telefono, 
                   email, contacto_nombre, contacto_telefono, estado, usuario_registro)
                  VALUES (:ruc, :razon_social, :nombre_comercial, :direccion, :telefono,
                          :email, :contacto_nombre, :contacto_telefono, :estado, :usuario_registro)";
        
        $stmt = $this->conn->prepare($query);
        
        $ruc = $proveedor->getRuc();
        $razon_social = $proveedor->getRazonSocial();
        $nombre_comercial = $proveedor->getNombreComercial();
        $direccion = $proveedor->getDireccion();
        $telefono = $proveedor->getTelefono();
        $email = $proveedor->getEmail();
        $contacto_nombre = $proveedor->getContactoNombre();
        $contacto_telefono = $proveedor->getContactoTelefono();
        $estado = $proveedor->getEstado();
        $usuario_registro = $proveedor->getUsuarioRegistro();
        
        $stmt->bindParam(":ruc", $ruc);
        $stmt->bindParam(":razon_social", $razon_social);
        $stmt->bindParam(":nombre_comercial", $nombre_comercial);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":contacto_nombre", $contacto_nombre);
        $stmt->bindParam(":contacto_telefono", $contacto_telefono);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":usuario_registro", $usuario_registro);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function listarProveedor($soloActivos = false) {
        $query = "SELECT * FROM " . $this->table;
        
        if ($soloActivos) {
            $query .= " WHERE estado = 'Activo'";
        }
        
        $query .= " ORDER BY idProveedor DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $proveedores = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $proveedores[] = $row;
        }
        
        return $proveedores;
    }
    
    public function obtenerPorIdProveedor($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE idProveedor = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function actualizarProveedor(Proveedor $proveedor) {
        $query = "UPDATE " . $this->table . "
                  SET ruc = :ruc,
                      razon_social = :razon_social,
                      nombre_comercial = :nombre_comercial,
                      direccion = :direccion,
                      telefono = :telefono,
                      email = :email,
                      contacto_nombre = :contacto_nombre,
                      contacto_telefono = :contacto_telefono,
                      estado = :estado
                  WHERE idProveedor = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $id = $proveedor->getIdProveedor();
        $ruc = $proveedor->getRuc();
        $razon_social = $proveedor->getRazonSocial();
        $nombre_comercial = $proveedor->getNombreComercial();
        $direccion = $proveedor->getDireccion();
        $telefono = $proveedor->getTelefono();
        $email = $proveedor->getEmail();
        $contacto_nombre = $proveedor->getContactoNombre();
        $contacto_telefono = $proveedor->getContactoTelefono();
        $estado = $proveedor->getEstado();
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":ruc", $ruc);
        $stmt->bindParam(":razon_social", $razon_social);
        $stmt->bindParam(":nombre_comercial", $nombre_comercial);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":contacto_nombre", $contacto_nombre);
        $stmt->bindParam(":contacto_telefono", $contacto_telefono);
        $stmt->bindParam(":estado", $estado);
        
        return $stmt->execute();
    }
    
    public function desactivarProveedor($id) {
        $query = "UPDATE " . $this->table . " SET estado = 'Inactivo' WHERE idProveedor = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    public function existeRuc($ruc, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE ruc = :ruc";
        
        if ($excluirId) {
            $query .= " AND idProveedor != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ruc", $ruc);
        
        if ($excluirId) {
            $stmt->bindParam(":id", $excluirId);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }
    
    public function buscarProveedor($termino) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE (razon_social LIKE :termino
                         OR ruc LIKE :termino
                         OR nombre_comercial LIKE :termino)
                  AND estado = 'Activo'
                  ORDER BY razon_social ASC";
        
        $stmt = $this->conn->prepare($query);
        $terminoBusqueda = "%{$termino}%";
        $stmt->bindParam(":termino", $terminoBusqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTotalProveedores() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE estado = 'Activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>