<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-building"></i> Gestión de Proveedores</h2>
            <p>Administra los proveedores del sistema</p>
        </div>
        <button class="btn btn-primary" onclick="abrirModalProveedor()">
            <i class="fas fa-plus"></i> Nuevo Proveedor
        </button>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertaProveedor"></div>
    
    <div class="card">
        <div class="card-header">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscarProveedor" placeholder="Buscar por RUC, razón social o nombre comercial...">
                </div>
                <select class="filter-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="Activo">Activos</option>
                    <option value="Inactivo">Inactivos</option>
                </select>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="tablaProveedores">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>RUC</th>
                            <th>Razón Social</th>
                            <th>Nombre Comercial</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProveedores">
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando proveedores...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Proveedor -->
<div id="modalProveedor" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Proveedor</h3>
            <button class="modal-close" onclick="cerrarModalProveedor()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formProveedor">
            <div class="modal-body">
                <input type="hidden" id="idProveedor" name="idProveedor">
                
                <h4 style="margin-bottom: 15px; color: #667eea;">
                    <i class="fas fa-building"></i> Datos de la Empresa
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>RUC <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="ruc" id="ruc" maxlength="11" 
                               pattern="\d{11}" required
                               placeholder="Ej: 20123456789">
                        <small class="form-help">Debe tener 11 dígitos</small>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="telefono" 
                               maxlength="15"
                               placeholder="Ej: 987654321">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Razón Social <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="razon_social" id="razon_social" required
                           placeholder="Ej: EMPRESA S.A.C.">
                </div>
                
                <div class="form-group">
                    <label>Nombre Comercial <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nombre_comercial" id="nombre_comercial" required
                           placeholder="Ej: Mi Empresa">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email"
                           placeholder="email@empresa.com">
                </div>
                
                <div class="form-group">
                    <label>Dirección</label>
                    <textarea name="direccion" id="direccion" rows="2"
                              placeholder="Dirección completa de la empresa"></textarea>
                </div>
                
                <h4 style="margin: 25px 0 15px; color: #667eea;">
                    <i class="fas fa-user-tie"></i> Datos del Contacto
                </h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Contacto</label>
                        <input type="text" name="contacto_nombre" id="contacto_nombre"
                               placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label>Teléfono del Contacto</label>
                        <input type="text" name="contacto_telefono" id="contacto_telefono"
                               maxlength="15"
                               placeholder="Ej: 987654321">
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
                <button type="button" class="btn btn-secondary" onclick="cerrarModalProveedor()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/proveedores.js"></script>