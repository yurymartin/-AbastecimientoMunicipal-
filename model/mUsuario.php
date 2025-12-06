<?php
class usuario {
    private $idUsuario;
    private $username;
    private $password;
    private $nombres;
    private $apellidos;
    private $dni;
    private $email;
    private $rolId;
    private $estado;
    private $fecha_creacion;

    public function __construct($data = array()){
        if (!empty($data)){
            $this->idUsuario = isset($data['idUsuario']) ? $data['idUsuario'] : null;
            $this->username = isset($data['username']) ? $data['username'] : null;
            $this->password = isset($data['password']) ? $data['password'] : null;
            $this->nombres = isset($data['nombres']) ? $data['nombres'] : null;
            $this->apellidos = isset($data['apellidos']) ? $data['apellidos'] : null;
            $this->dni = isset($data['dni']) ? $data['dni'] : null;
            $this->email = isset($data['email']) ? $data['email'] : null;
            $this->rolId = isset($data['rolId']) ? $data['rolId'] : null;
            $this->estado = isset($data['estado']) ? $data['estado'] : 'Activo';
        }
    }

    public function getIdUsuario(){ return $this->idUsuario; }
    public function getUsername(){ return $this->username;}
    public function getPassword(){ return $this->password; }
    public function getNombres(){ return $this->nombres; }
    public function getApellidos(){ return $this->apellidos; }
    public function getDni(){ return $this->dni; }
    public function getEmail(){ return $this->email; }
    public function getRolid(){ return $this->rolId; }
    public function getEstado(){ return $this->estado; }
    public function getFechaCreacion(){ return $this->fecha_creacion; }

    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function settUsername($username) { $this->username = $username; }
    public function settPassword($password) { $this->password = $password; }
    public function settNombres($nombres) { $this->nombres = $nombres; }
    public function settApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function settDni($dni) { $this->dni = $dni; }
    public function settEmail($email) { $this->email = $email; }
    public function settRolid($rolId) { $this->rolId = $rolId; }
    public function settEstado($estado) { $this->estado = $estado; }

    public function getNombreCompleto() {
        return $this->nombres . ' ' . $this->apellidos;
    }

    public function validar() {
        $errores = array();
        
        if (empty($this->username)) {
            $errores[] = "El nombre de usuario es requerido";
        }
        
        if (empty($this->password) && empty($this->idUsuario)) {
            $errores[] = "La contraseña es requerida";
        }

        if (empty($this->nombres)) {
            $errores[] = "Los nombres son requeridos";
        }

        if (empty($this->apellidos)) {
            $errores[] = "Los apellidos son requeridos";
        }

        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El email no es válido";
        }

        if (empty($this->rolId)) {
            $errores[] = "El rol es requerido";
        }

        if (!empty($this->dni) && !preg_match('/^[0-9]{8}$/', $this->dni)) {
            $errores[] = "El DNI debe tener 8 dígitos";
        }

        return $errores;
    }

};