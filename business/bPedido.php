<?php
require_once __DIR__ . '/../dao/daoPedido.php';
require_once __DIR__ . '/../dao/daoProducto.php';
require_once __DIR__ . '/../model/mPedido.php';
require_once __DIR__ . '/../config/Database.php';

class bPedido {
    private $pedidoDAO;
    private $productoDAO;
    
    public function __construct() {
        $this->pedidoDAO = new daoPedido();
        $this->productoDAO = new daoProducto();
    }
    
    // CREAR PEDIDO 
    public function crearPedidoB($datos, $detalles) {
        try {
            if (empty($detalles)) {
                return array('success' => false, 'message' => 'Debe agregar al menos un producto al pedido');
            }
            
            $datos['numero_pedido'] = Pedido::generarNumeroPedido();
            $datos['usuario_solicita'] = $_SESSION['idUsuario'] ?? null;
            
            $pedido = new Pedido($datos);
            
            $pedido->calcularTotales($detalles);
            
            $errores = $pedido->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            if ($this->pedidoDAO->existeNumeroPedido($pedido->getNumeroPedido())) {
                return array('success' => false, 'message' => 'El número de pedido ya existe. Intente nuevamente');
            }
            
            $idPedido = $this->pedidoDAO->crearPedido($pedido);
            
            if ($idPedido) {
                foreach ($detalles as $det) {
                    $det['pedidoId'] = $idPedido;
                    $detallePedido = new DetallePedido($det);
                    $detallePedido->calcularSubtotal();
                    
                    $erroresDetalle = $detallePedido->validar();
                    if (!empty($erroresDetalle)) {
                        return array('success' => false, 'message' => 'Error en detalle: ' . implode(', ', $erroresDetalle));
                    }
                    
                    $this->pedidoDAO->crearDetallePedido($detallePedido);
                }
                
                $this->registrarAuditoria('pedidos', $idPedido, 'INSERT', $_SESSION['idUsuario'] ?? null);
                
                return array(
                    'success' => true, 
                    'message' => 'Pedido registrado exitosamente',
                    'id' => $idPedido,
                    'numero_pedido' => $pedido->getNumeroPedido()
                );
            }
            
            return array('success' => false, 'message' => 'Error al registrar el pedido');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // ACTUALIZAR PEDIDO
    public function actualizarPedidoB($datos, $detalles) {
        try {
            if (empty($detalles)) {
                return array('success' => false, 'message' => 'Debe agregar al menos un producto al pedido');
            }

            $pedidoActual = $this->pedidoDAO->obtenerPorIdPedido($datos['idPedido']);
            if ($pedidoActual) {
                $datos['numero_pedido'] = $pedidoActual['numero_pedido'];
            }
            
            $pedido = new Pedido($datos);
            
            $pedido->calcularTotales($detalles);
            
            $errores = $pedido->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            if ($this->pedidoDAO->actualizarPedido($pedido)) {
                $this->pedidoDAO->eliminarDetallePedido($pedido->getIdPedido());
                
                foreach ($detalles as $det) {
                    $det['pedidoId'] = $pedido->getIdPedido();
                    $detallePedido = new DetallePedido($det);
                    $detallePedido->calcularSubtotal();
                    
                    $this->pedidoDAO->crearDetallePedido($detallePedido);
                }
                
                $this->registrarAuditoria('pedidos', $pedido->getIdPedido(), 'UPDATE', $_SESSION['idUsuario'] ?? null);
                
                return array('success' => true, 'message' => 'Pedido actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el pedido');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // CAMBIAR ESTADO DEL PEDIDO
    public function cambiarEstadoB($idPedido, $nuevoEstado) {
        try {
            $pedidoActual = $this->pedidoDAO->obtenerPorIdPedido($idPedido);
            
            if (!$pedidoActual) {
                return array('success' => false, 'message' => 'Pedido no encontrado');
            }
            
            if ($nuevoEstado == 3) {
                $detalles = $this->pedidoDAO->obtenerDetallesPedido($idPedido);
                
                foreach ($detalles as $detalle) {
                    $this->productoDAO->actualizarStock(
                        $detalle['productoId'], 
                        $detalle['cantidad'], 
                        'sumar'
                    );
                }
            }
            
            if ($this->pedidoDAO->cambiarEstado($idPedido, $nuevoEstado)) {
                $this->registrarAuditoria('pedidos', $idPedido, 'UPDATE', $_SESSION['idUsuario'] ?? null);
                
                $mensaje = 'Estado del pedido actualizado';
                if ($nuevoEstado == 3) {
                    $mensaje .= ' y stock actualizado';
                }
                
                return array('success' => true, 'message' => $mensaje);
            }
            
            return array('success' => false, 'message' => 'Error al cambiar el estado');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function listarPedidosB($filtros = array()) {
        return $this->pedidoDAO->listarPedidos($filtros);
    }
    
    public function obtenerPedidoPorIdB($id) {
        return $this->pedidoDAO->obtenerPorIdPedido($id);
    }
    
    public function obtenerDetallesPedidoB($id) {
        return $this->pedidoDAO->obtenerDetallesPedido($id);
    }
    
    public function buscarPedidosB($termino) {
        return $this->pedidoDAO->buscarPedidos($termino);
    }
    
    public function obtenerEstadosB() {
        return $this->pedidoDAO->obtenerEstados();
    }
    
    public function obtenerProveedoresActivosB() {
        return $this->pedidoDAO->obtenerProveedoresActivos();
    }
    
    public function obtenerProductosActivosB() {
        return $this->pedidoDAO->obtenerProductosActivos();
    }
    
    public function obtenerTotalPedidosB() {
        return $this->pedidoDAO->obtenerTotalPedidos();
    }
    
    public function obtenerTotalPorEstadoB($estadoId) {
        return $this->pedidoDAO->obtenerTotalPorEstado($estadoId);
    }
    
    public function obtenerPedidosRecientesB($limite = 5) {
        return $this->pedidoDAO->obtenerPedidosRecientes($limite);
    }
    
    public function validarStockDisponible($detalles) {
        $errores = array();
        
        foreach ($detalles as $detalle) {
            $producto = $this->productoDAO->obtenerPorIdProducto($detalle['productoId']);
            
            if ($producto && isset($detalle['cantidad'])) {
                $cantidadSolicitada = $detalle['cantidad'];
                $stockActual = $producto['stock_actual'];
                
                if ($stockActual < $cantidadSolicitada) {
                    $errores[] = "Stock insuficiente para: " . $producto['nombre_producto'] . 
                                " (Stock: " . $stockActual . ", Solicitado: " . $cantidadSolicitada . ")";
                }
            }
        }
        
        return $errores;
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