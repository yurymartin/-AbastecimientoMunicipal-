<?php
require_once __DIR__ . '/../dao/daoProveedor.php';
require_once __DIR__ . '/../model/mProveedor.php';
require_once __DIR__ . '/../config/Database.php';

class bProveedor {
    private $proveedorDAO;
    
    public function __construct() {
        $this->proveedorDAO = new daoProveedor();
    }
    
    
    public function crearProveedorB($datos) {
        try {
            
            $datos['usuario_registro'] = $_SESSION['idUsuario'] ?? null;
            
            $proveedor = new Proveedor($datos);
            
            
            $errores = $proveedor->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            
            if ($this->proveedorDAO->existeRuc($proveedor->getRuc())) {
                return array('success' => false, 'message' => 'El RUC ya está registrado en el sistema');
            }
            
            
            $id = $this->proveedorDAO->crearProveedor($proveedor);
            
            if ($id) {
                
                $this->registrarAuditoria('proveedores', $id, 'INSERT', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Proveedor registrado exitosamente', 'id' => $id);
            }
            
            return array('success' => false, 'message' => 'Error al registrar el proveedor');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function actualizarProveedorB($datos) {
        try {
            $proveedor = new Proveedor($datos);
            
            
            $errores = $proveedor->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            
            if ($this->proveedorDAO->existeRuc($proveedor->getRuc(), $proveedor->getIdProveedor())) {
                return array('success' => false, 'message' => 'El RUC ya está registrado en el sistema');
            }
            
            
            if ($this->proveedorDAO->actualizarProveedor($proveedor)) {
                $this->registrarAuditoria('proveedores', $proveedor->getIdProveedor(), 'UPDATE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Proveedor actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el proveedor');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function eliminarProveedorB($id) {
        try {
            
            if ($this->tienePedidosAsociados($id)) {
                return array('success' => false, 'message' => 'No se puede eliminar el proveedor porque tiene pedidos asociados');
            }
            
            if ($this->proveedorDAO->desactivarProveedor($id)) {
                $this->registrarAuditoria('proveedores', $id, 'DELETE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Proveedor eliminado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al eliminar el proveedor');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    
    public function listarProveedorB($soloActivos = false) {
        return $this->proveedorDAO->listarProveedor($soloActivos);
    }
    
    
    public function obtenerProveedorPorIdB($id) {
        return $this->proveedorDAO->obtenerPorIdProveedor($id);
    }
    
    
    public function buscarProveedorB($termino) {
        return $this->proveedorDAO->buscarProveedor($termino);
    }
    
    
    public function obtenerTotalProveedoresB() {
        return $this->proveedorDAO->obtenerTotalProveedores();
    }
    
    
    private function tienePedidosAsociados($idProveedor) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "SELECT COUNT(*) as total FROM pedidos WHERE proveedorId = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id", $idProveedor);
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