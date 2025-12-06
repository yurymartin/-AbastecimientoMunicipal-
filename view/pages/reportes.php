<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h2>
            <p>Análisis y generación de reportes del sistema</p>
        </div>
    </div>
    
    <!-- Filtros de Fecha -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 28px;">
            <div style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <label>Fecha Inicio</label>
                    <input type="date" id="fechaInicio" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <label>Fecha Fin</label>
                    <input type="date" id="fechaFin" value="<?php echo date('Y-m-t'); ?>">
                </div>
                <button type="button" class="btn btn-primary" onclick="cargarEstadisticas()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetearFiltros()">
                    <i class="fas fa-undo"></i> Mes Actual
                </button>
            </div>
        </div>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertaReportes"></div>
    
    <!-- Resumen General -->
    <div class="stats-grid" style="margin-bottom: 30px;" id="statsContainer">
        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <h3>Total Pedidos</h3>
                <p class="stat-number" id="totalPedidos">0</p>
                <small id="montoPedidos">S/ 0.00</small>
            </div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <h3>Proveedores Activos</h3>
                <p class="stat-number" id="totalProveedores">0</p>
                <small>En el período</small>
            </div>
        </div>
        
        <div class="stat-card orange">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3>Total Gastos</h3>
                <p class="stat-number" id="totalGastos">0</p>
                <small id="montoGastos">S/ 0.00</small>
            </div>
        </div>
        
        <div class="stat-card purple">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-details">
                <h3>Productos Pedidos</h3>
                <p class="stat-number" id="totalProductos">0</p>
                <small>Diferentes productos</small>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Gráfico de Pedidos por Estado -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Pedidos por Estado</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPedidosEstado" style="max-height: 300px;"></canvas>
            </div>
        </div>
        
        <!-- Gráfico de Gastos por Tipo -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Gastos por Tipo</h3>
            </div>
            <div class="card-body">
                <canvas id="chartGastosTipo" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Pedidos por Mes -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Pedidos por Mes (<?php echo date('Y'); ?>)</h3>
        </div>
        <div class="card-body">
            <canvas id="chartPedidosMes" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <!-- Top Proveedores y Productos -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Top Proveedores -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-trophy"></i> Top 10 Proveedores</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTopProveedores" style="max-height: 400px;"></canvas>
            </div>
        </div>
        
        <!-- Top Productos -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-trophy"></i> Top 10 Productos</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTopProductos" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Opciones de Reportes -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-file-alt"></i> Generar Reportes</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <!-- Reporte de Pedidos -->
                <div class="report-option">
                    <h4><i class="fas fa-clipboard-list"></i> Reporte de Pedidos</h4>
                    <p>Listado detallado de pedidos por período</p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn btn-success" style="flex: 1;" onclick="exportarReporte('pedidos', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-danger" style="flex: 1;" onclick="exportarReporte('pedidos', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                
                <!-- Reporte de Gastos -->
                <div class="report-option">
                    <h4><i class="fas fa-money-bill-wave"></i> Reporte de Gastos</h4>
                    <p>Detalle de gastos por período y tipo</p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn btn-success" style="flex: 1;" onclick="exportarReporte('gastos', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-danger" style="flex: 1;" onclick="exportarReporte('gastos', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                
                <!-- Reporte de Proveedores -->
                <div class="report-option">
                    <h4><i class="fas fa-building"></i> Reporte de Proveedores</h4>
                    <p>Análisis completo de proveedores</p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn btn-success" style="flex: 1;" onclick="exportarReporte('proveedores', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-danger" style="flex: 1;" onclick="exportarReporte('proveedores', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                
                <!-- Reporte de Productos -->
                <div class="report-option">
                    <h4><i class="fas fa-box"></i> Reporte de Productos</h4>
                    <p>Inventario y movimientos de productos</p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn btn-success" style="flex: 1;" onclick="exportarReporte('productos', 'excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-danger" style="flex: 1;" onclick="exportarReporte('productos', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-option {
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}
.report-option:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.report-option h4 {
    color: #1f2937;
    margin-bottom: 10px;
    font-size: 16px;
}
.report-option p {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="assets/js/reportes.js"></script>