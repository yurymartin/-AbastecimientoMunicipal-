<?php
require_once __DIR__ . '/../config/Database.php';

class daoGastos {
    private $conn;
    private $table = "gastos";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function crearGasto($datos) {
        $query = "INSERT INTO " . $this->table . "
                  (pedidoId, tipo_gasto, monto, fecha_gasto, tipo_documento,
                   numero_documento, descripcion, usuario_registro)
                  VALUES (:pedido, :tipo, :monto, :fecha, :tipo_doc,
                          :num_doc, :descripcion, :usuario)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":pedido", $datos['pedidoId']);
        $stmt->bindParam(":tipo", $datos['tipo_gasto']);
        $stmt->bindParam(":monto", $datos['monto']);
        $stmt->bindParam(":fecha", $datos['fecha_gasto']);
        $stmt->bindParam(":tipo_doc", $datos['tipo_documento']);
        $stmt->bindParam(":num_doc", $datos['numero_documento']);
        $stmt->bindParam(":descripcion", $datos['descripcion']);
        $stmt->bindParam(":usuario", $datos['usuario_registro']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function listarGastos($filtros = array()) {
        $query = "SELECT g.*, 
                         p.numero_pedido,
                         prov.razon_social,
                         u.nombres as usuario_nombre, u.apellidos as usuario_apellidos
                  FROM " . $this->table . " g
                  INNER JOIN pedidos p ON g.pedidoId = p.idPedido
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN usuarios u ON g.usuario_registro = u.idUsuario
                  WHERE 1=1";
        
        if (!empty($filtros['tipo_gasto'])) {
            $query .= " AND g.tipo_gasto = :tipo_gasto";
        }
        
        if (!empty($filtros['pedidoId'])) {
            $query .= " AND g.pedidoId = :pedidoId";
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND g.fecha_gasto >= :fecha_desde";
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND g.fecha_gasto <= :fecha_hasta";
        }
        
        $query .= " ORDER BY g.fecha_gasto DESC, g.idGasto DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['tipo_gasto'])) {
            $stmt->bindParam(":tipo_gasto", $filtros['tipo_gasto']);
        }
        if (!empty($filtros['pedidoId'])) {
            $stmt->bindParam(":pedidoId", $filtros['pedidoId']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $stmt->bindParam(":fecha_desde", $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $stmt->bindParam(":fecha_hasta", $filtros['fecha_hasta']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorIdGasto($id) {
        $query = "SELECT g.*, 
                         p.numero_pedido,
                         prov.razon_social
                  FROM " . $this->table . " g
                  INNER JOIN pedidos p ON g.pedidoId = p.idPedido
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  WHERE g.idGasto = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function actualizarGasto($datos) {
        $query = "UPDATE " . $this->table . "
                  SET pedidoId = :pedido,
                      tipo_gasto = :tipo,
                      monto = :monto,
                      fecha_gasto = :fecha,
                      tipo_documento = :tipo_doc,
                      numero_documento = :num_doc,
                      descripcion = :descripcion
                  WHERE idGasto = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $datos['idGasto']);
        $stmt->bindParam(":pedido", $datos['pedidoId']);
        $stmt->bindParam(":tipo", $datos['tipo_gasto']);
        $stmt->bindParam(":monto", $datos['monto']);
        $stmt->bindParam(":fecha", $datos['fecha_gasto']);
        $stmt->bindParam(":tipo_doc", $datos['tipo_documento']);
        $stmt->bindParam(":num_doc", $datos['numero_documento']);
        $stmt->bindParam(":descripcion", $datos['descripcion']);
        
        return $stmt->execute();
    }
    
    public function eliminarGasto($id) {
        $query = "DELETE FROM " . $this->table . " WHERE idGasto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    public function obtenerGastosPorPedido($pedidoId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE pedidoId = :pedido 
                  ORDER BY fecha_gasto DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido", $pedidoId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTotalGastos($filtros = array()) {
        $query = "SELECT COALESCE(SUM(monto), 0) as total FROM " . $this->table . " WHERE 1=1";
        
        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND fecha_gasto >= :fecha_desde";
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND fecha_gasto <= :fecha_hasta";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['fecha_desde'])) {
            $stmt->bindParam(":fecha_desde", $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $stmt->bindParam(":fecha_hasta", $filtros['fecha_hasta']);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function obtenerTiposGastos() {
        $query = "SELECT DISTINCT tipo_gasto FROM " . $this->table . " ORDER BY tipo_gasto";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerPedidosDisponibles() {
        $query = "SELECT p.idPedido, p.numero_pedido, prov.razon_social
                  FROM pedidos p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  ORDER BY p.fecha_pedido DESC
                  LIMIT 100";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarGastos($termino) {
        $query = "SELECT g.*, 
                         p.numero_pedido,
                         prov.razon_social
                  FROM " . $this->table . " g
                  INNER JOIN pedidos p ON g.pedidoId = p.idPedido
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  WHERE g.tipo_gasto LIKE :termino
                     OR g.descripcion LIKE :termino
                     OR p.numero_pedido LIKE :termino
                     OR prov.razon_social LIKE :termino
                  ORDER BY g.fecha_gasto DESC";
        
        $stmt = $this->conn->prepare($query);
        $terminoBusqueda = "%{$termino}%";
        $stmt->bindParam(":termino", $terminoBusqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>