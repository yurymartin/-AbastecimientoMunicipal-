<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bPedido.php';

class PedidoController {
    private $pedidoBusiness;
    
    public function __construct() {
        $this->pedidoBusiness = new bPedido();
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
            case 'obtenerDetalles':
                $this->obtenerDetalles();
                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'cambiarEstado':
                $this->cambiarEstado();
                break;
            case 'buscar':
                $this->buscar();
                break;
            case 'obtenerEstados':
                $this->obtenerEstados();
                break;
            case 'obtenerProveedores':
                $this->obtenerProveedores();
                break;
            case 'obtenerProductos':
                $this->obtenerProductos();
                break;
            case 'obtenerProducto':
                $this->obtenerProducto();
                break;
            case 'estadisticas':
                $this->estadisticas();
                break;
            case 'verificarNumero':
                $this->verificarNumero();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function listar() {
        try {
            $filtros = [];
            if (isset($_GET['estadoId'])) $filtros['estadoId'] = $_GET['estadoId'];
            if (isset($_GET['proveedorId'])) $filtros['proveedorId'] = $_GET['proveedorId'];
            if (isset($_GET['fecha_desde'])) $filtros['fecha_desde'] = $_GET['fecha_desde'];
            if (isset($_GET['fecha_hasta'])) $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
            
            $pedidos = $this->pedidoBusiness->listarPedidosB($filtros);
            
            echo json_encode([
                'success' => true,
                'data' => $pedidos,
                'total' => count($pedidos)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar pedidos: ' . $e->getMessage()
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
            
            $pedido = $this->pedidoBusiness->obtenerPedidoPorIdB($id);
            
            if ($pedido) {
                echo json_encode(['success' => true, 'data' => $pedido]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener pedido: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerDetalles() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $detalles = $this->pedidoBusiness->obtenerDetallesPedidoB($id);
            
            echo json_encode([
                'success' => true,
                'data' => $detalles
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardar() {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                return;
            }
            
            $datos = [
                'idPedido' => $data['idPedido'] ?? null,
                'proveedorId' => $data['proveedorId'] ?? null,
                'fecha_pedido' => $data['fecha_pedido'] ?? date('Y-m-d'),
                'fecha_entrega_estimada' => $data['fecha_entrega_estimada'] ?? null,
                'estadoId' => $data['estadoId'] ?? 1,
                'observaciones' => $data['observaciones'] ?? ''
            ];
            
            $detalles = $data['detalles'] ?? [];
            
            if (empty($datos['proveedorId'])) {
                echo json_encode(['success' => false, 'message' => 'Debe seleccionar un proveedor']);
                return;
            }
            
            if (empty($detalles)) {
                echo json_encode(['success' => false, 'message' => 'Debe agregar al menos un producto']);
                return;
            }
            
            if (empty($datos['idPedido'])) {
                $resultado = $this->pedidoBusiness->crearPedidoB($datos, $detalles);
            } else {
                $resultado = $this->pedidoBusiness->actualizarPedidoB($datos, $detalles);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar pedido: ' . $e->getMessage()
            ]);
        }
    }
    
    private function cambiarEstado() {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            $idPedido = $data['idPedido'] ?? null;
            $nuevoEstado = $data['nuevoEstado'] ?? null;
            
            if (!$idPedido || !$nuevoEstado) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            $resultado = $this->pedidoBusiness->cambiarEstadoB($idPedido, $nuevoEstado);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
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
            
            $pedidos = $this->pedidoBusiness->buscarPedidosB($termino);
            
            echo json_encode([
                'success' => true,
                'data' => $pedidos,
                'total' => count($pedidos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerEstados() {
        try {
            $estados = $this->pedidoBusiness->obtenerEstadosB();
            echo json_encode(['success' => true, 'data' => $estados]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function obtenerProveedores() {
        try {
            $proveedores = $this->pedidoBusiness->obtenerProveedoresActivosB();
            echo json_encode(['success' => true, 'data' => $proveedores]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function obtenerProductos() {
        try {
            $productos = $this->pedidoBusiness->obtenerProductosActivosB();
            echo json_encode(['success' => true, 'data' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function obtenerProducto() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $productos = $this->pedidoBusiness->obtenerProductosActivosB();
            $producto = null;
            
            foreach ($productos as $prod) {
                if ($prod['idProducto'] == $id) {
                    $producto = $prod;
                    break;
                }
            }
            
            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function estadisticas() {
        try {
            $totalPendientes = $this->pedidoBusiness->obtenerTotalPorEstadoB(1);
            $totalEnProceso = $this->pedidoBusiness->obtenerTotalPorEstadoB(2);
            $totalEntregados = $this->pedidoBusiness->obtenerTotalPorEstadoB(3);
            $totalGeneral = $this->pedidoBusiness->obtenerTotalPedidosB();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'totalPendientes' => $totalPendientes,
                    'totalEnProceso' => $totalEnProceso,
                    'totalEntregados' => $totalEntregados,
                    'totalGeneral' => $totalGeneral
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    private function verificarNumero() {
        try {
            $numero = $_GET['numero'] ?? '';
            $excluirId = $_GET['excluirId'] ?? null;
            
            if (empty($numero)) {
                echo json_encode(['success' => false, 'message' => 'Número no proporcionado']);
                return;
            }
            
            require_once __DIR__ . '/../dao/daoPedido.php';
            $dao = new daoPedido();
            $existe = $dao->existeNumeroPedido($numero, $excluirId);
            
            echo json_encode(['success' => true, 'existe' => $existe]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

try {
    $controller = new PedidoController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>