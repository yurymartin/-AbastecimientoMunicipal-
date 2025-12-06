<div class="dashboard-content">
    <!-- Tarjetas de Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3>Usuarios</h3>
                <p class="stat-number">0</p>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Cargando...
                </span>
            </div>
        </div>
        
        <div class="stat-card purple">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <h3>Proveedores</h3>
                <p class="stat-number">0</p>
                <span class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Cargando...
                </span>
            </div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-details">
                <h3>Productos</h3>
                <p class="stat-number">0</p>
                <span class="stat-change">
                    <i class="fas fa-sync-alt fa-spin"></i> Cargando...
                </span>
            </div>
        </div>
        
        <div class="stat-card orange">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <h3>Pedidos</h3>
                <p class="stat-number">0</p>
                <span class="stat-change">
                    <i class="fas fa-sync-alt fa-spin"></i> Cargando...
                </span>
            </div>
        </div>
    </div>
    
    <!-- Sección de Gráficos y Tablas -->
    <div class="dashboard-grid">
        <!-- Pedidos Recientes -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-list"></i> Pedidos Recientes</h3>
                <a href="?page=pedidos" class="btn-link">Ver todos</a>
            </div>
            <div class="card-body">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>N° Pedido</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
                                <p style="margin-top: 10px; color: var(--text-secondary);">Cargando pedidos...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Productos con Stock Bajo -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h3>
                <a href="?page=productos" class="btn-link">Ver todos</a>
            </div>
            <div class="card-body">
                <div class="product-list">
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
                        <p style="margin-top: 10px; color: var(--text-secondary);">Cargando productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Accesos Rápidos -->
    <div class="quick-actions">
        <h3>Accesos Rápidos</h3>
        <div class="action-grid">
            <a href="?page=usuarios&action=crear" class="action-card">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Usuario</span>
            </a>
            <a href="?page=proveedores&action=crear" class="action-card">
                <i class="fas fa-building"></i>
                <span>Nuevo Proveedor</span>
            </a>
            <a href="?page=productos&action=crear" class="action-card">
                <i class="fas fa-box"></i>
                <span>Nuevo Producto</span>
            </a>
            <a href="?page=pedidos&action=crear" class="action-card">
                <i class="fas fa-clipboard-list"></i>
                <span>Nuevo Pedido</span>
            </a>
        </div>
    </div>
</div>

<!-- Agregar el CSS específico del dashboard -->
<link rel="stylesheet" href="assets/css/dashboard.css">

<!-- Agregar el JavaScript específico del dashboard -->
<script src="assets/js/dashboard.js"></script>