<?php
class Producto {
    private $idProducto;
    private $codigo_producto;
    private $nombre_producto;
    private $descripcion;
    private $categoriaId;
    private $unidad_medida;
    private $stock_actual;
    private $stock_minimo;
    private $precio_referencial;
    private $estado;
    private $fecha_registro;
    private $usuario_registro;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->idProducto = isset($data['idProducto']) ? $data['idProducto'] : null;
            $this->codigo_producto = isset($data['codigo_producto']) ? $data['codigo_producto'] : null;
            $this->nombre_producto = isset($data['nombre_producto']) ? $data['nombre_producto'] : null;
            $this->descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
            $this->categoriaId = isset($data['categoriaId']) ? $data['categoriaId'] : null;
            $this->unidad_medida = isset($data['unidad_medida']) ? $data['unidad_medida'] : null;
            $this->stock_actual = isset($data['stock_actual']) ? $data['stock_actual'] : 0;
            $this->stock_minimo = isset($data['stock_minimo']) ? $data['stock_minimo'] : 0;
            $this->precio_referencial = isset($data['precio_referencial']) ? $data['precio_referencial'] : 0;
            $this->estado = isset($data['estado']) ? $data['estado'] : 'Activo';
            $this->usuario_registro = isset($data['usuario_registro']) ? $data['usuario_registro'] : null;
        }
    }
    
    public function getIdProducto() { return $this->idProducto; }
    public function getCodigoProducto() { return $this->codigo_producto; }
    public function getNombreProducto() { return $this->nombre_producto; }
    public function getDescripcion() { return $this->descripcion; }
    public function getCategoriaId() { return $this->categoriaId; }
    public function getUnidadMedida() { return $this->unidad_medida; }
    public function getStockActual() { return $this->stock_actual; }
    public function getStockMinimo() { return $this->stock_minimo; }
    public function getPrecioReferencial() { return $this->precio_referencial; }
    public function getEstado() { return $this->estado; }
    public function getFechaRegistro() { return $this->fecha_registro; }
    public function getUsuarioRegistro() { return $this->usuario_registro; }
    
    public function setIdProducto($id) { $this->idProducto = $id; }
    public function setCodigoProducto($codigo) { $this->codigo_producto = $codigo; }
    public function setNombreProducto($nombre) { $this->nombre_producto = $nombre; }
    public function setDescripcion($desc) { $this->descripcion = $desc; }
    public function setCategoriaId($id) { $this->categoriaId = $id; }
    public function setUnidadMedida($unidad) { $this->unidad_medida = $unidad; }
    public function setStockActual($stock) { $this->stock_actual = $stock; }
    public function setStockMinimo($stock) { $this->stock_minimo = $stock; }
    public function setPrecioReferencial($precio) { $this->precio_referencial = $precio; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setUsuarioRegistro($id) { $this->usuario_registro = $id; }
    
    public function validar() {
        $errores = array();
        
        if (empty($this->codigo_producto)) {
            $errores[] = "El código del producto es requerido";
        } elseif (strlen($this->codigo_producto) < 3) {
            $errores[] = "El código debe tener al menos 3 caracteres";
        }
        
        if (empty($this->nombre_producto)) {
            $errores[] = "El nombre del producto es requerido";
        }
        
        if (empty($this->categoriaId)) {
            $errores[] = "La categoría es requerida";
        }
        
        if (empty($this->unidad_medida)) {
            $errores[] = "La unidad de medida es requerida";
        }
        
        if ($this->stock_actual < 0) {
            $errores[] = "El stock actual no puede ser negativo";
        }
        
        if ($this->stock_minimo < 0) {
            $errores[] = "El stock mínimo no puede ser negativo";
        }
        
        if ($this->precio_referencial < 0) {
            $errores[] = "El precio no puede ser negativo";
        }
        
        return $errores;
    }
    
    public function necesitaReposicion() {
        return $this->stock_actual <= $this->stock_minimo;
    }
    
    public function getEstadoStock() {
        if ($this->stock_actual <= 0) {
            return 'Sin Stock';
        } elseif ($this->stock_actual <= $this->stock_minimo) {
            return 'Crítico';
        } elseif ($this->stock_actual <= ($this->stock_minimo * 1.5)) {
            return 'Bajo';
        } else {
            return 'Normal';
        }
    }
    
    public function getClaseStock() {
        $estado = $this->getEstadoStock();
        switch ($estado) {
            case 'Sin Stock':
            case 'Crítico':
                return 'danger';
            case 'Bajo':
                return 'warning';
            default:
                return 'success';
        }
    }
}
?>