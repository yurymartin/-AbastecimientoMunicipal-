<?php
require_once __DIR__ . '/../config/Database.php';

class daoReportes {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function obtenerReportePedidos($fechaInicio, $fechaFin, $estadoId = null, $proveedorId = null) {
        $query = "SELECT p.*, 
                         prov.razon_social, prov.ruc,
                         ep.nombre as estado_nombre,
                         u.nombres as usuario_nombre, u.apellidos as usuario_apellidos,
                         COUNT(dp.idDetalle) as cantidad_items
                  FROM pedidos p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                  INNER JOIN usuarios u ON p.usuario_solicita = u.idUsuario
                  LEFT JOIN detalle_pedido dp ON p.idPedido = dp.pedidoId
                  WHERE p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";
        
        if ($estadoId) {
            $query .= " AND p.estadoId = :estadoId";
        }
        
        if ($proveedorId) {
            $query .= " AND p.proveedorId = :proveedorId";
        }
        
        $query .= " GROUP BY p.idPedido ORDER BY p.fecha_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        
        if ($estadoId) {
            $stmt->bindParam(":estadoId", $estadoId);
        }
        
        if ($proveedorId) {
            $stmt->bindParam(":proveedorId", $proveedorId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerReporteGastos($fechaInicio, $fechaFin, $tipoGasto = null) {
        $query = "SELECT g.*, 
                         p.numero_pedido,
                         prov.razon_social,
                         u.nombres as usuario_nombre, u.apellidos as usuario_apellidos
                  FROM gastos g
                  INNER JOIN pedidos p ON g.pedidoId = p.idPedido
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN usuarios u ON g.usuario_registro = u.idUsuario
                  WHERE g.fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin";
        
        if ($tipoGasto) {
            $query .= " AND g.tipo_gasto = :tipo_gasto";
        }
        
        $query .= " ORDER BY g.fecha_gasto DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        
        if ($tipoGasto) {
            $stmt->bindParam(":tipo_gasto", $tipoGasto);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerReporteProveedores() {
        $query = "SELECT prov.*,
                         COUNT(DISTINCT p.idPedido) as total_pedidos,
                         COALESCE(SUM(p.total), 0) as monto_total_pedidos,
                         MAX(p.fecha_pedido) as ultima_compra
                  FROM proveedores prov
                  LEFT JOIN pedidos p ON prov.idProveedor = p.proveedorId
                  GROUP BY prov.idProveedor
                  ORDER BY total_pedidos DESC, monto_total_pedidos DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerReporteProductos() {
        $query = "SELECT pr.*,
                         c.nombre as categoria_nombre,
                         COUNT(DISTINCT dp.pedidoId) as veces_pedido,
                         COALESCE(SUM(dp.cantidad), 0) as cantidad_total_pedida,
                         CASE 
                             WHEN pr.stock_actual <= 0 THEN 'Sin Stock'
                             WHEN pr.stock_actual <= pr.stock_minimo THEN 'Crítico'
                             WHEN pr.stock_actual <= (pr.stock_minimo * 1.5) THEN 'Bajo'
                             ELSE 'Normal'
                         END AS estado_stock
                  FROM productos pr
                  LEFT JOIN categorias c ON pr.categoriaId = c.idCategoria
                  LEFT JOIN detalle_pedido dp ON pr.idProducto = dp.productoId
                  WHERE pr.estado = 'Activo'
                  GROUP BY pr.idProducto
                  ORDER BY veces_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticasPedidosPorEstado($fechaInicio, $fechaFin) {
        $query = "SELECT ep.nombre as estado,
                         COUNT(p.idPedido) as cantidad,
                         COALESCE(SUM(p.total), 0) as monto_total
                  FROM estado_pedido ep
                  LEFT JOIN pedidos p ON ep.idEstado = p.estadoId 
                      AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
                  GROUP BY ep.idEstado, ep.nombre
                  ORDER BY ep.idEstado";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticasPedidosPorMes($anio) {
        $query = "SELECT 
                         MONTH(fecha_pedido) as mes,
                         MONTHNAME(fecha_pedido) as mes_nombre,
                         COUNT(idPedido) as cantidad,
                         COALESCE(SUM(total), 0) as monto_total
                  FROM pedidos
                  WHERE YEAR(fecha_pedido) = :anio
                  GROUP BY MONTH(fecha_pedido), MONTHNAME(fecha_pedido)
                  ORDER BY MONTH(fecha_pedido)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":anio", $anio);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTopProveedores($limite = 10, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT prov.razon_social,
                         COUNT(p.idPedido) as total_pedidos,
                         COALESCE(SUM(p.total), 0) as monto_total
                  FROM proveedores prov
                  INNER JOIN pedidos p ON prov.idProveedor = p.proveedorId
                  WHERE 1=1";
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";
        }
        
        $query .= " GROUP BY prov.idProveedor, prov.razon_social
                   ORDER BY monto_total DESC
                   LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindParam(":fecha_inicio", $fechaInicio);
            $stmt->bindParam(":fecha_fin", $fechaFin);
        }
        
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTopProductos($limite = 10, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT pr.nombre_producto,
                         pr.codigo_producto,
                         c.nombre as categoria,
                         COALESCE(SUM(dp.cantidad), 0) as cantidad_total,
                         COUNT(DISTINCT dp.pedidoId) as veces_pedido
                  FROM productos pr
                  LEFT JOIN categorias c ON pr.categoriaId = c.idCategoria
                  INNER JOIN detalle_pedido dp ON pr.idProducto = dp.productoId
                  INNER JOIN pedidos p ON dp.pedidoId = p.idPedido
                  WHERE 1=1";
        
        if ($fechaInicio && $fechaFin) {
            $query .= " AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";
        }
        
        $query .= " GROUP BY pr.idProducto, pr.nombre_producto, pr.codigo_producto, c.nombre
                   ORDER BY cantidad_total DESC
                   LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindParam(":fecha_inicio", $fechaInicio);
            $stmt->bindParam(":fecha_fin", $fechaFin);
        }
        
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticasGastosPorTipo($fechaInicio, $fechaFin) {
        $query = "SELECT tipo_gasto,
                         COUNT(idGasto) as cantidad,
                         COALESCE(SUM(monto), 0) as monto_total
                  FROM gastos
                  WHERE fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin
                  GROUP BY tipo_gasto
                  ORDER BY monto_total DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTiposGastos() {
        $query = "SELECT DISTINCT tipo_gasto FROM gastos ORDER BY tipo_gasto";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerResumenGeneral($fechaInicio, $fechaFin) {
        $query = "SELECT 
                         COUNT(DISTINCT p.idPedido) as total_pedidos,
                         COALESCE(SUM(p.total), 0) as monto_total_pedidos,
                         COUNT(DISTINCT p.proveedorId) as total_proveedores,
                         COUNT(DISTINCT dp.productoId) as total_productos_pedidos,
                         (SELECT COUNT(DISTINCT idGasto) FROM gastos 
                          WHERE fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin) as total_gastos,
                         (SELECT COALESCE(SUM(monto), 0) FROM gastos 
                          WHERE fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin) as monto_total_gastos
                  FROM pedidos p
                  LEFT JOIN detalle_pedido dp ON p.idPedido = dp.pedidoId
                  WHERE p.fecha_pedido BETWEEN :fecha_inicio2 AND :fecha_fin2";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fechaInicio);
        $stmt->bindParam(":fecha_fin", $fechaFin);
        $stmt->bindParam(":fecha_inicio2", $fechaInicio);
        $stmt->bindParam(":fecha_fin2", $fechaFin);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>