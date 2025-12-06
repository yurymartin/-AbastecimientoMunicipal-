<?php
require_once __DIR__ . '/../config/Database.php';

class daoPerfil {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function obtenerPerfil($idUsuario) {
        $query = "SELECT u.*, r.nombreRol, r.descripcion as rol_descripcion
                  FROM usuarios u
                  INNER JOIN roles r ON u.rolId = r.idRol
                  WHERE u.idUsuario = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function actualizarPerfil($datos) {
        $query = "UPDATE usuarios 
                  SET nombres = :nombres,
                      apellidos = :apellidos,
                      dni = :dni,
                      email = :email
                  WHERE idUsuario = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $datos['idUsuario']);
        $stmt->bindParam(":nombres", $datos['nombres']);
        $stmt->bindParam(":apellidos", $datos['apellidos']);
        $stmt->bindParam(":dni", $datos['dni']);
        $stmt->bindParam(":email", $datos['email']);
        
        return $stmt->execute();
    }
    
    public function cambiarPassword($idUsuario, $passwordActual, $passwordNueva) {
        $query = "SELECT password FROM usuarios WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
            return array('success' => false, 'message' => 'La contraseña actual es incorrecta');
        }
        
        $query = "UPDATE usuarios SET password = :password WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $passwordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $stmt->bindParam(":password", $passwordHash);
        
        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Contraseña actualizada exitosamente');
        }
        
        return array('success' => false, 'message' => 'Error al actualizar la contraseña');
    }
    
    public function verificarEmailExiste($email, $idUsuario) {
        $query = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE email = :email AND idUsuario != :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }
    
    public function verificarDniExiste($dni, $idUsuario) {
        $query = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE dni = :dni AND idUsuario != :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dni", $dni);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }
    
    public function obtenerHistorialActividad($idUsuario, $limite = 50) {
        $query = "SELECT a.*, 
                         CASE 
                             WHEN a.accion = 'INSERT' THEN 'Creó'
                             WHEN a.accion = 'UPDATE' THEN 'Actualizó'
                             WHEN a.accion = 'DELETE' THEN 'Eliminó'
                             ELSE a.accion
                         END as accion_texto
                  FROM auditoria a
                  WHERE a.usuarioId = :id
                  ORDER BY a.fecha_accion DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticasUsuario($idUsuario) {
        $stats = array();
        
        $query = "SELECT COUNT(*) as total FROM pedidos WHERE usuario_solicita = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pedidos_solicitados'] = $row['total'];
        
        $query = "SELECT COUNT(*) as total FROM gastos WHERE usuario_registro = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['gastos_registrados'] = $row['total'];
        
        $query = "SELECT COUNT(*) as total FROM productos WHERE usuario_registro = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['productos_registrados'] = $row['total'];
        
        $query = "SELECT COUNT(*) as total FROM auditoria WHERE usuarioId = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_acciones'] = $row['total'];
        
        $query = "SELECT MAX(fecha_accion) as ultima_actividad FROM auditoria WHERE usuarioId = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ultima_actividad'] = $row['ultima_actividad'];
        
        return $stats;
    }
    
    public function obtenerSesionesUsuario($idUsuario, $limite = 10) {
        $query = "SELECT * FROM sesiones 
                  WHERE usuarioId = :id 
                  ORDER BY fecha_inicio DESC 
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $idUsuario);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>