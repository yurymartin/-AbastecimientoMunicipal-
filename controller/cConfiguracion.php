<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

if (!isset($_SESSION['nombreRol']) || $_SESSION['nombreRol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para esta acción']);
    exit;
}

require_once __DIR__ . '/../business/bConfiguracion.php';

class ConfiguracionController {
    private $configuracionBusiness;
    
    public function __construct() {
        $this->configuracionBusiness = new bConfiguracion();
    }
    
    public function procesarPeticion() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'cargarDatos':
                $this->cargarDatos();
                break;
            case 'estadisticas':
                $this->obtenerEstadisticas();
                break;
            
            case 'listarCategorias':
                $this->listarCategorias();
                break;
            case 'guardarCategoria':
                $this->guardarCategoria();
                break;
            case 'eliminarCategoria':
                $this->eliminarCategoria();
                break;
            case 'obtenerCategoria':
                $this->obtenerCategoria();
                break;
            
            case 'listarRoles':
                $this->listarRoles();
                break;
            case 'guardarRol':
                $this->guardarRol();
                break;
            case 'eliminarRol':
                $this->eliminarRol();
                break;
            
            case 'listarEstados':
                $this->listarEstados();
                break;
            case 'guardarEstado':
                $this->guardarEstado();
                break;
            
            case 'listarAuditoria':
                $this->listarAuditoria();
                break;
            case 'limpiarAuditoria':
                $this->limpiarAuditoria();
                break;
            
            case 'listarSesiones':
                $this->listarSesiones();
                break;
            case 'cerrarSesion':
                $this->cerrarSesion();
                break;
            
            case 'generarRespaldo':
                $this->generarRespaldo();
                break;
            
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    
    private function cargarDatos() {
        try {
            $datos = array(
                'categorias' => $this->configuracionBusiness->listarCategoriasB(),
                'roles' => $this->configuracionBusiness->listarRolesB(),
                'estados' => $this->configuracionBusiness->listarEstadosPedidoB(),
                'estadisticas' => $this->configuracionBusiness->obtenerEstadisticasSistemaB()
            );
            
            echo json_encode([
                'success' => true,
                'data' => $datos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerEstadisticas() {
        try {
            $stats = $this->configuracionBusiness->obtenerEstadisticasSistemaB();
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function listarCategorias() {
        try {
            $categorias = $this->configuracionBusiness->listarCategoriasB();
            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardarCategoria() {
        try {
            $id = $_POST['idCategoria'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            if (empty($nombre)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre es requerido'
                ]);
                return;
            }
            
            if (empty($id)) {
                $resultado = $this->configuracionBusiness->crearCategoriaB($nombre, $descripcion);
            } else {
                $resultado = $this->configuracionBusiness->actualizarCategoriaB($id, $nombre, $descripcion);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function eliminarCategoria() {
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $resultado = $this->configuracionBusiness->eliminarCategoriaB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerCategoria() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $categoria = $this->configuracionBusiness->obtenerCategoriaPorIdB($id);
            
            if ($categoria) {
                echo json_encode(['success' => true, 'data' => $categoria]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function listarRoles() {
        try {
            $roles = $this->configuracionBusiness->listarRolesB();
            echo json_encode([
                'success' => true,
                'data' => $roles
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardarRol() {
        try {
            $id = $_POST['idRol'] ?? null;
            $nombre = trim($_POST['nombreRol'] ?? '');
            $descripcion = trim($_POST['descripcionRol'] ?? '');
            
            if (empty($nombre)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre es requerido'
                ]);
                return;
            }
            
            if (empty($id)) {
                $resultado = $this->configuracionBusiness->crearRolB($nombre, $descripcion);
            } else {
                $resultado = $this->configuracionBusiness->actualizarRolB($id, $nombre, $descripcion);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function eliminarRol() {
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $resultado = $this->configuracionBusiness->eliminarRolB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function listarEstados() {
        try {
            $estados = $this->configuracionBusiness->listarEstadosPedidoB();
            echo json_encode([
                'success' => true,
                'data' => $estados
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardarEstado() {
        try {
            $id = $_POST['idEstado'] ?? null;
            $nombre = trim($_POST['nombreEstado'] ?? '');
            $descripcion = trim($_POST['descripcionEstado'] ?? '');
            
            if (empty($id) || empty($nombre)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
                return;
            }
            
            $resultado = $this->configuracionBusiness->actualizarEstadoPedidoB($id, $nombre, $descripcion);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function listarAuditoria() {
        try {
            $filtros = array();
            if (isset($_GET['tabla'])) {
                $filtros['tabla'] = $_GET['tabla'];
            }
            if (isset($_GET['accion'])) {
                $filtros['accion'] = $_GET['accion'];
            }
            if (isset($_GET['fecha_desde'])) {
                $filtros['fecha_desde'] = $_GET['fecha_desde'];
            }
            if (isset($_GET['fecha_hasta'])) {
                $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
            }
            
            $limite = $_GET['limite'] ?? 100;
            $auditoria = $this->configuracionBusiness->listarAuditoriaB($limite, $filtros);
            
            echo json_encode([
                'success' => true,
                'data' => $auditoria,
                'total' => count($auditoria)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function limpiarAuditoria() {
        try {
            $dias = $_POST['dias'] ?? 90;
            $resultado = $this->configuracionBusiness->limpiarAuditoriaAntiguaB($dias);
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function listarSesiones() {
        try {
            $sesiones = $this->configuracionBusiness->obtenerSesionesActivasB();
            echo json_encode([
                'success' => true,
                'data' => $sesiones,
                'total' => count($sesiones)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function cerrarSesion() {
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $resultado = $this->configuracionBusiness->cerrarSesionB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    
    private function generarRespaldo() {
        try {
            $resultado = $this->configuracionBusiness->generarRespaldoB();
            
            if ($resultado['success']) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
                header('Content-Length: ' . strlen($resultado['backup']));
                
                echo $resultado['backup'];
                exit;
            } else {
                echo json_encode($resultado);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

try {
    $controller = new ConfiguracionController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>