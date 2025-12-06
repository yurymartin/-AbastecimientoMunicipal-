<?php
require_once __DIR__ . '/../../config/Permisos.php';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-building"></i>
            <span>Abastecimiento</span>
        </div>
        <button class="toggle-btn" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <nav class="sidebar-menu">
        <!-- Dashboard - Todos tienen acceso -->
        <?php if (Permisos::tiene('dashboard')): ?>
        <a href="?page=dashboard" class="menu-item" data-page="dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>
        
        <div class="menu-section">
            <span class="section-title">GESTIÓN</span>
        </div>
        
        <!-- Usuarios - Solo Administrador -->
        <?php if (Permisos::tiene('usuarios')): ?>
        <a href="?page=usuarios" class="menu-item" data-page="usuarios">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        <?php endif; ?>
        
        <!-- Proveedores - Administrador y Empleado -->
        <?php if (Permisos::tiene('proveedores')): ?>
        <a href="?page=proveedores" class="menu-item" data-page="proveedores">
            <i class="fas fa-building"></i>
            <span>Proveedores</span>
        </a>
        <?php endif; ?>
        
        <!-- Productos - Administrador y Empleado -->
        <?php if (Permisos::tiene('productos')): ?>
        <a href="?page=productos" class="menu-item" data-page="productos">
            <i class="fas fa-box"></i>
            <span>Productos</span>
        </a>
        <?php endif; ?>
        
        <!-- Pedidos - Administrador y Empleado -->
        <?php if (Permisos::tiene('pedidos')): ?>
        <a href="?page=pedidos" class="menu-item" data-page="pedidos">
            <i class="fas fa-clipboard-list"></i>
            <span>Pedidos</span>
        </a>
        <?php endif; ?>
        
        <!-- Reportes - Solo Administrador -->
        <?php if (Permisos::tiene('reportes')): ?>
        <div class="menu-section">
            <span class="section-title">REPORTES</span>
        </div>
        
        <a href="?page=reportes" class="menu-item" data-page="reportes">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
        <?php endif; ?>
        
        <!-- Gastos - Solo Administrador -->
        <?php if (Permisos::tiene('gastos')): ?>
        <a href="?page=gastos" class="menu-item" data-page="gastos">
            <i class="fas fa-money-bill-wave"></i>
            <span>Gastos</span>
        </a>
        <?php endif; ?>
        
        <!-- Configuración - Solo Administrador -->
        <?php if (Permisos::tiene('configuracion')): ?>
        <div class="menu-section">
            <span class="section-title">SISTEMA</span>
        </div>
        
        <a href="?page=configuracion" class="menu-item" data-page="configuracion">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo $_SESSION['nombre_completo']; ?></span>
                <span class="user-role"><?php echo $_SESSION['nombreRol']; ?></span>
            </div>
        </div>
    </div>
</aside>