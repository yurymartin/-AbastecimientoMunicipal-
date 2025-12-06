<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-clipboard-list"></i> Gestión de Pedidos</h2>
            <p>Control y seguimiento de pedidos de compra</p>
        </div>
        <button class="btn btn-primary" onclick="abrirModalPedido()">
            <i class="fas fa-plus"></i> Nuevo Pedido
        </button>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertaPedido"></div>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card orange">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>Pendientes</h3>
                <p class="stat-number" id="totalPendientes">0</p>
            </div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-spinner fa-pulse"></i>
            </div>
            <div class="stat-details">
                <h3>En Proceso</h3>
                <p class="stat-number" id="totalEnProceso">0</p>
            </div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>Entregados</h3>
                <p class="stat-number" id="totalEntregados">0</p>
            </div>
        </div>
        
        <div class="stat-card purple">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <h3>Total Pedidos</h3>
                <p class="stat-number" id="totalGeneral">0</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscarPedido" placeholder="Buscar por número, proveedor...">
                </div>
                <select class="filter-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <!-- Se llenarán dinámicamente -->
                </select>
                <select class="filter-select" id="filtroProveedor">
                    <option value="">Todos los proveedores</option>
                    <!-- Se llenarán dinámicamente -->
                </select>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="tablaPedidos">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Entrega</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPedidos">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando pedidos...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Pedido -->
<div id="modalPedido" class="modal">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Pedido</h3>
            <button class="modal-close" onclick="cerrarModalPedido()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formPedido">
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="idPedido" name="idPedido">
                
                <h4 style="margin-bottom: 15px; color: #667eea;">
                    <i class="fas fa-info-circle"></i> Información General
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Proveedor <span style="color: var(--danger);">*</span></label>
                        <select name="proveedorId" id="proveedorId" required>
                            <option value="">Seleccione un proveedor...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado <span style="color: var(--danger);">*</span></label>
                        <select name="estadoId" id="estadoId" required>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de Pedido <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="fecha_pedido" id="fecha_pedido" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Entrega Estimada</label>
                        <input type="date" name="fecha_entrega_estimada" id="fecha_entrega_estimada">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2"
                              placeholder="Comentarios o instrucciones adicionales"></textarea>
                </div>
                
                <h4 style="margin: 25px 0 15px; color: #667eea;">
                    <i class="fas fa-boxes"></i> Productos del Pedido
                </h4>
                
                <button type="button" class="btn btn-secondary" onclick="agregarProductoPedido()" 
                        style="margin-bottom: 15px;">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
                
                <div id="productosContainer">

                </div>
                
                <div class="totales-pedido" style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <div class="totales-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Subtotal:</span>
                        <strong>S/ <span id="subtotalPedido">0.00</span></strong>
                    </div>
                    <div class="totales-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>IGV (18%):</span>
                        <strong>S/ <span id="igvPedido">0.00</span></strong>
                    </div>
                    <div class="totales-row" style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid #d1d5db; font-size: 18px;">
                        <span><strong>TOTAL:</strong></span>
                        <strong style="color: #059669;">S/ <span id="totalPedido">0.00</span></strong>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalPedido()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Pedido
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Detalle -->
<div id="modalDetalle" class="modal">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice"></i> Detalle del Pedido</h3>
            <button class="modal-close" onclick="cerrarModalDetalle()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div id="detalleContent">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalDetalle()">
                Cerrar
            </button>
            <button type="button" class="btn btn-info" onclick="imprimirDetalle()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<script src="assets/js/pedidos.js"></script>