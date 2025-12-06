<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Abastecimiento Municipal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    
    <?php if ($page === 'dashboard'): ?>
        <link rel="stylesheet" href="assets/css/dashboard.css">
    <?php endif; ?>
</head>
<body>
    <div class="app-container">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/view/components/sidebar.php'; ?>
        
        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-content">
            <!-- TOPBAR -->
            <?php include __DIR__ . '/view/components/topbar.php'; ?>
            
            <!-- ÁREA DE CONTENIDO -->
            <div id="content-area" class="content-area">
                <?php
                $pagePath = __DIR__ . "/view/pages/{$page}.php";
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    include __DIR__ . '/view/pages/dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/app.js"></script>
    
    <<?php if ($page === 'dashboard'): ?>
        <script src="assets/js/dashboard.js"></script>
    <?php elseif ($page === 'usuarios'): ?>
        <script src="assets/js/usuarios.js"></script>
    <?php elseif ($page === 'proveedores'): ?>
        <script src="assets/js/proveedores.js"></script>
    <?php elseif ($page === 'productos'): ?>
        <script src="assets/js/productos.js"></script>
    <?php elseif ($page === 'pedidos'): ?>
        <script src="assets/js/pedidos.js"></script>
    <?php elseif ($page === 'reportes'): ?>
        <script src="assets/js/reportes.js"></script>
    <?php elseif ($page === 'gastos'): ?>
        <script src="assets/js/gastos.js"></script>
    <?php elseif ($page === 'perfil'): ?>
        <script src="assets/js/perfil.js"></script>
    <?php elseif ($page === 'configuracion'): ?>
        <script src="assets/js/configuracion.js"></script>
    <?php endif; ?>
</body>
</html>