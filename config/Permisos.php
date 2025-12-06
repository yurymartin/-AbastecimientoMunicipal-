<?php
class Permisos {
    
    private static $permisos = [
        1 => [
            'dashboard',
            'usuarios',
            'proveedores',
            'productos',
            'pedidos',
            'reportes',
            'gastos',
            'configuracion',
            'perfil'
        ],
        
        2 => [
            'dashboard',
            'proveedores',
            'productos',
            'pedidos',
            'perfil'
        ]
        
    ];
    
    private static $nombresRoles = [
        1 => 'Administrador',
        2 => 'Empleado'
    ];
    
    /**
     * @param string
     * @return bool
     */
    public static function tiene($modulo) {
        if (!isset($_SESSION['rolId'])) {
            return false;
        }
        
        $rolId = $_SESSION['rolId'];
        
        if (!isset(self::$permisos[$rolId])) {
            return false;
        }
        
        return in_array($modulo, self::$permisos[$rolId]);
    }
    
    /**
     * @return array
     */
    public static function obtenerModulosPermitidos() {
        $rolId = $_SESSION['rolId'] ?? 0;
        return self::$permisos[$rolId] ?? [];
    }
    
    /**
     * @return bool
     */
    public static function esAdministrador() {
        return isset($_SESSION['rolId']) && $_SESSION['rolId'] == 1;
    }
    
    /**
     * @return bool
     */
    public static function esEmpleado() {
        return isset($_SESSION['rolId']) && $_SESSION['rolId'] == 2;
    }
    
    /**
     * @return string
     */
    public static function obtenerNombreRol() {
        $rolId = $_SESSION['rolId'] ?? 0;
        return self::$nombresRoles[$rolId] ?? 'Desconocido';
    }
    
    /**
     * @param string
     * @param string
     */
    public static function requierePermiso($modulo, $urlDestino = '?page=dashboard') {
        if (!self::tiene($modulo)) {
            header("Location: $urlDestino");
            exit;
        }
    }
    
    /**
     * @param string
     */
    public static function requierePermisoAjax($modulo) {
        if (!self::tiene($modulo)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción',
                'codigo' => 'PERMISO_DENEGADO'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    /**
     * @param string
     * @return string
     */
    public static function mensajeAccesoDenegado($modulo) {
        return "No tiene permisos para acceder al módulo: " . ucfirst($modulo) . 
               ". Contacte al administrador si necesita acceso.";
    }
}