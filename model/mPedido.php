<?php
class Pedido {
    private $idPedido;
    private $numero_pedido;
    private $proveedorId;
    private $fecha_pedido;
    private $fecha_entrega_estimada;
    private $estadoId;
    private $subtotal;
    private $igv;
    private $total;
    private $observaciones;
    private $usuario_solicita;
    private $fecha_registro;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->idPedido = isset($data['idPedido']) ? $data['idPedido'] : null;
            $this->numero_pedido = isset($data['numero_pedido']) ? $data['numero_pedido'] : null;
            $this->proveedorId = isset($data['proveedorId']) ? $data['proveedorId'] : null;
            $this->fecha_pedido = isset($data['fecha_pedido']) ? $data['fecha_pedido'] : date('Y-m-d');
            $this->fecha_entrega_estimada = isset($data['fecha_entrega_estimada']) ? $data['fecha_entrega_estimada'] : null;
            $this->estadoId = isset($data['estadoId']) ? $data['estadoId'] : 1;
            $this->subtotal = isset($data['subtotal']) ? $data['subtotal'] : 0;
            $this->igv = isset($data['igv']) ? $data['igv'] : 0;
            $this->total = isset($data['total']) ? $data['total'] : 0;
            $this->observaciones = isset($data['observaciones']) ? $data['observaciones'] : null;
            $this->usuario_solicita = isset($data['usuario_solicita']) ? $data['usuario_solicita'] : null;
        }
    }
    
    public function getIdPedido() { return $this->idPedido; }
    public function getNumeroPedido() { return $this->numero_pedido; }
    public function getProveedorId() { return $this->proveedorId; }
    public function getFechaPedido() { return $this->fecha_pedido; }
    public function getFechaEntregaEstimada() { return $this->fecha_entrega_estimada; }
    public function getEstadoId() { return $this->estadoId; }
    public function getSubtotal() { return $this->subtotal; }
    public function getIgv() { return $this->igv; }
    public function getTotal() { return $this->total; }
    public function getObservaciones() { return $this->observaciones; }
    public function getUsuarioSolicita() { return $this->usuario_solicita; }
    public function getFechaRegistro() { return $this->fecha_registro; }
    
    public function setIdPedido($id) { $this->idPedido = $id; }
    public function setNumeroPedido($numero) { $this->numero_pedido = $numero; }
    public function setProveedorId($id) { $this->proveedorId = $id; }
    public function setFechaPedido($fecha) { $this->fecha_pedido = $fecha; }
    public function setFechaEntregaEstimada($fecha) { $this->fecha_entrega_estimada = $fecha; }
    public function setEstadoId($id) { $this->estadoId = $id; }
    public function setSubtotal($subtotal) { $this->subtotal = $subtotal; }
    public function setIgv($igv) { $this->igv = $igv; }
    public function setTotal($total) { $this->total = $total; }
    public function setObservaciones($obs) { $this->observaciones = $obs; }
    public function setUsuarioSolicita($id) { $this->usuario_solicita = $id; }
    
    public function validar() {
        $errores = array();
        
        if (empty($this->numero_pedido)) {
            $errores[] = "El número de pedido es requerido";
        }
        
        if (empty($this->proveedorId)) {
            $errores[] = "El proveedor es requerido";
        }
        
        if (empty($this->fecha_pedido)) {
            $errores[] = "La fecha de pedido es requerida";
        }
        
        if (empty($this->estadoId)) {
            $errores[] = "El estado del pedido es requerido";
        }
        
        if (!empty($this->fecha_entrega_estimada) && !empty($this->fecha_pedido)) {
            if (strtotime($this->fecha_entrega_estimada) < strtotime($this->fecha_pedido)) {
                $errores[] = "La fecha de entrega no puede ser anterior a la fecha del pedido";
            }
        }
        
        if ($this->subtotal < 0) {
            $errores[] = "El subtotal no puede ser negativo";
        }
        
        if ($this->igv < 0) {
            $errores[] = "El IGV no puede ser negativo";
        }
        
        if ($this->total < 0) {
            $errores[] = "El total no puede ser negativo";
        }
        
        return $errores;
    }
    
    public function calcularTotales($detalles) {
        $subtotal = 0;
        
        foreach ($detalles as $detalle) {
            $subtotal += $detalle['subtotal'];
        }
        
        $this->subtotal = $subtotal;
        $this->igv = $subtotal * 0.18;
        $this->total = $subtotal + $this->igv;
    }
    
    public static function generarNumeroPedido() {
        return 'PED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    public function getClaseEstado() {
        switch ($this->estadoId) {
            case 1:
                return 'warning';
            case 2:
                return 'info';
            case 3:
                return 'success';
            case 4:
                return 'danger';
            default:
                return 'secondary';
        }
    }
}

class DetallePedido {
    private $idDetalle;
    private $pedidoId;
    private $productoId;
    private $cantidad;
    private $precio_unitario;
    private $subtotal;
    private $estado;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->idDetalle = isset($data['idDetalle']) ? $data['idDetalle'] : null;
            $this->pedidoId = isset($data['pedidoId']) ? $data['pedidoId'] : null;
            $this->productoId = isset($data['productoId']) ? $data['productoId'] : null;
            $this->cantidad = isset($data['cantidad']) ? $data['cantidad'] : 0;
            $this->precio_unitario = isset($data['precio_unitario']) ? $data['precio_unitario'] : 0;
            $this->subtotal = isset($data['subtotal']) ? $data['subtotal'] : 0;
            $this->estado = isset($data['estado']) ? $data['estado'] : 'Pendiente';
        }
    }
    
    public function getIdDetalle() { return $this->idDetalle; }
    public function getPedidoId() { return $this->pedidoId; }
    public function getProductoId() { return $this->productoId; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecioUnitario() { return $this->precio_unitario; }
    public function getSubtotal() { return $this->subtotal; }
    public function getEstado() { return $this->estado; }
    
    public function setIdDetalle($id) { $this->idDetalle = $id; }
    public function setPedidoId($id) { $this->pedidoId = $id; }
    public function setProductoId($id) { $this->productoId = $id; }
    public function setCantidad($cant) { $this->cantidad = $cant; }
    public function setPrecioUnitario($precio) { $this->precio_unitario = $precio; }
    public function setSubtotal($sub) { $this->subtotal = $sub; }
    public function setEstado($estado) { $this->estado = $estado; }
    
    public function calcularSubtotal() {
        $this->subtotal = $this->cantidad * $this->precio_unitario;
        return $this->subtotal;
    }

    public function validar() {
        $errores = array();
        
        if (empty($this->productoId)) {
            $errores[] = "El producto es requerido";
        }
        
        if ($this->cantidad <= 0) {
            $errores[] = "La cantidad debe ser mayor a 0";
        }
        
        if ($this->precio_unitario < 0) {
            $errores[] = "El precio unitario no puede ser negativo";
        }
        
        return $errores;
    }
}
?>