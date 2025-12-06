<?php
require_once __DIR__ . '/../dao/daoProducto.php';
require_once __DIR__ . '/../model/mProducto.php';
require_once __DIR__ . '/../config/Database.php';

class bProducto {
    private $productoDAO;
    
    public function __construct() {
        $this->productoDAO = new daoProducto();
    }
    
    
    public function crearProductoB($datos) {
        try {
            
            $datos['usuario_registro'] = $_SESSION['idUsuario'] ?? null;
            
            $producto = new Producto($datos);
            
            
            $errores = $producto->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            
            if ($this->productoDAO->existeCodigo($producto->getCodigoProducto())) {
                return array('success' => false, 'message' => 'El código del producto ya existe en el sistema');
            }
            
            
            $id = $this->productoDAO->crearProducto($producto);
            
            if ($id) {
                
                $this->registrarAuditoria('productos', $id, 'INSERT', $_SESSION['idUsuario'] ?? null);
                
                
                if ($producto->necesitaReposicion()) {
                    $mensaje = 'Producto registrado. ADVERTENCIA: El stock está por debajo del mínimo';
                } else {
                    $mensaje = 'Producto registrado exitosamente';
                }
                
                return array('success' => true, 'message' => $mensaje, 'id' => $id);
            }
            
            return array('success' => false, 'message' => 'Error al registrar el producto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function actualizarProductoB($datos) {
        try {
            $producto = new Producto($datos);
            
            
            $errores = $producto->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            
            if ($this->productoDAO->existeCodigo($producto->getCodigoProducto(), $producto->getIdProducto())) {
                return array('success' => false, 'message' => 'El código del producto ya existe en el sistema');
            }
            
            
            if ($this->productoDAO->actualizarProducto($producto)) {
                $this->registrarAuditoria('productos', $producto->getIdProducto(), 'UPDATE', $_SESSION['idUsuario'] ?? null);
                
                
                if ($producto->necesitaReposicion()) {
                    $mensaje = 'Producto actualizado. ADVERTENCIA: El stock está por debajo del mínimo';
                } else {
                    $mensaje = 'Producto actualizado exitosamente';
                }
                
                return array('success' => true, 'message' => $mensaje);
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el producto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function eliminarProductoB($id) {
        try {
            
            if ($this->tienePedidosAsociados($id)) {
                return array('success' => false, 'message' => 'No se puede eliminar el producto porque tiene pedidos asociados');
            }
            
            if ($this->productoDAO->desactivarProducto($id)) {
                $this->registrarAuditoria('productos', $id, 'DELETE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Producto eliminado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al eliminar el producto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function listarProductoB($soloActivos = false) {
        return $this->productoDAO->listarProducto($soloActivos);
    }
    
    
    public function obtenerProductoPorIdB($id) {
        return $this->productoDAO->obtenerPorIdProducto($id);
    }
    
    
    public function obtenerStockBajoB() {
        return $this->productoDAO->obtenerStockBajo();
    }
    
    
    public function buscarProductoB($termino) {
        return $this->productoDAO->buscarProducto($termino);
    }
    
    
    public function actualizarStockB($idProducto, $cantidad, $operacion = 'sumar') {
        try {
            if ($this->productoDAO->actualizarStock($idProducto, $cantidad, $operacion)) {
                $this->registrarAuditoria('productos', $idProducto, 'UPDATE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Stock actualizado');
            }
            return array('success' => false, 'message' => 'Error al actualizar stock');
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function obtenerCategoriasB() {
        return $this->productoDAO->obtenerCategorias();
    }
    
    
    public function obtenerTotalProductosB() {
        return $this->productoDAO->obtenerTotalProductos();
    }
    
    public function obtenerTotalStockBajoB() {
        return $this->productoDAO->obtenerTotalStockBajo();
    }
    
    
    private function tienePedidosAsociados($idProducto) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "SELECT COUNT(*) as total FROM detalle_pedido WHERE productoId = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id", $idProducto);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    private function registrarAuditoria($tabla, $idRegistro, $accion, $idUsuario) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO auditoria (tabla_afectada, registroId, accion, usuarioId, ip_address)
                      VALUES (:tabla, :registroId, :accion, :usuarioId, :ip)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":tabla", $tabla);
            $stmt->bindParam(":registroId", $idRegistro);
            $stmt->bindParam(":accion", $accion);
            $stmt->bindParam(":usuarioId", $idUsuario);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt->bindParam(":ip", $ip);
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
}
?>