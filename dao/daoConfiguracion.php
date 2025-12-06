<?php
require_once __DIR__ . '/../config/Database.php';

class daoConfiguracion {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    
    
    public function listarCategorias() {
        $query = "SELECT c.*,
                         COUNT(DISTINCT p.idProducto) as total_productos
                  FROM categorias c
                  LEFT JOIN productos p ON c.idCategoria = p.categoriaId
                  GROUP BY c.idCategoria
                  ORDER BY c.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function crearCategoria($nombre, $descripcion) {
        $query = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function actualizarCategoria($id, $nombre, $descripcion) {
        $query = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE idCategoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        return $stmt->execute();
    }
    
    public function eliminarCategoria($id) {
        
        $query = "SELECT COUNT(*) as total FROM productos WHERE categoriaId = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            return array('success' => false, 'message' => 'No se puede eliminar. Tiene ' . $row['total'] . ' productos asociados');
        }
        
        $query = "DELETE FROM categorias WHERE idCategoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    public function obtenerCategoriaPorId($id) {
        $query = "SELECT * FROM categorias WHERE idCategoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    
    public function listarEstadosPedido() {
        $query = "SELECT e.*,
                         COUNT(DISTINCT p.idPedido) as total_pedidos
                  FROM estado_pedido e
                  LEFT JOIN pedidos p ON e.idEstado = p.estadoId
                  GROUP BY e.idEstado
                  ORDER BY e.idEstado";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function actualizarEstadoPedido($id, $nombre, $descripcion) {
        $query = "UPDATE estado_pedido SET nombre = :nombre, descripcion = :descripcion WHERE idEstado = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        return $stmt->execute();
    }
    
    
    
    public function listarRoles() {
        $query = "SELECT r.*,
                         COUNT(DISTINCT u.idUsuario) as total_usuarios
                  FROM roles r
                  LEFT JOIN usuarios u ON r.idRol = u.rolId
                  GROUP BY r.idRol
                  ORDER BY r.nombreRol";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function crearRol($nombre, $descripcion) {
        $query = "INSERT INTO roles (nombreRol, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function actualizarRol($id, $nombre, $descripcion) {
        $query = "UPDATE roles SET nombreRol = :nombre, descripcion = :descripcion WHERE idRol = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        return $stmt->execute();
    }
    
    public function eliminarRol($id) {
        
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE rolId = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            return array('success' => false, 'message' => 'No se puede eliminar. Tiene ' . $row['total'] . ' usuarios asociados');
        }
        
        $query = "DELETE FROM roles WHERE idRol = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    
    
    public function obtenerEstadisticasSistema() {
        $stats = array();
        
        
        $tablas = ['usuarios', 'proveedores', 'productos', 'pedidos', 'gastos', 'categorias', 'roles'];
        
        foreach ($tablas as $tabla) {
            $query = "SELECT COUNT(*) as total FROM " . $tabla;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$tabla] = $row['total'];
        }
        
        
        $query = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                  FROM information_schema.TABLES 
                  WHERE table_schema = DATABASE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['db_size'] = $row['size_mb'];
        
        
        $query = "SELECT COUNT(*) as total FROM auditoria";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['auditoria'] = $row['total'];
        
        return $stats;
    }
    
    
    
    public function listarAuditoria($limite = 100, $filtros = array()) {
        $query = "SELECT a.*, u.nombres, u.apellidos
                  FROM auditoria a
                  LEFT JOIN usuarios u ON a.usuarioId = u.idUsuario
                  WHERE 1=1";
        
        if (!empty($filtros['tabla'])) {
            $query .= " AND a.tabla_afectada = :tabla";
        }
        
        if (!empty($filtros['accion'])) {
            $query .= " AND a.accion = :accion";
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND DATE(a.fecha_accion) >= :fecha_desde";
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND DATE(a.fecha_accion) <= :fecha_hasta";
        }
        
        $query .= " ORDER BY a.fecha_accion DESC LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['tabla'])) {
            $stmt->bindParam(":tabla", $filtros['tabla']);
        }
        if (!empty($filtros['accion'])) {
            $stmt->bindParam(":accion", $filtros['accion']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $stmt->bindParam(":fecha_desde", $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $stmt->bindParam(":fecha_hasta", $filtros['fecha_hasta']);
        }
        
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function limpiarAuditoriaAntigua($dias = 90) {
        $query = "DELETE FROM auditoria WHERE fecha_accion < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    
    
    public function generarRespaldo() {
        $dbname = $this->conn->query("SELECT DATABASE()")->fetchColumn();
        $tables = array();
        
        
        $result = $this->conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $backup = "-- Respaldo de Base de Datos: {$dbname}\n";
        $backup .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            
            $result = $this->conn->query("SHOW CREATE TABLE `{$table}`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $backup .= "-- Estructura de tabla `{$table}`\n";
            $backup .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $backup .= $row[1] . ";\n\n";
            
            
            $result = $this->conn->query("SELECT * FROM `{$table}`");
            $numRows = $result->rowCount();
            
            if ($numRows > 0) {
                $backup .= "-- Datos de tabla `{$table}`\n";
                
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $backup .= "INSERT INTO `{$table}` VALUES (";
                    $values = array();
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $backup .= implode(", ", $values) . ");\n";
                }
                $backup .= "\n";
            }
        }
        
        $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        return $backup;
    }
    
    
    
    public function obtenerSesionesActivas() {
        $query = "SELECT s.*, u.nombres, u.apellidos, u.username
                  FROM sesiones s
                  INNER JOIN usuarios u ON s.usuarioId = u.idUsuario
                  WHERE s.activa = 1
                  ORDER BY s.fecha_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function cerrarSesion($idSesion) {
        $query = "UPDATE sesiones SET activa = 0 WHERE idSesion = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idSesion);
        return $stmt->execute();
    }
}
?>