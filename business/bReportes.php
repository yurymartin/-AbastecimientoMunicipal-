<?php
require_once __DIR__ . '/../dao/daoReportes.php';

class bReportes {
    private $reportesDAO;
    
    public function __construct() {
        $this->reportesDAO = new daoReportes();
    }
    
    // REPORTE DE PEDIDOS
    public function generarReportePedidosB($fechaInicio, $fechaFin, $estadoId = null, $proveedorId = null) {
        try {
            // Validar fechas
            if (empty($fechaInicio) || empty($fechaFin)) {
                return array('success' => false, 'message' => 'Las fechas son requeridas');
            }
            
            if (strtotime($fechaFin) < strtotime($fechaInicio)) {
                return array('success' => false, 'message' => 'La fecha final no puede ser menor a la fecha inicial');
            }
            
            $datos = $this->reportesDAO->obtenerReportePedidos($fechaInicio, $fechaFin, $estadoId, $proveedorId);
            
            // Calcular monto total correctamente
            $montoTotal = 0;
            foreach ($datos as $pedido) {
                $montoTotal += floatval($pedido['total']);
            }
            
            return array(
                'success' => true,
                'datos' => $datos,
                'total_registros' => count($datos),
                'monto_total' => $montoTotal
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // REPORTE DE GASTOS
    public function generarReporteGastosB($fechaInicio, $fechaFin, $tipoGasto = null) {
        try {
            if (empty($fechaInicio) || empty($fechaFin)) {
                return array('success' => false, 'message' => 'Las fechas son requeridas');
            }
            
            $datos = $this->reportesDAO->obtenerReporteGastos($fechaInicio, $fechaFin, $tipoGasto);
            
            return array(
                'success' => true,
                'datos' => $datos,
                'total_registros' => count($datos),
                'monto_total' => array_sum(array_column($datos, 'monto'))
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // REPORTE DE PROVEEDORES
    public function generarReporteProveedoresB() {
        try {
            $datos = $this->reportesDAO->obtenerReporteProveedores();
            
            return array(
                'success' => true,
                'datos' => $datos,
                'total_registros' => count($datos)
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // REPORTE DE PRODUCTOS
    public function generarReporteProductosB() {
        try {
            $datos = $this->reportesDAO->obtenerReporteProductos();
            
            return array(
                'success' => true,
                'datos' => $datos,
                'total_registros' => count($datos)
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // OBTENER ESTADÍSTICAS PARA GRÁFICOS
    public function obtenerEstadisticasB($fechaInicio, $fechaFin) {
        try {
            return array(
                'success' => true,
                'resumen' => $this->reportesDAO->obtenerResumenGeneral($fechaInicio, $fechaFin),
                'pedidos_estado' => $this->reportesDAO->obtenerEstadisticasPedidosPorEstado($fechaInicio, $fechaFin),
                'pedidos_mes' => $this->reportesDAO->obtenerEstadisticasPedidosPorMes(date('Y')),
                'top_proveedores' => $this->reportesDAO->obtenerTopProveedores(10, $fechaInicio, $fechaFin),
                'top_productos' => $this->reportesDAO->obtenerTopProductos(10, $fechaInicio, $fechaFin),
                'gastos_tipo' => $this->reportesDAO->obtenerEstadisticasGastosPorTipo($fechaInicio, $fechaFin)
            );
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // OBTENER TIPOS DE GASTOS
    public function obtenerTiposGastosB() {
        return $this->reportesDAO->obtenerTiposGastos();
    }
    
    // EXPORTAR A PDF (usando HTML para convertir)
    public function exportarPDF($tipoReporte, $datos, $parametros) {
        return $this->generarContenidoReporte($tipoReporte, $datos, $parametros);
    }

    // GENERAR SOLO CONTENIDO INTERNO
    private function generarContenidoReporte($tipoReporte, $datos, $parametros) {
        $html = '<div class="header">';
        $html .= '<h1>Sistema de Abastecimiento Municipal</h1>';
        $html .= '<p>Reporte de ' . ucfirst($tipoReporte) . '</p>';
        $oldTimezone = date_default_timezone_get();
        date_default_timezone_set('America/Lima');
        $html .= '<p>Generado el: ' . date('d/m/Y H:i:s') . '</p>';
        date_default_timezone_set($oldTimezone);
        $html .= '</div>';
        
        // Información de parámetros
        if (!empty($parametros)) {
            $html .= '<div class="info-box">';
            $html .= '<strong>Parámetros del Reporte:</strong><br>';
            if (isset($parametros['fecha_inicio'])) {
                $html .= 'Período: ' . date('d/m/Y', strtotime($parametros['fecha_inicio'])) . 
                        ' al ' . date('d/m/Y', strtotime($parametros['fecha_fin'])) . '<br>';
            }
            $html .= '</div>';
        }
        
        // Contenido según tipo de reporte
        switch ($tipoReporte) {
            case 'pedidos':
                $html .= $this->generarTablaPedidos($datos);
                break;
            case 'gastos':
                $html .= $this->generarTablaGastos($datos);
                break;
            case 'proveedores':
                $html .= $this->generarTablaProveedores($datos);
                break;
            case 'productos':
                $html .= $this->generarTablaProductos($datos);
                break;
        }
        
        $html .= '<div class="footer">';
        $html .= '<p>Sistema de Abastecimiento Municipal - Reporte Generado Automáticamente</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    // GENERAR HTML PARA REPORTES
    private function generarHTMLReporte($tipoReporte, $datos, $parametros) {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte - ' . ucfirst($tipoReporte) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; color: #1f2937; }
                .header p { margin: 5px 0; color: #6b7280; }
                .info-box { background: #f3f4f6; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #3b82f6; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
                tr:nth-child(even) { background: #f9fafb; }
                .total-row { font-weight: bold; background: #dbeafe !important; }
                .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; }
            </style>
        </head>
        <body>';

        $html .= $this->generarContenidoReporte($tipoReporte, $datos, $parametros);

        $html .= '</body></html>';

        return $html;
    }
    
    private function generarTablaPedidos($datos) {
        $html = '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>N° Pedido</th><th>Fecha</th><th>Proveedor</th>';
        $html .= '<th>Estado</th><th>Items</th><th>Total</th>';
        $html .= '</tr></thead><tbody>';
        
        if (empty($datos)) {
            $html .= '<tr>';
            $html .= '<td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">No hay pedidos en este período</td>';
            $html .= '</tr>';
        } else {
            $total = 0;
            foreach ($datos as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['numero_pedido']) . '</td>';
                $html .= '<td>' . date('d/m/Y', strtotime($row['fecha_pedido'])) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['estado_nombre']) . '</td>';
                $html .= '<td style="text-align: center;">' . $row['cantidad_items'] . '</td>';
                $html .= '<td style="text-align: right;">S/ ' . number_format($row['total'], 2) . '</td>';
                $html .= '</tr>';
                $total += floatval($row['total']);
            }

            // Solo mostrar total si HAY datos
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="5" style="text-align: right;">TOTAL:</td>';
            $html .= '<td style="text-align: right;">S/ ' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
    
    private function generarTablaGastos($datos) {
        $html = '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>Pedido</th><th>Tipo</th><th>Fecha</th>';
        $html .= '<th>Proveedor</th><th>Descripción</th><th>Monto</th>';
        $html .= '</tr></thead><tbody>';
        
        if (empty($datos)) {
            $html .= '<tr>';
            $html .= '<td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">No hay gastos registrados en este período</td>';
            $html .= '</tr>';
        } else {
            $total = 0;
            foreach ($datos as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['numero_pedido']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['tipo_gasto']) . '</td>';
                $html .= '<td>' . date('d/m/Y', strtotime($row['fecha_gasto'])) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
                $html .= '<td style="text-align: right;">S/ ' . number_format($row['monto'], 2) . '</td>';
                $html .= '</tr>';
                $total += floatval($row['monto']);
            }

            // Solo mostrar total si HAY datos
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="5" style="text-align: right;">TOTAL:</td>';
            $html .= '<td style="text-align: right;">S/ ' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
    
    private function generarTablaProveedores($datos) {
        $html = '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>RUC</th><th>Razón Social</th><th>Email</th>';
        $html .= '<th>Teléfono</th><th>Total Pedidos</th><th>Monto Total</th>';
        $html .= '</tr></thead><tbody>';
        
        if (empty($datos)) {
            $html .= '<tr>';
            $html .= '<td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">No hay proveedores registrados</td>';
            $html .= '</tr>';
        } else {
            foreach ($datos as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['ruc']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['razon_social']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['email'] ?? '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($row['telefono'] ?? '-') . '</td>';
                $html .= '<td style="text-align: center;">' . $row['total_pedidos'] . '</td>';
                $html .= '<td style="text-align: right;">S/ ' . number_format($row['monto_total_pedidos'], 2) . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }
    
    private function generarTablaProductos($datos) {
        $html = '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>Código</th><th>Producto</th><th>Categoría</th>';
        $html .= '<th>Stock Actual</th><th>Estado Stock</th><th>Veces Pedido</th><th>Cantidad Total</th>';
        $html .= '</tr></thead><tbody>';
        
        if (empty($datos)) {
            $html .= '<tr>';
            $html .= '<td colspan="7" style="text-align: center; padding: 20px; color: #6b7280;">No hay productos registrados</td>';
            $html .= '</tr>';
        } else {
            foreach ($datos as $row) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['codigo_producto']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['nombre_producto']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['categoria_nombre'] ?? '-') . '</td>';
                $html .= '<td style="text-align: center;">' . $row['stock_actual'] . '</td>';
                $html .= '<td>' . $row['estado_stock'] . '</td>';
                $html .= '<td style="text-align: center;">' . $row['veces_pedido'] . '</td>';
                $html .= '<td style="text-align: center;">' . $row['cantidad_total_pedida'] . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
?>