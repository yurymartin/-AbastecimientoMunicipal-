<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once __DIR__ . '/../business/bPerfil.php';

class PerfilController {
    private $perfilBusiness;
    
    public function __construct() {
        $this->perfilBusiness = new bPerfil();
    }
    
    public function procesarPeticion() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'obtener':
                $this->obtenerPerfil();
                break;
            case 'actualizar':
                $this->actualizarPerfil();
                break;
            case 'cambiar_password':
                $this->cambiarPassword();
                break;
            case 'estadisticas':
                $this->obtenerEstadisticas();
                break;
            case 'actividad':
                $this->obtenerActividad();
                break;
            case 'sesiones':
                $this->obtenerSesiones();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    private function obtenerPerfil() {
        try {
            $idUsuario = $_SESSION['idUsuario'];
            $perfil = $this->perfilBusiness->obtenerPerfilB($idUsuario);
            
            if ($perfil) {
                echo json_encode(['success' => true, 'data' => $perfil]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Perfil no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener perfil: ' . $e->getMessage()
            ]);
        }
    }
    
    private function actualizarPerfil() {
        try {
            $datos = [
                'idUsuario' => $_SESSION['idUsuario'],
                'nombres' => trim($_POST['nombres'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'dni' => trim($_POST['dni'] ?? ''),
                'email' => trim($_POST['email'] ?? '')
            ];
            
            $resultado = $this->perfilBusiness->actualizarPerfilB($datos);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ]);
        }
    }
    
    private function cambiarPassword() {
        try {
            $idUsuario = $_SESSION['idUsuario'];
            $passwordActual = $_POST['password_actual'] ?? '';
            $passwordNueva = $_POST['password_nueva'] ?? '';
            $passwordConfirmar = $_POST['password_confirmar'] ?? '';
            
            $resultado = $this->perfilBusiness->cambiarPasswordB(
                $idUsuario,
                $passwordActual,
                $passwordNueva,
                $passwordConfirmar
            );
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cambiar contraseña: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerEstadisticas() {
        try {
            $idUsuario = $_SESSION['idUsuario'];
            $estadisticas = $this->perfilBusiness->obtenerEstadisticasUsuarioB($idUsuario);
            
            echo json_encode([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerActividad() {
        try {
            $idUsuario = $_SESSION['idUsuario'];
            $limite = $_GET['limite'] ?? 50;
            
            $actividad = $this->perfilBusiness->obtenerHistorialActividadB($idUsuario, $limite);
            
            echo json_encode([
                'success' => true,
                'data' => $actividad
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerSesiones() {
        try {
            $idUsuario = $_SESSION['idUsuario'];
            $limite = $_GET['limite'] ?? 10;
            
            $sesiones = $this->perfilBusiness->obtenerSesionesUsuarioB($idUsuario, $limite);
            
            echo json_encode([
                'success' => true,
                'data' => $sesiones
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

try {
    $controller = new PerfilController();
    $controller->procesarPeticion();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>