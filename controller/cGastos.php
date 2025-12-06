<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bGastos.php';

class GastosController {
    private $gastosBusiness;
    
    public function __construct() {
        $this->gastosBusiness = new bGastos();
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
            case 'por_pedido':
                $this->porPedido();
                break;
            case 'tipos_gastos':
                $this->tiposGastos();
                break;
            case 'pedidos_disponibles':
                $this->pedidosDisponibles();
                break;
            case 'total':
                $this->obtenerTotal();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function listar() {
        try {
            $filtros = array();
            if (isset($_GET['tipo_gasto'])) {
                $filtros['tipo_gasto'] = $_GET['tipo_gasto'];
            }
            if (isset($_GET['pedidoId'])) {
                $filtros['pedidoId'] = $_GET['pedidoId'];
            }
            if (isset($_GET['fecha_desde'])) {
                $filtros['fecha_desde'] = $_GET['fecha_desde'];
            }
            if (isset($_GET['fecha_hasta'])) {
                $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
            }
            
            $gastos = $this->gastosBusiness->listarGastosB($filtros);
            $totalGastos = $this->gastosBusiness->obtenerTotalGastosB($filtros);
            
            echo json_encode([
                'success' => true,
                'data' => $gastos,
                'total' => count($gastos),
                'monto_total' => $totalGastos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar gastos: ' . $e->getMessage()
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
            
            $gasto = $this->gastosBusiness->obtenerGastoPorIdB($id);
            
            if ($gasto) {
                echo json_encode(['success' => true, 'data' => $gasto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gasto no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener gasto: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardar() {
        try {
            $datos = [
                'idGasto' => $_POST['idGasto'] ?? null,
                'pedidoId' => $_POST['pedidoId'] ?? '',
                'tipo_gasto' => trim($_POST['tipo_gasto'] ?? ''),
                'monto' => $_POST['monto'] ?? 0,
                'fecha_gasto' => $_POST['fecha_gasto'] ?? date('Y-m-d'),
                'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];
            
            if (empty($datos['pedidoId']) || empty($datos['tipo_gasto']) || empty($datos['monto'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ]);
                return;
            }
            
            if (empty($datos['idGasto'])) {
                $resultado = $this->gastosBusiness->crearGastoB($datos);
            } else {
                $resultado = $this->gastosBusiness->actualizarGastoB($datos);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar gasto: ' . $e->getMessage()
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
            
            $resultado = $this->gastosBusiness->eliminarGastoB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar gasto: ' . $e->getMessage()
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
            
            $gastos = $this->gastosBusiness->buscarGastosB($termino);
            
            echo json_encode([
                'success' => true,
                'data' => $gastos,
                'total' => count($gastos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function porPedido() {
        try {
            $pedidoId = $_GET['pedido_id'] ?? null;
            
            if (!$pedidoId) {
                echo json_encode(['success' => false, 'message' => 'Pedido ID no proporcionado']);
                return;
            }
            
            $gastos = $this->gastosBusiness->obtenerGastosPorPedidoB($pedidoId);
            
            echo json_encode([
                'success' => true,
                'data' => $gastos,
                'total' => count($gastos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function tiposGastos() {
        try {
            $tipos = $this->gastosBusiness->obtenerTiposGastosB();
            
            echo json_encode([
                'success' => true,
                'data' => $tipos
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener tipos de gastos: ' . $e->getMessage()
            ]);
        }
    }
    
    private function pedidosDisponibles() {
        try {
            $pedidos = $this->gastosBusiness->obtenerPedidosDisponiblesB();
            
            echo json_encode([
                'success' => true,
                'data' => $pedidos
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener pedidos: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerTotal() {
        try {
            $filtros = array();
            if (isset($_GET['fecha_desde'])) {
                $filtros['fecha_desde'] = $_GET['fecha_desde'];
            }
            if (isset($_GET['fecha_hasta'])) {
                $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
            }
            
            $total = $this->gastosBusiness->obtenerTotalGastosB($filtros);
            
            echo json_encode([
                'success' => true,
                'total' => $total
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

try {
    $controller = new GastosController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>