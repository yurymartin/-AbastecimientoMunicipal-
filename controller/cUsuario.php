<?php
session_start();

require_once __DIR__ . '/../business/bUsuario.php';

class UsuarioController {
    private $usuarioBusiness;
    
    public function __construct() {
        $this->usuarioBusiness = new bUsuario();
    }
    
    public function procesarPeticion() {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $action = $_GET['action'] ?? $_POST['action'] ?? 'listar';
        
        switch ($action) {
            case 'listar':
                $this->listar();
                break;
            case 'obtener':
                $this->obtener();
                break;
            case 'guardar':
                $this->guardar();
                break;
            case 'eliminar':
                $this->eliminar();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                $this->listar();
        }
    }
    
    private function listar() {
        $this->verificarSesion();
        $usuarios = $this->usuarioBusiness->listarUsuB();
        require_once __DIR__ . '/../view/usuario/listar.php';
    }
    
    private function obtener() {
        $this->verificarSesion();
        
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_GET['id'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID no proporcionado'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $usuario = $this->usuarioBusiness->obtenerUsuPorIdB($_GET['id']);
        
        if ($usuario) {
            echo json_encode([
                'success' => true, 
                'data' => $usuario
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Usuario no encontrado'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
    private function guardar() {
        $this->verificarSesion();
        
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false, 
                'message' => 'Método no permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $datos = array(
                'idUsuario' => $_POST['idUsuario'] ?? null,
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'nombres' => trim($_POST['nombres'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'dni' => trim($_POST['dni'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'rolId' => $_POST['rolId'] ?? '',
                'estado' => $_POST['estado'] ?? 'Activo'
            );
            
            if (!empty($datos['idUsuario']) && empty($datos['password'])) {
                unset($datos['password']);
            }
            
            if (empty($datos['idUsuario'])) {
                $resultado = $this->usuarioBusiness->crearUsuarioB($datos);
            } else {
                $resultado = $this->usuarioBusiness->actualizarUsuarioB($datos);
            }
            
            if (!is_array($resultado)) {
                $resultado = [
                    'success' => false,
                    'message' => 'Error inesperado en la operación'
                ];
            }
            
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }
    
    private function eliminar() {
        $this->verificarSesion();
        
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false, 
                'message' => 'Método no permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!isset($_POST['id'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID no proporcionado'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $resultado = $this->usuarioBusiness->eliminarUsuarioB($_POST['id']);
            
            if (!is_array($resultado)) {
                $resultado = [
                    'success' => false,
                    'message' => 'Error inesperado en la operación'
                ];
            }
            
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }
    
    private function logout() {
        session_destroy();
        header("Location: ../login.php");
        exit;
    }
    
    private function verificarSesion() {
        if (!isset($_SESSION['idUsuario'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                
                if (ob_get_level()) {
                    ob_clean();
                }
                
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Sesión expirada', 
                    'redirect' => true
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                header("Location: ../login.php");
                exit;
            }
        }
    }
}

$controller = new UsuarioController();
$controller->procesarPeticion();