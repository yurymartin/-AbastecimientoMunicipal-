<?php
require_once __DIR__ . '/../dao/daoUsuario.php';
require_once __DIR__ . '/../model/mUsuario.php';

class bUsuario{
    private $usuarioDAO;

    public function __construct(){
        $this->usuarioDAO = new daoUsuario();
    }

    
    public function crearUsuarioB($datos){
        try{
            $usuario = new usuario($datos);

            
            $errores = $usuario->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }

            
            if($this->usuarioDAO->existeUsername($usuario->getUsername())){
                return array('success' => false, 'message' => 'El nombre de usuario ya existe');
            }

            
            $id = $this->usuarioDAO->crearUsuario($usuario);

            if($id){
                
                $this->registrarAuditoria('usuarios', $id, 'INSERT', $_SESSION['id_usuario'] ?? null);
                return array('success' => true, 'message' => 'Usuario creado exitosamente', 'id' => $id);
            }

            return array('success' => false, 'message' => 'Error al crear el usuario');

        } catch(Exception $e){
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    
    public function actualizarUsuarioB($datos){
        try{
            $usuario = new usuario($datos);

            
            $errores = $usuario->validar();
            if (!empty($errores)) {
                return array('success' => false, 'message' => implode(', ', $errores));
            }

            
            if ($this->usuarioDAO->existeUsername($usuario->getUsername(), $usuario->getIdUsuario())){
                return array('success' => false, 'message' => 'El nombre de usuario ya existe');
            }

            
            if ($this->usuarioDAO->actualizarUsuario($usuario)){
                $this->registrarAuditoria('usuarios', $usuario->getIdUsuario(), 'UPDATE', $_SESSION['id_usuario'] ?? null);
                return array('success' => true, 'message' => 'Usuario actualizado exitosamente');
            }

            return array('success' => false, 'message' => 'Error al actualizar el usuario');

        } catch(Exception $e){
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    
    public function eliminarUsuarioB($id){
        try{
            
            if ($this->esUltimoAdministrador($id)){
                return array('success' => false, 'message' => 'No se puede eliminar el último administrador del sistema');
            }

            if ($this->usuarioDAO->desactivarUsuario($id)){
                $this->registrarAuditoria('usuarios', $id, 'DELETE', $_SESSION['idUsuario'] ?? null);
                return array('success' => true, 'message' => 'Usuario eliminado exitosamente');
            }

            return array('success' => false, 'message' => 'Error al eliminar el usuario');

        } catch(Exception $e){
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    
    public function listarUsuB($pagina = 1, $porPagina = 10){
        return $this->usuarioDAO->listarUsuario($pagina, $porPagina);
    }

    public function contarUsuariosB() {
        return $this->usuarioDAO->contarUsuarios();
    }

    
    public function obtenerUsuPorIdB($id) {
        return $this->usuarioDAO->obtenerporIdUsuario($id);
    }

    
    public function autenticar($username, $password) {
        try {
            $usuario = $this->usuarioDAO->buscarPorUsername($username);
            
            if (!$usuario) {
                return array('success' => false, 'message' => 'Credenciales incorrectas');
            }
            
            if (!password_verify($password, $usuario['password'])) {
                return array('success' => false, 'message' => 'Credenciales incorrectas');
            }
            
            
            $_SESSION['idUsuario'] = $usuario['idUsuario'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['nombre_completo'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
            $_SESSION['rolId'] = $usuario['rolId'];
            $_SESSION['nombreRol'] = $usuario['nombreRol'];
            
            
            $this->registrarSesion($usuario['idUsuario']);
            
            return array('success' => true, 'message' => 'Autenticación exitosa', 'usuario' => $usuario);
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    
    public function obtenerRoles() {
        return $this->usuarioDAO->obtenerRoles();
    }

    
    private function esUltimoAdministrador($idUsuario) {
        $usuario = $this->usuarioDAO->obtenerporIdUsuario($idUsuario);
        if ($usuario && $usuario['rolId'] == 1) { 
            $usuarios = $this->usuarioDAO->listarUsuario();
            $cantidadAdmins = 0;
            foreach ($usuarios as $u) {
                if ($u['rolId'] == 1 && $u['estado'] == 'Activo') {
                    $cantidadAdmins++;
                }
            }
            return $cantidadAdmins <= 1;
        }
        return false;
    }

    
    private function registrarAuditoria($tabla, $idRegistro, $accion, $idUsuario) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO auditoria (tabla_afectada, registroId, accion, usuarioId, ip_address) 
                      VALUES (:tabla, :registroId, :accion, :usuarioId, :ip)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":tabla", $tabla);
            $stmt->bindParam(":registroId", $idRegistro);
            $stmt->bindParam(":accion", $accion);
            $stmt->bindParam(":usuarioId", $idUsuario);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt->bindParam(":ip", $ip);
            
            $stmt->execute();
        } catch (Exception $e) {
            
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }

    
    private function registrarSesion($idUsuario) {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            $token = bin2hex(random_bytes(32));
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $query = "INSERT INTO sesiones (usuarioId, token_sesion, ip_address, user_agent, fecha_expiracion) 
                      VALUES (:usuarioId, :token, :ip, :user_agent, :fecha_exp)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":usuarioId", $idUsuario);
            $stmt->bindParam(":token", $token);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt->bindParam(":ip", $ip);
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
            $stmt->bindParam(":user_agent", $userAgent);
            $stmt->bindParam(":fecha_exp", $fechaExpiracion);
            
            $stmt->execute();
            
            $_SESSION['token_sesion'] = $token;
        } catch (Exception $e) {
            error_log("Error al registrar sesión: " . $e->getMessage());
        }
    }

}