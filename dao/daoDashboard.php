<?php
require_once __DIR__ . '/../config/Database.php';

class daoDashboard {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function contarUsuarios() {
        try {
            $query = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'Activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarUsuarios: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarProveedores() {
        try {
            $query = "SELECT COUNT(*) as total FROM proveedores WHERE estado = 'Activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarProveedores: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarProductos() {
        try {
            $query = "SELECT COUNT(*) as total FROM productos WHERE estado = 'Activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarProductos: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarPedidos() {
        try {
            $query = "SELECT COUNT(*) as total FROM pedidos";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarPedidos: " . $e->getMessage());
            return 0;
        }
    }
    
    public function obtenerPedidosRecientes($limite = 5) {
        try {
            $query = "SELECT 
                        p.numero_pedido,
                        prov.razon_social,
                        p.fecha_pedido,
                        ep.nombre as nombre_estado,
                        p.total
                      FROM pedidos p
                      INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                      INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                      ORDER BY p.fecha_pedido DESC
                      LIMIT :limite";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPedidosRecientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerProductosStockBajo($limite = 10) {
        try {
            $query = "SELECT 
                        codigo_producto,
                        nombre_producto,
                        stock_actual,
                        stock_minimo
                      FROM productos
                      WHERE estado = 'Activo'
                      AND stock_actual <= stock_minimo
                      ORDER BY stock_actual ASC
                      LIMIT :limite";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductosStockBajo: " . $e->getMessage());
            return [];
        }
    }
    
    public function contarPedidosMesActual() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM pedidos 
                      WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE())
                      AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarPedidosMesActual: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarPedidosMesAnterior() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM pedidos 
                      WHERE MONTH(fecha_pedido) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                      AND YEAR(fecha_pedido) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarPedidosMesAnterior: " . $e->getMessage());
            return 0;
        }
    }
    
    public function obtenerGastosMesActual() {
        try {
            $query = "SELECT COALESCE(SUM(monto), 0) as total 
                      FROM gastos 
                      WHERE MONTH(fecha_gasto) = MONTH(CURRENT_DATE())
                      AND YEAR(fecha_gasto) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['total']);
        } catch (PDOException $e) {
            error_log("Error en obtenerGastosMesActual: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarProveedoresNuevosMes() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM proveedores 
                      WHERE MONTH(fecha_registro) = MONTH(CURRENT_DATE())
                      AND YEAR(fecha_registro) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarProveedoresNuevosMes: " . $e->getMessage());
            return 0;
        }
    }
    
    public function contarPedidosPendientes() {
        try {
            $query = "SELECT COUNT(*) as total 
                      FROM pedidos p
                      INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                      WHERE ep.nombre = 'Pendiente'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error en contarPedidosPendientes: " . $e->getMessage());
            return 0;
        }
    }
}
?>