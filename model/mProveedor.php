<?php
class Proveedor {
    private $idProveedor;
    private $ruc;
    private $razon_social;
    private $nombre_comercial;
    private $direccion;
    private $telefono;
    private $email;
    private $contacto_nombre;
    private $contacto_telefono;
    private $estado;
    private $fecha_registro;
    private $usuario_registro;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            $this->idProveedor = isset($data['idProveedor']) ? $data['idProveedor'] : null;
            $this->ruc = isset($data['ruc']) ? $data['ruc'] : null;
            $this->razon_social = isset($data['razon_social']) ? $data['razon_social'] : null;
            $this->nombre_comercial = isset($data['nombre_comercial']) ? $data['nombre_comercial'] : null;
            $this->direccion = isset($data['direccion']) ? $data['direccion'] : null;
            $this->telefono = isset($data['telefono']) ? $data['telefono'] : null;
            $this->email = isset($data['email']) ? $data['email'] : null;
            $this->contacto_nombre = isset($data['contacto_nombre']) ? $data['contacto_nombre'] : null;
            $this->contacto_telefono = isset($data['contacto_telefono']) ? $data['contacto_telefono'] : null;
            $this->estado = isset($data['estado']) ? $data['estado'] : 'Activo';
            $this->usuario_registro = isset($data['usuario_registro']) ? $data['usuario_registro'] : null;
        }
    }
    
    public function getIdProveedor() { return $this->idProveedor; }
    public function getRuc() { return $this->ruc; }
    public function getRazonSocial() { return $this->razon_social; }
    public function getNombreComercial() { return $this->nombre_comercial; }
    public function getDireccion() { return $this->direccion; }
    public function getTelefono() { return $this->telefono; }
    public function getEmail() { return $this->email; }
    public function getContactoNombre() { return $this->contacto_nombre; }
    public function getContactoTelefono() { return $this->contacto_telefono; }
    public function getEstado() { return $this->estado; }
    public function getFechaRegistro() { return $this->fecha_registro; }
    public function getUsuarioRegistro() { return $this->usuario_registro; }
    
    public function setIdProveedor($id) { $this->idProveedor = $id; }
    public function setRuc($ruc) { $this->ruc = $ruc; }
    public function setRazonSocial($razon) { $this->razon_social = $razon; }
    public function setNombreComercial($nombre) { $this->nombre_comercial = $nombre; }
    public function setDireccion($dir) { $this->direccion = $dir; }
    public function setTelefono($tel) { $this->telefono = $tel; }
    public function setEmail($email) { $this->email = $email; }
    public function setContactoNombre($nombre) { $this->contacto_nombre = $nombre; }
    public function setContactoTelefono($tel) { $this->contacto_telefono = $tel; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setUsuarioRegistro($id) { $this->usuario_registro = $id; }
    
    public function validar() {
        $errores = array();
        
        if (empty($this->ruc)) {
            $errores[] = "El RUC es requerido";
        } elseif (!preg_match('/^\d{11}$/', $this->ruc)) {
            $errores[] = "El RUC debe tener 11 dígitos";
        }
        
        if (empty($this->razon_social)) {
            $errores[] = "La razón social es requerida";
        }
        
        if (empty($this->nombre_comercial)) {
            $errores[] = "El nombre comercial es requerido";
        }
        
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El email no es válido";
        }
        
        if (!empty($this->telefono) && !preg_match('/^\d{7,15}$/', $this->telefono)) {
            $errores[] = "El teléfono debe tener entre 7 y 15 dígitos";
        }
        
        return $errores;
    }
    
    public function getNombreCompleto() {
        return $this->razon_social . ' (' . $this->ruc . ')';
    }
}
?>