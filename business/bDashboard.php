<?php
require_once __DIR__ . '/../dao/daoDashboard.php';

class bDashboard {
    private $dao;
    
    public function __construct() {
        $this->dao = new daoDashboard();
    }
    
    // Obtener todos los datos del dashboard
    public function obtenerDatosCompletos() {
        try {
            $datos = [
                'usuarios' => $this->dao->contarUsuarios(),
                'proveedores' => $this->dao->contarProveedores(),
                'productos' => $this->dao->contarProductos(),
                'pedidos' => $this->dao->contarPedidos(),
                'pedidosRecientes' => $this->obtenerPedidosRecientes(5),
                'productosStockBajo' => $this->obtenerProductosStockBajo(5),
                'estadisticasMes' => $this->obtenerEstadisticasMesActual()
            ];
            
            return $datos;
        } catch (Exception $e) {
            error_log("Error en bDashboard::obtenerDatosCompletos - " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener estadísticas básicas
    public function obtenerEstadisticas() {
        return [
            'usuarios' => $this->dao->contarUsuarios(),
            'proveedores' => $this->dao->contarProveedores(),
            'productos' => $this->dao->contarProductos(),
            'pedidos' => $this->dao->contarPedidos()
        ];
    }
    
    // Obtener pedidos recientes
    public function obtenerPedidosRecientes($limite = 5) {
        $pedidos = $this->dao->obtenerPedidosRecientes($limite);
        
        $pedidosFormateados = [];
        foreach ($pedidos as $pedido) {
            $pedidosFormateados[] = [
                'numero_pedido' => $pedido['numero_pedido'],
                'proveedor' => $pedido['razon_social'],
                'fecha' => date('d/m/Y', strtotime($pedido['fecha_pedido'])),
                'estado' => $pedido['nombre_estado'],
                'total' => floatval($pedido['total'])
            ];
        }
        
        return $pedidosFormateados;
    }
    
    // Obtener productos con stock bajo
    public function obtenerProductosStockBajo($limite = 10) {
        $productos = $this->dao->obtenerProductosStockBajo($limite);
        
        $productosFormateados = [];
        foreach ($productos as $producto) {
            $productosFormateados[] = [
                'codigo' => $producto['codigo_producto'],
                'nombre' => $producto['nombre_producto'],
                'stock' => intval($producto['stock_actual']),
                'stock_minimo' => intval($producto['stock_minimo'])
            ];
        }
        
        return $productosFormateados;
    }
    

    public function obtenerEstadisticasMesActual() {
        return [
            'pedidosMes' => $this->dao->contarPedidosMesActual(),
            'gastosMes' => $this->dao->obtenerGastosMesActual(),
            'proveedoresNuevos' => $this->dao->contarProveedoresNuevosMes()
        ];
    }
    
    public function obtenerComparacionMesAnterior() {
        $mesActual = $this->dao->contarPedidosMesActual();
        $mesAnterior = $this->dao->contarPedidosMesAnterior();
        
        $cambio = 0;
        if ($mesAnterior > 0) {
            $cambio = (($mesActual - $mesAnterior) / $mesAnterior) * 100;
        }
        
        return [
            'mesActual' => $mesActual,
            'mesAnterior' => $mesAnterior,
            'porcentajeCambio' => round($cambio, 1),
            'esPositivo' => $cambio >= 0
        ];
    }
}
?>