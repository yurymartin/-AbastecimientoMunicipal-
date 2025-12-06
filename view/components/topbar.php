<?php
require_once __DIR__ . '/../../config/Permisos.php';
?>
<header class="topbar">
    <div class="topbar-left">
        <button class="mobile-toggle" id="mobileToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title" id="pageTitle">Dashboard</h1>
    </div>
    
    <div class="topbar-right">
        
        <div class="user-menu">
            <button class="user-btn" id="userMenuBtn">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span class="user-name"><?php echo $_SESSION['nombre_completo']; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <div class="user-dropdown" id="userDropdown">
                <a href="?page=perfil" class="dropdown-item">
                    <i class="fas fa-user"></i> Mi Perfil
                </a>
                <?php if (Permisos::tiene('usuarios')): ?>
                <a href="?page=configuracion" class="dropdown-item">
                    <i class="fas fa-cog"></i> Configuración
                </a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a href="controller/cUsuario.php?action=logout" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</header>