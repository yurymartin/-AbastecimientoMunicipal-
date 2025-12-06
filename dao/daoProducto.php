<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/mProducto.php';

class daoProducto {
    private $conn;
    private $table = "productos";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function crearProducto(Producto $producto) {
        $query = "INSERT INTO " . $this->table . "
                  (codigo_producto, nombre_producto, descripcion, categoriaId,
                   unidad_medida, stock_actual, stock_minimo, precio_referencial,
                   estado, usuario_registro)
                  VALUES (:codigo, :nombre, :descripcion, :categoria,
                          :unidad, :stock_actual, :stock_minimo, :precio,
                          :estado, :usuario)";
        
        $stmt = $this->conn->prepare($query);
        
        $codigo = $producto->getCodigoProducto();
        $nombre = $producto->getNombreProducto();
        $descripcion = $producto->getDescripcion();
        $categoria = $producto->getCategoriaId();
        $unidad = $producto->getUnidadMedida();
        $stock_actual = $producto->getStockActual();
        $stock_minimo = $producto->getStockMinimo();
        $precio = $producto->getPrecioReferencial();
        $estado = $producto->getEstado();
        $usuario = $producto->getUsuarioRegistro();
        
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":unidad", $unidad);
        $stmt->bindParam(":stock_actual", $stock_actual);
        $stmt->bindParam(":stock_minimo", $stock_minimo);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":usuario", $usuario);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function listarProducto($soloActivos = false) {
        $query = "SELECT p.*, c.nombre as nombre_categoria
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoriaId = c.idCategoria";
        
        if ($soloActivos) {
            $query .= " WHERE p.estado = 'Activo'";
        }
        
        $query .= " ORDER BY p.nombre_producto ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $productos = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = $row;
        }
        
        return $productos;
    }
    
    public function obtenerPorIdProducto($id) {
        $query = "SELECT p.*, c.nombre as nombre_categoria
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoriaId = c.idCategoria
                  WHERE p.idProducto = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function actualizarProducto(Producto $producto) {
        $query = "UPDATE " . $this->table . "
                  SET codigo_producto = :codigo,
                      nombre_producto = :nombre,
                      descripcion = :descripcion,
                      categoriaId = :categoria,
                      unidad_medida = :unidad,
                      stock_actual = :stock_actual,
                      stock_minimo = :stock_minimo,
                      precio_referencial = :precio,
                      estado = :estado
                  WHERE idProducto = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $id = $producto->getIdProducto();
        $codigo = $producto->getCodigoProducto();
        $nombre = $producto->getNombreProducto();
        $descripcion = $producto->getDescripcion();
        $categoria = $producto->getCategoriaId();
        $unidad = $producto->getUnidadMedida();
        $stock_actual = $producto->getStockActual();
        $stock_minimo = $producto->getStockMinimo();
        $precio = $producto->getPrecioReferencial();
        $estado = $producto->getEstado();
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":unidad", $unidad);
        $stmt->bindParam(":stock_actual", $stock_actual);
        $stmt->bindParam(":stock_minimo", $stock_minimo);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":estado", $estado);
        
        return $stmt->execute();
    }
    
    public function desactivarProducto($id) {
        $query = "UPDATE " . $this->table . " SET estado = 'Inactivo' WHERE idProducto = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    public function existeCodigo($codigo, $excluirId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE codigo_producto = :codigo";
        
        if ($excluirId) {
            $query .= " AND idProducto != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        
        if ($excluirId) {
            $stmt->bindParam(":id", $excluirId);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }
    
    public function obtenerStockBajo() {
        $query = "SELECT p.*, c.nombre as nombre_categoria
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoriaId = c.idCategoria
                  WHERE p.stock_actual <= p.stock_minimo
                  AND p.estado = 'Activo'
                  ORDER BY p.stock_actual ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function actualizarStock($idProducto, $cantidad, $operacion = 'sumar') {
        if ($operacion == 'sumar') {
            $query = "UPDATE " . $this->table . "
                      SET stock_actual = stock_actual + :cantidad
                      WHERE idProducto = :id";
        } else {
            $query = "UPDATE " . $this->table . "
                      SET stock_actual = stock_actual - :cantidad
                      WHERE idProducto = :id AND stock_actual >= :cantidad";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":id", $idProducto);
        
        return $stmt->execute();
    }
    
    public function buscarProducto($termino) {
        $query = "SELECT p.*, c.nombre as nombre_categoria
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoriaId = c.idCategoria
                  WHERE (p.nombre_producto LIKE :termino
                         OR p.codigo_producto LIKE :termino
                         OR p.descripcion LIKE :termino)
                  AND p.estado = 'Activo'
                  ORDER BY p.nombre_producto ASC";
        
        $stmt = $this->conn->prepare($query);
        $terminoBusqueda = "%{$termino}%";
        $stmt->bindParam(":termino", $terminoBusqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerCategorias() {
        $query = "SELECT * FROM categorias ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTotalProductos() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE estado = 'Activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function obtenerTotalStockBajo() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . "
                  WHERE stock_actual <= stock_minimo AND estado = 'Activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>