<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-money-bill-wave"></i> Gestión de Gastos</h2>
            <p>Registro y control de gastos asociados a pedidos</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="abrirModalGasto()">
                <i class="fas fa-plus"></i> Nuevo Gasto
            </button>
        </div>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertaGastos"></div>
    
    <!-- Tarjeta de Total -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card orange">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3>Total Gastos</h3>
                <p class="stat-number" id="totalGastosDisplay">S/ 0.00</p>
                <small><span id="cantidadGastos">0</span> registros</small>
            </div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-details">
                <h3>Mes Actual</h3>
                <p class="stat-number" id="gastosMesActual">S/ 0.00</p>
                <small><?php echo date('F Y'); ?></small>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscarGasto" placeholder="Buscar por tipo, descripción, pedido...">
                </div>
                
                <select class="filter-select" id="filtroTipoGasto">
                    <option value="">Todos los tipos</option>
                    <!-- Se llenarán dinámicamente -->
                </select>
                
                <input type="date" id="filtroFechaDesde" class="filter-select" placeholder="Desde">
                <input type="date" id="filtroFechaHasta" class="filter-select" placeholder="Hasta">
                
                <button type="button" class="btn btn-primary" onclick="filtrarGastos()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="tablaGastos">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Tipo Gasto</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Documento</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyGastos">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando gastos...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="tfootGastos" style="display: none;">
                        <tr style="background: #f3f4f6; font-weight: bold;">
                            <td colspan="6" style="text-align: right; padding: 16px;">TOTAL:</td>
                            <td colspan="2" style="padding: 16px;">
                                <strong style="color: #ef4444; font-size: 16px;" id="totalTabla">S/ 0.00</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Gasto -->
<div id="modalGasto" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Gasto</h3>
            <button class="modal-close" onclick="cerrarModalGasto()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formGasto">
            <div class="modal-body">
                <input type="hidden" id="idGasto" name="idGasto">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Pedido Asociado <span style="color: var(--danger);">*</span></label>
                        <select name="pedidoId" id="pedidoId" required>
                            <option value="">Seleccione un pedido...</option>
                            <!-- Se llenarán dinámicamente -->
                        </select>
                        <small class="form-help">Seleccione el pedido al que pertenece este gasto</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Gasto <span style="color: var(--danger);">*</span></label>
                        <select name="tipo_gasto" id="tipo_gasto" required>
                            <option value="">Seleccione...</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Almacenamiento">Almacenamiento</option>
                            <option value="Embalaje">Embalaje</option>
                            <option value="Seguro">Seguro</option>
                            <option value="Impuestos">Impuestos</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Monto (S/) <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="monto" id="monto" 
                               step="0.01" min="0.01" required
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha del Gasto <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="fecha_gasto" id="fecha_gasto" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <h4 style="margin: 20px 0 15px; color: #667eea;">
                    <i class="fas fa-file-invoice"></i> Documento (Opcional)
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de Documento</label>
                        <select name="tipo_documento" id="tipo_documento">
                            <option value="">Sin documento</option>
                            <option value="Factura">Factura</option>
                            <option value="Boleta">Boleta</option>
                            <option value="Recibo">Recibo</option>
                            <option value="Nota de Crédito">Nota de Crédito</option>
                            <option value="Nota de Débito">Nota de Débito</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Número de Documento</label>
                        <input type="text" name="numero_documento" id="numero_documento"
                               placeholder="Ej: F001-12345">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="3"
                              placeholder="Detalles adicionales del gasto..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalGasto()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Gasto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cargar el JavaScript específico -->
<script src="assets/js/gastos.js"></script>