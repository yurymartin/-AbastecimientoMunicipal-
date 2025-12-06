<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-box"></i> Gestión de Productos</h2>
            <p>Control de inventario y stock</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-warning" id="btnStockBajo" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i> <span id="textoStockBajo">Stock Bajo (0)</span>
            </button>
            <button class="btn btn-primary" onclick="abrirModalProducto()">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertaProducto"></div>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card blue">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-details">
                <h3>Total Productos</h3>
                <p class="stat-number" id="totalProductos">0</p>
            </div>
        </div>
        
        <div class="stat-card orange" id="cardStockBajo">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-details">
                <h3>Stock Bajo</h3>
                <p class="stat-number" id="totalStockBajo">0</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscarProducto" placeholder="Buscar por código, nombre...">
                </div>
                <select class="filter-select" id="filtroCategoria">
                    <option value="">Todas las categorías</option>
                    <!-- Se llenarán dinámicamente -->
                </select>
                <select class="filter-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="Activo">Activos</option>
                    <option value="Inactivo">Inactivos</option>
                </select>
                <select class="filter-select" id="filtroStock">
                    <option value="">Todo el stock</option>
                    <option value="critico">Stock Crítico</option>
                    <option value="bajo">Stock Bajo</option>
                    <option value="normal">Stock Normal</option>
                </select>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="tablaProductos">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Estado Stock</th>
                            <th>Precio Ref.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProductos">
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando productos...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Producto -->
<div id="modalProducto" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Producto</h3>
            <button class="modal-close" onclick="cerrarModalProducto()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formProducto">
            <div class="modal-body">
                <input type="hidden" id="idProducto" name="idProducto">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Código <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="codigo_producto" id="codigo_producto" required
                               placeholder="Ej: PROD-001">
                        <small class="form-help">Mínimo 3 caracteres</small>
                    </div>
                    <div class="form-group">
                        <label>Categoría <span style="color: var(--danger);">*</span></label>
                        <select name="categoriaId" id="categoriaId" required>
                            <option value="">Seleccione...</option>
                            <!-- Se llenarán dinámicamente -->
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nombre del Producto <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nombre_producto" id="nombre_producto" required
                           placeholder="Nombre descriptivo del producto">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="2"
                              placeholder="Detalles adicionales del producto"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Unidad de Medida <span style="color: var(--danger);">*</span></label>
                        <select name="unidad_medida" id="unidad_medida" required>
                            <option value="">Seleccione...</option>
                            <option value="Unidad">Unidad</option>
                            <option value="Caja">Caja</option>
                            <option value="Paquete">Paquete</option>
                            <option value="Kilogramo">Kilogramo</option>
                            <option value="Litro">Litro</option>
                            <option value="Metro">Metro</option>
                            <option value="Docena">Docena</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Precio Referencial</label>
                        <input type="number" name="precio_referencial" id="precio_referencial" 
                               step="0.01" min="0" value="0"
                               placeholder="0.00">
                    </div>
                </div>
                
                <h4 style="margin: 20px 0 15px; color: #667eea;">
                    <i class="fas fa-boxes"></i> Control de Stock
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Actual <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="stock_actual" id="stock_actual" 
                               min="0" value="0" required>
                        <small class="form-help">Cantidad disponible ahora</small>
                    </div>
                    <div class="form-group">
                        <label>Stock Mínimo <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="stock_minimo" id="stock_minimo" 
                               min="0" value="0" required>
                        <small class="form-help">Cantidad mínima para alerta</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Estado <span style="color: var(--danger);">*</span></label>
                    <select name="estado" id="estado" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalProducto()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ajustar Stock -->
<div id="modalStock" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-exchange-alt"></i> Ajustar Stock</h3>
            <button class="modal-close" onclick="cerrarModalStock()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formStock">
            <div class="modal-body">
                <input type="hidden" id="idProductoStock" name="idProducto">
                
                <div class="alert alert-info" style="margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i>
                    <strong id="nombreProductoStock"></strong>
                </div>
                
                <div class="form-group">
                    <label>Operación <span style="color: var(--danger);">*</span></label>
                    <select name="operacion" id="operacion" required>
                        <option value="sumar">📦 Entrada (Sumar) - Recepción de mercadería</option>
                        <option value="restar">📤 Salida (Restar) - Entrega de mercadería</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Cantidad <span style="color: var(--danger);">*</span></label>
                    <input type="number" name="cantidad" id="cantidad" min="1" required
                           placeholder="Ingrese la cantidad">
                </div>
                
                <div class="alert alert-warning" style="font-size: 13px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Este ajuste quedará registrado en la auditoría del sistema
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalStock()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Actualizar Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/productos.js"></script>