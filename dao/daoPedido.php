<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/mPedido.php';

class daoPedido {
    private $conn;
    private $tablePedidos = "pedidos";
    private $tableDetalle = "detalle_pedido";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function crearPedido(Pedido $pedido) {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->tablePedidos . "
                      (numero_pedido, proveedorId, fecha_pedido, fecha_entrega_estimada,
                       estadoId, subtotal, igv, total, observaciones, usuario_solicita)
                      VALUES (:numero, :proveedor, :fecha_pedido, :fecha_entrega,
                              :estado, :subtotal, :igv, :total, :observaciones, :usuario)";
            
            $stmt = $this->conn->prepare($query);
            
            $numero = $pedido->getNumeroPedido();
            $proveedor = $pedido->getProveedorId();
            $fecha_pedido = $pedido->getFechaPedido();
            $fecha_entrega = $pedido->getFechaEntregaEstimada();
            $estado = $pedido->getEstadoId();
            $subtotal = $pedido->getSubtotal();
            $igv = $pedido->getIgv();
            $total = $pedido->getTotal();
            $observaciones = $pedido->getObservaciones();
            $usuario = $pedido->getUsuarioSolicita();
            
            $stmt->bindParam(":numero", $numero);
            $stmt->bindParam(":proveedor", $proveedor);
            $stmt->bindParam(":fecha_pedido", $fecha_pedido);
            $stmt->bindParam(":fecha_entrega", $fecha_entrega);
            $stmt->bindParam(":estado", $estado);
            $stmt->bindParam(":subtotal", $subtotal);
            $stmt->bindParam(":igv", $igv);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":observaciones", $observaciones);
            $stmt->bindParam(":usuario", $usuario);
            
            if ($stmt->execute()) {
                $idPedido = $this->conn->lastInsertId();
                $this->conn->commit();
                return $idPedido;
            }
            
            $this->conn->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    public function crearDetallePedido(DetallePedido $detalle) {
        $query = "INSERT INTO " . $this->tableDetalle . "
                  (pedidoId, productoId, cantidad, precio_unitario, subtotal, estado)
                  VALUES (:pedido, :producto, :cantidad, :precio, :subtotal, :estado)";
        
        $stmt = $this->conn->prepare($query);
        
        $pedidoId = $detalle->getPedidoId();
        $productoId = $detalle->getProductoId();
        $cantidad = $detalle->getCantidad();
        $precio = $detalle->getPrecioUnitario();
        $subtotal = $detalle->getSubtotal();
        $estado = $detalle->getEstado();
        
        $stmt->bindParam(":pedido", $pedidoId);
        $stmt->bindParam(":producto", $productoId);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":subtotal", $subtotal);
        $stmt->bindParam(":estado", $estado);
        
        return $stmt->execute();
    }
    
    public function listarPedidos($filtros = array()) {
        $query = "SELECT p.*, 
                         prov.razon_social, prov.ruc,
                         ep.nombre as estado_nombre,
                         u.nombres as usuario_nombre,
                         COUNT(dp.idDetalle) as cantidad_items
                  FROM " . $this->tablePedidos . " p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                  INNER JOIN usuarios u ON p.usuario_solicita = u.idUsuario
                  LEFT JOIN detalle_pedido dp ON p.idPedido = dp.pedidoId
                  WHERE 1=1";
        
        if (!empty($filtros['estadoId'])) {
            $query .= " AND p.estadoId = :estadoId";
        }
        
        if (!empty($filtros['proveedorId'])) {
            $query .= " AND p.proveedorId = :proveedorId";
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND p.fecha_pedido >= :fecha_desde";
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND p.fecha_pedido <= :fecha_hasta";
        }
        
        $query .= " GROUP BY p.idPedido ORDER BY p.fecha_pedido DESC, p.idPedido DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['estadoId'])) {
            $stmt->bindParam(":estadoId", $filtros['estadoId']);
        }
        if (!empty($filtros['proveedorId'])) {
            $stmt->bindParam(":proveedorId", $filtros['proveedorId']);
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
    
    public function obtenerPorIdPedido($id) {
        $query = "SELECT p.*, 
                         prov.razon_social, prov.ruc, prov.direccion, prov.telefono,
                         ep.nombre as estado_nombre,
                         u.nombres as usuario_nombre, u.apellidos as usuario_apellidos
                  FROM " . $this->tablePedidos . " p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                  INNER JOIN usuarios u ON p.usuario_solicita = u.idUsuario
                  WHERE p.idPedido = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerDetallesPedido($idPedido) {
        $query = "SELECT dp.*, 
                         pr.codigo_producto, pr.nombre_producto, pr.unidad_medida
                  FROM " . $this->tableDetalle . " dp
                  INNER JOIN productos pr ON dp.productoId = pr.idProducto
                  WHERE dp.pedidoId = :id
                  ORDER BY dp.idDetalle";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idPedido);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function actualizarPedido(Pedido $pedido) {
        $query = "UPDATE " . $this->tablePedidos . "
                  SET proveedorId = :proveedor,
                      fecha_pedido = :fecha_pedido,
                      fecha_entrega_estimada = :fecha_entrega,
                      estadoId = :estado,
                      subtotal = :subtotal,
                      igv = :igv,
                      total = :total,
                      observaciones = :observaciones
                  WHERE idPedido = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $id = $pedido->getIdPedido();
        $proveedor = $pedido->getProveedorId();
        $fecha_pedido = $pedido->getFechaPedido();
        $fecha_entrega = $pedido->getFechaEntregaEstimada();
        $estado = $pedido->getEstadoId();
        $subtotal = $pedido->getSubtotal();
        $igv = $pedido->getIgv();
        $total = $pedido->getTotal();
        $observaciones = $pedido->getObservaciones();
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":proveedor", $proveedor);
        $stmt->bindParam(":fecha_pedido", $fecha_pedido);
        $stmt->bindParam(":fecha_entrega", $fecha_entrega);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":subtotal", $subtotal);
        $stmt->bindParam(":igv", $igv);
        $stmt->bindParam(":total", $total);
        $stmt->bindParam(":observaciones", $observaciones);
        
        return $stmt->execute();
    }
    
    public function cambiarEstado($idPedido, $nuevoEstado) {
        $query = "UPDATE " . $this->tablePedidos . " 
                  SET estadoId = :estado 
                  WHERE idPedido = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $nuevoEstado);
        $stmt->bindParam(":id", $idPedido);
        
        return $stmt->execute();
    }
    
    public function existeNumeroPedido($numero, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->tablePedidos . " 
                  WHERE numero_pedido = :numero";
        
        if ($excluirId) {
            $query .= " AND idPedido != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero", $numero);
        
        if ($excluirId) {
            $stmt->bindParam(":id", $excluirId);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }
    
    public function eliminarDetallePedido($idPedido) {
        $query = "DELETE FROM " . $this->tableDetalle . " WHERE pedidoId = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idPedido);
        return $stmt->execute();
    }
    
    public function obtenerEstados() {
        $query = "SELECT * FROM estado_pedido ORDER BY idEstado";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerProveedoresActivos() {
        $query = "SELECT idProveedor, razon_social, ruc 
                  FROM proveedores 
                  WHERE estado = 'Activo' 
                  ORDER BY razon_social";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerProductosActivos() {
        $query = "SELECT p.idProducto, p.codigo_producto, p.nombre_producto, 
                         p.unidad_medida, p.precio_referencial, p.stock_actual,
                         c.nombre as categoria
                  FROM productos p
                  LEFT JOIN categorias c ON p.categoriaId = c.idCategoria
                  WHERE p.estado = 'Activo' 
                  ORDER BY p.nombre_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTotalPedidos() {
        $query = "SELECT COUNT(*) as total FROM " . $this->tablePedidos;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function obtenerTotalPorEstado($estadoId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->tablePedidos . "
                  WHERE estadoId = :estado";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estadoId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function obtenerPedidosRecientes($limite = 5) {
        $query = "SELECT p.*, 
                         prov.razon_social,
                         ep.nombre as estado_nombre
                  FROM " . $this->tablePedidos . " p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                  ORDER BY p.fecha_registro DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPedidos($termino) {
        $query = "SELECT p.*, 
                         prov.razon_social, prov.ruc,
                         ep.nombre as estado_nombre,
                         u.nombres as usuario_nombre
                  FROM " . $this->tablePedidos . " p
                  INNER JOIN proveedores prov ON p.proveedorId = prov.idProveedor
                  INNER JOIN estado_pedido ep ON p.estadoId = ep.idEstado
                  INNER JOIN usuarios u ON p.usuario_solicita = u.idUsuario
                  WHERE p.numero_pedido LIKE :termino
                     OR prov.razon_social LIKE :termino
                     OR prov.ruc LIKE :termino
                  ORDER BY p.fecha_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $terminoBusqueda = "%{$termino}%";
        $stmt->bindParam(":termino", $terminoBusqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>