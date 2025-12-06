<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bProducto.php';

class ProductoController {
    private $productoBusiness;
    
    public function __construct() {
        $this->productoBusiness = new bProducto();
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
            case 'stockBajo':
                $this->stockBajo();
                break;
            case 'actualizarStock':
                $this->actualizarStock();
                break;
            case 'obtenerCategorias':
                $this->obtenerCategorias();
                break;
            case 'verificarCodigo':
                $this->verificarCodigo();
                break;
            case 'estadisticas':
                $this->estadisticas();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function listar() {
        try {
            $soloActivos = isset($_GET['soloActivos']) ? 
                          filter_var($_GET['soloActivos'], FILTER_VALIDATE_BOOLEAN) : false;
            
            $productos = $this->productoBusiness->listarProductoB($soloActivos);
            
            echo json_encode([
                'success' => true,
                'data' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar productos: ' . $e->getMessage()
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
            
            $producto = $this->productoBusiness->obtenerProductoPorIdB($id);
            
            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener producto: ' . $e->getMessage()
            ]);
        }
    }
    
    private function guardar() {
        try {
            $datos = [
                'idProducto' => $_POST['idProducto'] ?? null,
                'codigo_producto' => trim($_POST['codigo_producto'] ?? ''),
                'nombre_producto' => trim($_POST['nombre_producto'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoriaId' => $_POST['categoriaId'] ?? null,
                'unidad_medida' => $_POST['unidad_medida'] ?? '',
                'stock_actual' => $_POST['stock_actual'] ?? 0,
                'stock_minimo' => $_POST['stock_minimo'] ?? 0,
                'precio_referencial' => $_POST['precio_referencial'] ?? 0,
                'estado' => $_POST['estado'] ?? 'Activo'
            ];
            
            if (empty($datos['codigo_producto']) || empty($datos['nombre_producto']) || 
                empty($datos['categoriaId']) || empty($datos['unidad_medida'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan campos requeridos'
                ]);
                return;
            }
            
            if (empty($datos['idProducto'])) {
                $resultado = $this->productoBusiness->crearProductoB($datos);
            } else {
                $resultado = $this->productoBusiness->actualizarProductoB($datos);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar producto: ' . $e->getMessage()
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
            
            $resultado = $this->productoBusiness->eliminarProductoB($id);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
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
            
            $productos = $this->productoBusiness->buscarProductoB($termino);
            
            echo json_encode([
                'success' => true,
                'data' => $productos,
                'total' => count($productos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar: ' . $e->getMessage()
            ]);
        }
    }
    
    private function stockBajo() {
        try {
            $productos = $this->productoBusiness->obtenerStockBajoB();
            
            echo json_encode([
                'success' => true,
                'data' => $productos,
                'total' => count($productos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener stock bajo: ' . $e->getMessage()
            ]);
        }
    }
    
    private function actualizarStock() {
        try {
            $idProducto = $_POST['idProducto'] ?? null;
            $cantidad = $_POST['cantidad'] ?? 0;
            $operacion = $_POST['operacion'] ?? 'sumar';
            
            if (!$idProducto || $cantidad <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos inválidos para actualizar stock'
                ]);
                return;
            }
            
            $resultado = $this->productoBusiness->actualizarStockB($idProducto, $cantidad, $operacion);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerCategorias() {
        try {
            $categorias = $this->productoBusiness->obtenerCategoriasB();
            
            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ]);
        }
    }
    
    private function verificarCodigo() {
        try {
            $codigo = $_GET['codigo'] ?? '';
            $excluirId = $_GET['excluirId'] ?? null;
            
            if (empty($codigo)) {
                echo json_encode(['success' => false, 'message' => 'Código no proporcionado']);
                return;
            }
            
            require_once __DIR__ . '/../dao/daoProducto.php';
            $dao = new daoProducto();
            $existe = $dao->existeCodigo($codigo, $excluirId);
            
            echo json_encode([
                'success' => true,
                'existe' => $existe
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al verificar código: ' . $e->getMessage()
            ]);
        }
    }
    
    private function estadisticas() {
        try {
            $totalProductos = $this->productoBusiness->obtenerTotalProductosB();
            $totalStockBajo = $this->productoBusiness->obtenerTotalStockBajoB();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'totalProductos' => $totalProductos,
                    'totalStockBajo' => $totalStockBajo
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }
}

try {
    $controller = new ProductoController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>