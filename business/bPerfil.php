<?php
require_once __DIR__ . '/../dao/daoPerfil.php';
require_once __DIR__ . '/../config/Database.php';

class bPerfil {
    private $perfilDAO;
    
    public function __construct() {
        $this->perfilDAO = new daoPerfil();
    }
    
    public function obtenerPerfilB($idUsuario) {
        return $this->perfilDAO->obtenerPerfil($idUsuario);
    }
    
    public function actualizarPerfilB($datos) {
        try {
            $errores = $this->validarDatosPerfil($datos);
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }
            
            if ($this->perfilDAO->verificarEmailExiste($datos['email'], $datos['idUsuario'])) {
                return array('success' => false, 'message' => 'El email ya está registrado por otro usuario');
            }
            
            if ($this->perfilDAO->verificarDniExiste($datos['dni'], $datos['idUsuario'])) {
                return array('success' => false, 'message' => 'El DNI ya está registrado por otro usuario');
            }
            
            if ($this->perfilDAO->actualizarPerfil($datos)) {
                $_SESSION['nombres'] = $datos['nombres'];
                $_SESSION['apellidos'] = $datos['apellidos'];
                $_SESSION['nombre_completo'] = $datos['nombres'] . ' ' . $datos['apellidos'];
                $_SESSION['email'] = $datos['email'];
                
                $this->registrarAuditoria('usuarios', $datos['idUsuario'], 'UPDATE');
                
                return array('success' => true, 'message' => 'Perfil actualizado exitosamente');
            }
            
            return array('success' => false, 'message' => 'Error al actualizar el perfil');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    // CAMBIAR CONTRASEÑA
    public function cambiarPasswordB($idUsuario, $passwordActual, $passwordNueva, $passwordConfirmar) {
        try {
            if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirmar)) {
                return array('success' => false, 'message' => 'Todos los campos son requeridos');
            }
            
            if ($passwordNueva !== $passwordConfirmar) {
                return array('success' => false, 'message' => 'Las contraseñas nuevas no coinciden');
            }
            
            if (strlen($passwordNueva) < 6) {
                return array('success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres');
            }
            
            if ($passwordActual === $passwordNueva) {
                return array('success' => false, 'message' => 'La nueva contraseña debe ser diferente a la actual');
            }
            
            $resultado = $this->perfilDAO->cambiarPassword($idUsuario, $passwordActual, $passwordNueva);
            
            if ($resultado['success']) {
                $this->registrarAuditoria('usuarios', $idUsuario, 'UPDATE');
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
    
    public function obtenerHistorialActividadB($idUsuario, $limite = 50) {
        return $this->perfilDAO->obtenerHistorialActividad($idUsuario, $limite);
    }
    
    public function obtenerEstadisticasUsuarioB($idUsuario) {
        return $this->perfilDAO->obtenerEstadisticasUsuario($idUsuario);
    }
    
    public function obtenerSesionesUsuarioB($idUsuario, $limite = 10) {
        return $this->perfilDAO->obtenerSesionesUsuario($idUsuario, $limite);
    }
    
    private function validarDatosPerfil($datos) {
        $errores = array();
        
        if (empty($datos['nombres'])) {
            $errores[] = "El nombre es requerido";
        }
        
        if (empty($datos['apellidos'])) {
            $errores[] = "Los apellidos son requeridos";
        }
        
        if (empty($datos['dni'])) {
            $errores[] = "El DNI es requerido";
        } elseif (!preg_match('/^[0-9]{8}$/', $datos['dni'])) {
            $errores[] = "El DNI debe tener 8 dígitos";
        }
        
        if (empty($datos['email'])) {
            $errores[] = "El email es requerido";
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El email no es válido";
        }
        
        return $errores;
    }
    
    private function registrarAuditoria($tabla, $idRegistro, $accion) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO auditoria (tabla_afectada, registroId, accion, usuarioId, ip_address)
                      VALUES (:tabla, :registroId, :accion, :usuarioId, :ip)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":tabla", $tabla);
            $stmt->bindParam(":registroId", $idRegistro);
            $stmt->bindParam(":accion", $accion);
            $idUsuario = $_SESSION['idUsuario'] ?? null;
            $stmt->bindParam(":usuarioId", $idUsuario);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt->bindParam(":ip", $ip);
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
}
?>