<?php
require_once __DIR__ . '/../dao/daoGastos.php';
require_once __DIR__ . '/../config/Database.php';

class bGastos {
    private $gastosDAO;
    
    public function __construct() {
        $this->gastosDAO = new daoGastos();
    }
    
    // CREAR GASTO
    public function crearGastoB($datos) {
        try {
            $datos['usuario_registro'] = $_SESSION['idUsuario'] ?? null;
            
            $errores = $this->validarGasto($datos);
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            $id = $this->gastosDAO->crearGasto($datos);
            
            if ($id) {
                $this->registrarAuditoria('gastos', $id, 'INSERT', $_SESSION['idUsuario'] ?? null);
                
                return array('success' => true, 'message' => 'Gasto registrado exitosamente', 'id' => $id);
            }
            
            return array('success' => false, 'message' => 'Error al registrar el gasto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // ACTUALIZAR GASTO
    public function actualizarGastoB($datos) {
        try {
            $errores = $this->validarGasto($datos);
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            if ($this->gastosDAO->actualizarGasto($datos)) {
                $this->registrarAuditoria('gastos', $datos['idGasto'], 'UPDATE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Gasto actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el gasto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // ELIMINAR GASTO
    public function eliminarGastoB($id) {
        try {
            if ($this->gastosDAO->eliminarGasto($id)) {
                $this->registrarAuditoria('gastos', $id, 'DELETE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Gasto eliminado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al eliminar el gasto');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function listarGastosB($filtros = array()) {
        return $this->gastosDAO->listarGastos($filtros);
    }
    
    public function obtenerGastoPorIdB($id) {
        return $this->gastosDAO->obtenerPorIdGasto($id);
    }
    
    public function obtenerGastosPorPedidoB($pedidoId) {
        return $this->gastosDAO->obtenerGastosPorPedido($pedidoId);
    }
    
    public function buscarGastosB($termino) {
        return $this->gastosDAO->buscarGastos($termino);
    }
    
    public function obtenerTiposGastosB() {
        return $this->gastosDAO->obtenerTiposGastos();
    }
    
    public function obtenerPedidosDisponiblesB() {
        return $this->gastosDAO->obtenerPedidosDisponibles();
    }
    
    public function obtenerTotalGastosB($filtros = array()) {
        return $this->gastosDAO->obtenerTotalGastos($filtros);
    }
    
    private function validarGasto($datos) {
        $errores = array();
        
        if (empty($datos['pedidoId'])) {
            $errores[] = "El pedido es requerido";
        }
        
        if (empty($datos['tipo_gasto'])) {
            $errores[] = "El tipo de gasto es requerido";
        }
        
        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = "El monto debe ser mayor a 0";
        }
        
        if (empty($datos['fecha_gasto'])) {
            $errores[] = "La fecha del gasto es requerida";
        }
        
        return $errores;
    }
    
    // REGISTRAR AUDITORÍA
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