<?php
require_once __DIR__ . '/../dao/daoConfiguracion.php';
require_once __DIR__ . '/../config/Database.php';

class bConfiguracion {
    private $configuracionDAO;
    
    public function __construct() {
        $this->configuracionDAO = new daoConfiguracion();
    }
    
    
    public function listarCategoriasB() {
        return $this->configuracionDAO->listarCategorias();
    }
    
    public function crearCategoriaB($nombre, $descripcion) {
        try {
            if (empty($nombre)) {
                return array('success' => false, 'message' => 'El nombre es requerido');
            }
            
            $id = $this->configuracionDAO->crearCategoria($nombre, $descripcion);
            
            if ($id) {
                $this->registrarAuditoria('categorias', $id, 'INSERT');
                return array('success' => true, 'message' => 'Categoría creada exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al crear la categoría');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function actualizarCategoriaB($id, $nombre, $descripcion) {
        try {
            if (empty($nombre)) {
                return array('success' => false, 'message' => 'El nombre es requerido');
            }
            
            if ($this->configuracionDAO->actualizarCategoria($id, $nombre, $descripcion)) {
                $this->registrarAuditoria('categorias', $id, 'UPDATE');
                return array('success' => true, 'message' => 'Categoría actualizada exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar la categoría');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function eliminarCategoriaB($id) {
        try {
            $resultado = $this->configuracionDAO->eliminarCategoria($id);
            
            if ($resultado === true) {
                $this->registrarAuditoria('categorias', $id, 'DELETE');
                return array('success' => true, 'message' => 'Categoría eliminada exitosamente');
            } elseif (is_array($resultado)) {
                return $resultado;
            }
            
            return array('success' => false, 'message' => 'Error al eliminar la categoría');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function obtenerCategoriaPorIdB($id) {
        return $this->configuracionDAO->obtenerCategoriaPorId($id);
    }
    
    // ROLES
    
    public function listarRolesB() {
        return $this->configuracionDAO->listarRoles();
    }
    
    public function crearRolB($nombre, $descripcion) {
        try {
            if (empty($nombre)) {
                return array('success' => false, 'message' => 'El nombre es requerido');
            }
            
            $id = $this->configuracionDAO->crearRol($nombre, $descripcion);
            
            if ($id) {
                $this->registrarAuditoria('roles', $id, 'INSERT');
                return array('success' => true, 'message' => 'Rol creado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al crear el rol');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function actualizarRolB($id, $nombre, $descripcion) {
        try {
            if (empty($nombre)) {
                return array('success' => false, 'message' => 'El nombre es requerido');
            }
            
            if ($this->configuracionDAO->actualizarRol($id, $nombre, $descripcion)) {
                $this->registrarAuditoria('roles', $id, 'UPDATE');
                return array('success' => true, 'message' => 'Rol actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el rol');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function eliminarRolB($id) {
        try {
            $resultado = $this->configuracionDAO->eliminarRol($id);
            
            if ($resultado === true) {
                $this->registrarAuditoria('roles', $id, 'DELETE');
                return array('success' => true, 'message' => 'Rol eliminado exitosamente');
            } elseif (is_array($resultado)) {
                return $resultado;
            }
            
            return array('success' => false, 'message' => 'Error al eliminar el rol');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // ESTADOS DE PEDIDOS
    
    public function listarEstadosPedidoB() {
        return $this->configuracionDAO->listarEstadosPedido();
    }
    
    public function actualizarEstadoPedidoB($id, $nombre, $descripcion) {
        try {
            if (empty($nombre)) {
                return array('success' => false, 'message' => 'El nombre es requerido');
            }
            
            if ($this->configuracionDAO->actualizarEstadoPedido($id, $nombre, $descripcion)) {
                $this->registrarAuditoria('estado_pedido', $id, 'UPDATE');
                return array('success' => true, 'message' => 'Estado actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el estado');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // ESTADÍSTICAS
    
    public function obtenerEstadisticasSistemaB() {
        return $this->configuracionDAO->obtenerEstadisticasSistema();
    }
    
    // AUDITORÍA
    
    public function listarAuditoriaB($limite = 100, $filtros = array()) {
        return $this->configuracionDAO->listarAuditoria($limite, $filtros);
    }
    
    public function limpiarAuditoriaAntiguaB($dias = 90) {
        try {
            $eliminados = $this->configuracionDAO->limpiarAuditoriaAntigua($dias);
            return array(
                'success' => true, 
                'message' => "Se eliminaron {$eliminados} registros de auditoría",
                'eliminados' => $eliminados
            );
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // RESPALDO
    
    public function generarRespaldoB() {
        try {
            $backup = $this->configuracionDAO->generarRespaldo();
            
            if ($backup) {
                return array(
                    'success' => true, 
                    'message' => 'Respaldo generado exitosamente',
                    'backup' => $backup
                );
            }
            
            return array('success' => false, 'message' => 'Error al generar el respaldo');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // SESIONES
    
    public function obtenerSesionesActivasB() {
        return $this->configuracionDAO->obtenerSesionesActivas();
    }
    
    public function cerrarSesionB($idSesion) {
        try {
            if ($this->configuracionDAO->cerrarSesion($idSesion)) {
                return array('success' => true, 'message' => 'Sesión cerrada exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al cerrar la sesión');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // AUDITORÍA
    private function registrarAuditoria($tabla, $idRegistro, $accion) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO auditoria (tabla_afectada, registroId, accion, usuarioId, ip_address)
                      VALUES (:tabla, :registroId, :accion, :usuarioId, :ip)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":tabla", $tabla);
            $stmt->bindParam(":registroId", $idRegistro);
            $stmt->bindParam(":accion", $accion);
            $idUsuario = $_SESSION['idUsuario'] ?? null;
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