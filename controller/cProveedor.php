<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bProveedor.php';

class ProveedorController {
    private $proveedorBusiness;
    
    public function __construct() {
        $this->proveedorBusiness = new bProveedor();
    }
    
    public function procesarPeticion() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'listar':
                $this->listar();
                break;
            case 'obtener':
                $this->obtener();
                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'eliminar':
                $this->eliminar();
                break;
            case 'buscar':
                $this->buscar();
                break;
            case 'verificarRuc':
                $this->verificarRuc();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function listar() {
        try {
            $soloActivos = isset($_GET['soloActivos']) ? 
                          filter_var($_GET['soloActivos'], FILTER_VALIDATE_BOOLEAN) : false;
            
            $proveedores = $this->proveedorBusiness->listarProveedorB($soloActivos);
            
            echo json_encode([
                'success' => true,
                'data' => $proveedores,
                'total' => count($proveedores)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar proveedores: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtener() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $proveedor = $this->proveedorBusiness->obtenerProveedorPorIdB($id);
            
            if ($proveedor) {
                echo json_encode(['success' => true, 'data' => $proveedor]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener proveedor: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardar() {
        try {
            $datos = [
                'idProveedor' => $_POST['idProveedor'] ?? null,
                'ruc' => trim($_POST['ruc'] ?? ''),
                'razon_social' => trim($_POST['razon_social'] ?? ''),
                'nombre_comercial' => trim($_POST['nombre_comercial'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'contacto_nombre' => trim($_POST['contacto_nombre'] ?? ''),
                'contacto_telefono' => trim($_POST['contacto_telefono'] ?? ''),
                'estado' => $_POST['estado'] ?? 'Activo'
            ];
            
            if (empty($datos['ruc']) || empty($datos['razon_social']) || 
                empty($datos['nombre_comercial'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ]);
                return;
            }
            
            if (empty($datos['idProveedor'])) {
                $resultado = $this->proveedorBusiness->crearProveedorB($datos);
            } else {
                $resultado = $this->proveedorBusiness->actualizarProveedorB($datos);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar proveedor: ' . $e->getMessage()
            ]);
        }
    }
    
    private function eliminar() {
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $resultado = $this->proveedorBusiness->eliminarProveedorB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar proveedor: ' . $e->getMessage()
            ]);
        }
    }
    
    private function buscar() {
        try {
            $termino = $_GET['termino'] ?? '';
            
            if (empty($termino)) {
                echo json_encode(['success' => false, 'message' => 'Término de búsqueda vacío']);
                return;
            }
            
            $proveedores = $this->proveedorBusiness->buscarProveedorB($termino);
            
            echo json_encode([
                'success' => true,
                'data' => $proveedores,
                'total' => count($proveedores)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function verificarRuc() {
        try {
            $ruc = $_GET['ruc'] ?? '';
            $excluirId = $_GET['excluirId'] ?? null;
            
            if (empty($ruc)) {
                echo json_encode(['success' => false, 'message' => 'RUC no proporcionado']);
                return;
            }
            
            require_once __DIR__ . '/../dao/daoProveedor.php';
            $dao = new daoProveedor();
            $existe = $dao->existeRuc($ruc, $excluirId);
            
            echo json_encode([
                'success' => true,
                'existe' => $existe
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al verificar RUC: ' . $e->getMessage()
            ]);
        }
    }
}

try {
    $controller = new ProveedorController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>