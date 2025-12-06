<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida'
    ]);
    exit;
}

require_once __DIR__ . '/../business/bDashboard.php';

$action = $_GET['action'] ?? 'obtenerDatos';

try {
    $bDashboard = new bDashboard();
    
    switch ($action) {
        case 'obtenerDatos':
            obtenerDatosDashboard($bDashboard);
            break;
            
        case 'obtenerEstadisticas':
            obtenerEstadisticas($bDashboard);
            break;
            
        case 'obtenerPedidosRecientes':
            obtenerPedidosRecientes($bDashboard);
            break;
            
        case 'obtenerProductosStockBajo':
            obtenerProductosStockBajo($bDashboard);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

function obtenerDatosDashboard($bDashboard) {
    $datos = $bDashboard->obtenerDatosCompletos();
    
    if ($datos) {
        echo json_encode([
            'success' => true,
            'data' => $datos
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener los datos'
        ]);
    }
}

function obtenerEstadisticas($bDashboard) {
    $stats = $bDashboard->obtenerEstadisticas();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function obtenerPedidosRecientes($bDashboard) {
    $limite = $_GET['limite'] ?? 5;
    $pedidos = $bDashboard->obtenerPedidosRecientes($limite);
    
    echo json_encode([
        'success' => true,
        'data' => $pedidos
    ]);
}

function obtenerProductosStockBajo($bDashboard) {
    $limite = $_GET['limite'] ?? 10;
    $productos = $bDashboard->obtenerProductosStockBajo($limite);
    
    echo json_encode([
        'success' => true,
        'data' => $productos
    ]);
}
?>