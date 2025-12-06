<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bReportes.php';

class ReportesController {
    private $reportesBusiness;
    
    public function __construct() {
        $this->reportesBusiness = new bReportes();
    }
    
    public function procesarPeticion() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'estadisticas':
                $this->obtenerEstadisticas();
                break;
            case 'generar_pedidos':
                $this->generarReportePedidos();
                break;
            case 'generar_gastos':
                $this->generarReporteGastos();
                break;
            case 'generar_proveedores':
                $this->generarReporteProveedores();
                break;
            case 'generar_productos':
                $this->generarReporteProductos();
                break;
            case 'exportar_excel':
                $this->exportarExcel();
                break;
            case 'exportar_pdf':
                $this->exportarPDF();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function obtenerEstadisticas() {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
            
            $estadisticas = $this->reportesBusiness->obtenerEstadisticasB($fechaInicio, $fechaFin);
            
            echo json_encode($estadisticas);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generarReportePedidos() {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
            $estadoId = $_GET['estado_id'] ?? null;
            $proveedorId = $_GET['proveedor_id'] ?? null;
            
            $resultado = $this->reportesBusiness->generarReportePedidosB(
                $fechaInicio, 
                $fechaFin, 
                $estadoId, 
                $proveedorId
            );
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generarReporteGastos() {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
            $tipoGasto = $_GET['tipo_gasto'] ?? null;
            
            $resultado = $this->reportesBusiness->generarReporteGastosB(
                $fechaInicio, 
                $fechaFin, 
                $tipoGasto
            );
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generarReporteProveedores() {
        try {
            $resultado = $this->reportesBusiness->generarReporteProveedoresB();
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generarReporteProductos() {
        try {
            $resultado = $this->reportesBusiness->generarReporteProductosB();
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function exportarExcel() {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        
        $tipoReporte = $_GET['tipo'] ?? 'pedidos';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        switch ($tipoReporte) {
            case 'pedidos':
                $resultado = $this->reportesBusiness->generarReportePedidosB($fechaInicio, $fechaFin);
                $datos = $resultado['datos'];
                $nombreArchivo = 'reporte_pedidos_' . date('Ymd') . '.xls';
                break;
            case 'gastos':
                $resultado = $this->reportesBusiness->generarReporteGastosB($fechaInicio, $fechaFin);
                $datos = $resultado['datos'];
                $nombreArchivo = 'reporte_gastos_' . date('Ymd') . '.xls';
                break;
            case 'proveedores':
                $resultado = $this->reportesBusiness->generarReporteProveedoresB();
                $datos = $resultado['datos'];
                $nombreArchivo = 'reporte_proveedores_' . date('Ymd') . '.xls';
                break;
            case 'productos':
                $resultado = $this->reportesBusiness->generarReporteProductosB();
                $datos = $resultado['datos'];
                $nombreArchivo = 'reporte_productos_' . date('Ymd') . '.xls';
                break;
            default:
                die('Tipo de reporte no válido');
        }
        
        header("Content-Disposition: attachment; filename=$nombreArchivo");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo "\xEF\xBB\xBF";
        echo $this->generarContenidoExcel($tipoReporte, $datos, $fechaInicio, $fechaFin);
        exit;
    }
    
    private function exportarPDF() {
        header('Content-Type: text/html; charset=UTF-8');

        $tipoReporte = $_GET['tipo'] ?? 'pedidos';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        switch ($tipoReporte) {
            case 'pedidos':
                $resultado = $this->reportesBusiness->generarReportePedidosB($fechaInicio, $fechaFin);
                break;
            case 'gastos':
                $resultado = $this->reportesBusiness->generarReporteGastosB($fechaInicio, $fechaFin);
                break;
            case 'proveedores':
                $resultado = $this->reportesBusiness->generarReporteProveedoresB();
                break;
            case 'productos':
                $resultado = $this->reportesBusiness->generarReporteProductosB();
                break;
            default:
                die('Tipo de reporte no válido');
        }

        $parametros = array(
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        );

        $contenido = $this->reportesBusiness->exportarPDF($tipoReporte, $resultado['datos'], $parametros);

        $htmlCompleto = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte - ' . ucfirst($tipoReporte) . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
            padding: 0;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .header h1 { 
            margin: 0; 
            color: #1f2937; 
            font-size: 24px;
        }
        .header p { 
            margin: 5px 0; 
            color: #6b7280; 
        }
        .info-box { 
            background: #f3f4f6; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th { 
            background: #667eea; 
            color: white; 
            padding: 12px 10px; 
            text-align: left;
            font-weight: 600;
        }
        td { 
            padding: 10px; 
            border-bottom: 1px solid #e5e7eb; 
        }
        tr:nth-child(even) { 
            background: #f9fafb; 
        }
        tr:hover {
            background: #f3f4f6;
        }
        .total-row { 
            font-weight: bold; 
            background: #dbeafe !important;
            font-size: 14px;
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center; 
            font-size: 10px; 
            color: #9ca3af; 
        }
        .print-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        @media print {
            .print-buttons {
                display: none;
            }
            body {
                margin: 0;
            }
            tr:hover {
                background: inherit;
            }
        }
    </style>
</head>
<body>
    <div class="print-buttons">
        <button class="btn btn-success" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
        <button class="btn btn-primary" onclick="window.close()">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>
    
    ' . $contenido . '
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>';
    
        echo $htmlCompleto;
        exit;
    }
    
    private function generarContenidoExcel($tipo, $datos, $fechaInicio, $fechaFin) {
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h2>Sistema de Abastecimiento Municipal</h2>';
        $html .= '<h3>Reporte de ' . ucfirst($tipo) . '</h3>';
        $html .= '<p>Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . '</p>';
        $html .= '<p>Generado: ' . date('d/m/Y H:i:s') . '</p><br>';
        
        switch ($tipo) {
            case 'pedidos':
                $html .= '<table border="1">';
                $html .= '<tr><th>N° Pedido</th><th>Fecha</th><th>Proveedor</th><th>RUC</th><th>Estado</th><th>Items</th><th>Total</th></tr>';
                foreach ($datos as $row) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($row['numero_pedido']) . '</td>';
                    $html .= '<td>' . date('d/m/Y', strtotime($row['fecha_pedido'])) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['ruc']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['estado_nombre']) . '</td>';
                    $html .= '<td>' . $row['cantidad_items'] . '</td>';
                    $html .= '<td>' . number_format($row['total'], 2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
                
            case 'gastos':
                $html .= '<table border="1">';
                $html .= '<tr><th>Pedido</th><th>Tipo Gasto</th><th>Fecha</th><th>Proveedor</th><th>Descripción</th><th>Monto</th></tr>';
                foreach ($datos as $row) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($row['numero_pedido']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['tipo_gasto']) . '</td>';
                    $html .= '<td>' . date('d/m/Y', strtotime($row['fecha_gasto'])) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
                    $html .= '<td>' . number_format($row['monto'], 2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
                
            case 'proveedores':
                $html .= '<table border="1">';
                $html .= '<tr><th>RUC</th><th>Razón Social</th><th>Email</th><th>Teléfono</th><th>Total Pedidos</th><th>Monto Total</th></tr>';
                foreach ($datos as $row) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($row['ruc']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['email'] ?? '-') . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['telefono'] ?? '-') . '</td>';
                    $html .= '<td>' . $row['total_pedidos'] . '</td>';
                    $html .= '<td>' . number_format($row['monto_total_pedidos'], 2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
                
            case 'productos':
                $html .= '<table border="1">';
                $html .= '<tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Estado Stock</th><th>Veces Pedido</th><th>Cantidad Total</th></tr>';
                foreach ($datos as $row) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($row['codigo_producto']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['nombre_producto']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['categoria_nombre'] ?? '-') . '</td>';
                    $html .= '<td>' . $row['stock_actual'] . '</td>';
                    $html .= '<td>' . $row['estado_stock'] . '</td>';
                    $html .= '<td>' . $row['veces_pedido'] . '</td>';
                    $html .= '<td>' . $row['cantidad_total_pedida'] . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
        }
        
        $html .= '</body></html>';
        return $html;
    }
}

try {
    $controller = new ReportesController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>