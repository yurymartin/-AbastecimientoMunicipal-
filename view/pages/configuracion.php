<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
            <p>Gestión de parámetros y configuraciones generales</p>
        </div>
    </div>
    
    <div id="alertaConfiguracion"></div>
    
    <!-- Estadísticas del Sistema -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-details">
                <h3>Usuarios</h3>
                <p class="stat-number" id="statUsuarios">0</p>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div class="stat-details">
                <h3>Proveedores</h3>
                <p class="stat-number" id="statProveedores">0</p>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-details">
                <h3>Productos</h3>
                <p class="stat-number" id="statProductos">0</p>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-details">
                <h3>Pedidos</h3>
                <p class="stat-number" id="statPedidos">0</p>
            </div>
        </div>
    </div>
    
    <!-- Info adicional -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <div class="config-info-box" style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <strong><i class="fas fa-database"></i> Base de Datos:</strong>
                    <span id="statDBSize">0 MB</span>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <strong><i class="fas fa-history"></i> Auditoría:</strong>
                    <span id="statAuditoria">0 registros</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pestañas de Configuración -->
    <div class="card config-card">
        <div class="card-header">
            <div class="tabs-config">
                <button class="tab-btn active" onclick="cambiarTab('categorias')">
                    <i class="fas fa-tags"></i> Categorías
                </button>
                <button class="tab-btn" onclick="cambiarTab('roles')">
                    <i class="fas fa-user-tag"></i> Roles
                </button>
                <button class="tab-btn" onclick="cambiarTab('estados')">
                    <i class="fas fa-list"></i> Estados
                </button>
                <button class="tab-btn" onclick="cambiarTab('sistema')">
                    <i class="fas fa-tools"></i> Sistema
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- TAB: Categorías -->
            <div id="tab-categorias" class="tab-content config-tab-content active">
                <div class="config-table-header">
                    <h3><i class="fas fa-tags"></i> Categorías de Productos</h3>
                    <button class="btn btn-primary" onclick="abrirModalCategoria()">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Productos</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCategorias">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Cargando categorías...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TAB: Roles -->
            <div id="tab-roles" class="tab-content config-tab-content">
                <div class="config-table-header">
                    <h3><i class="fas fa-user-tag"></i> Roles de Usuario</h3>
                    <button class="btn btn-primary" onclick="abrirModalRol()">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Usuarios</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRoles">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Cargando roles...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TAB: Estados -->
            <div id="tab-estados" class="tab-content config-tab-content">
                <h3 style="margin-bottom: 10px;"><i class="fas fa-list"></i> Estados de Pedidos</h3>
                <div class="config-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Los estados de pedidos son predefinidos y solo pueden editarse. No se pueden crear ni eliminar.</span>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Pedidos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyEstados">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Cargando estados...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TAB: Sistema -->
            <div id="tab-sistema" class="tab-content config-tab-content">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-tools"></i> Herramientas del Sistema</h3>
                
                <div class="config-tools-grid">
                    <!-- Respaldo de BD -->
                    <div class="config-tool-card">
                        <div class="config-tool-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                            <i class="fas fa-database"></i>
                        </div>
                        <h4>Respaldo de Base de Datos</h4>
                        <p>Generar copia de seguridad completa de la base de datos en formato SQL</p>
                        <button onclick="generarRespaldo()" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-download"></i> Descargar Respaldo
                        </button>
                        <small>💡 Recomendación: Realizar respaldo semanal</small>
                    </div>
                    
                    <!-- Auditoría -->
                    <div class="config-tool-card">
                        <div class="config-tool-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Registro de Auditoría</h4>
                        <p>Ver historial completo de operaciones del sistema con filtros avanzados</p>
                        <button onclick="verAuditoria()" class="btn btn-success" style="width: 100%;">
                            <i class="fas fa-eye"></i> Ver Auditoría
                        </button>
                    </div>
                    
                    <!-- Sesiones Activas -->
                    <div class="config-tool-card">
                        <div class="config-tool-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4>Sesiones Activas</h4>
                        <p>Administrar sesiones activas de usuarios conectados al sistema</p>
                        <button onclick="verSesiones()" class="btn btn-warning" style="width: 100%;">
                            <i class="fas fa-eye"></i> Ver Sesiones (<span id="totalSesiones">0</span>)
                        </button>
                    </div>
                    
                    <!-- Limpiar Auditoría -->
                    <div class="config-tool-card">
                        <div class="config-tool-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                            <i class="fas fa-broom"></i>
                        </div>
                        <h4>Limpiar Auditoría Antigua</h4>
                        <p>Eliminar registros de auditoría mayores a 90 días para optimizar espacio</p>
                        <button onclick="confirmarLimpiarAuditoria()" class="btn btn-danger" style="width: 100%;">
                            <i class="fas fa-trash-alt"></i> Limpiar Registros
                        </button>
                    </div>
                </div>
                
                <div class="config-alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Importante:</strong> Las operaciones de sistema son sensibles. Asegúrese de tener respaldos antes de realizar cambios críticos.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Categoría -->
<div id="modalCategoria" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="tituloCategoria"><i class="fas fa-tags"></i> Nueva Categoría</h3>
            <button class="modal-close" onclick="cerrarModalCategoria()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formCategoria">
            <div class="modal-body">
                <input type="hidden" id="idCategoria" name="idCategoria">
                
                <div class="form-group">
                    <label>Nombre <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nombre" id="nombreCategoria" required
                           placeholder="Ej: Materiales de Construcción">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcionCategoria" rows="3"
                              placeholder="Descripción opcional de la categoría"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCategoria()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rol -->
<div id="modalRol" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="tituloRol"><i class="fas fa-user-tag"></i> Nuevo Rol</h3>
            <button class="modal-close" onclick="cerrarModalRol()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formRol">
            <div class="modal-body">
                <input type="hidden" id="idRol" name="idRol">
                
                <div class="form-group">
                    <label>Nombre del Rol <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nombreRol" id="nombreRol" required
                           placeholder="Ej: Supervisor">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcionRol" id="descripcionRol" rows="3"
                              placeholder="Descripción de permisos y responsabilidades"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalRol()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Estado -->
<div id="modalEstado" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Estado de Pedido</h3>
            <button class="modal-close" onclick="cerrarModalEstado()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEstado">
            <div class="modal-body">
                <input type="hidden" id="idEstado" name="idEstado">
                
                <div class="form-group">
                    <label>Nombre <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nombreEstado" id="nombreEstado" required>
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcionEstado" id="descripcionEstado" rows="3"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEstado()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Auditoría -->
<div id="modalAuditoria" class="modal">
    <div class="modal-content" style="max-width: 1200px;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Registro de Auditoría</h3>
            <button class="modal-close" onclick="cerrarModalAuditoria()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Filtros -->
            <div class="config-filtros-grid">
                <div class="form-group">
                    <label>Tabla</label>
                    <select id="filtroTabla" class="form-control">
                        <option value="">Todas</option>
                        <option value="usuarios">Usuarios</option>
                        <option value="proveedores">Proveedores</option>
                        <option value="productos">Productos</option>
                        <option value="pedidos">Pedidos</option>
                        <option value="gastos">Gastos</option>
                        <option value="categorias">Categorías</option>
                        <option value="roles">Roles</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Acción</label>
                    <select id="filtroAccion" class="form-control">
                        <option value="">Todas</option>
                        <option value="INSERT">INSERT</option>
                        <option value="UPDATE">UPDATE</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Desde</label>
                    <input type="date" id="filtroFechaDesde" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Hasta</label>
                    <input type="date" id="filtroFechaHasta" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Límite</label>
                    <select id="filtroLimite" class="form-control">
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </div>
                
                <div style="display: flex; align-items: end;">
                    <button onclick="aplicarFiltrosAuditoria()" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
            
            <!-- Tabla -->
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="data-table">
                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>Acción</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAuditoria">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando auditoría...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalAuditoria()">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Modal Sesiones -->
<div id="modalSesiones" class="modal">
    <div class="modal-content" style="max-width: 1200px;">
        <div class="modal-header">
            <h3><i class="fas fa-users-cog"></i> Sesiones Activas</h3>
            <button class="modal-close" onclick="cerrarModalSesiones()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="config-alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Cerrar una sesión forzará al usuario a iniciar sesión nuevamente. Útil para desconectar usuarios inactivos o actividad sospechosa.</span>
            </div>
            
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="data-table">
                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                        <tr>
                            <th>Usuario</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Navegador</th>
                            <th>Inicio</th>
                            <th>Expira</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySesiones">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Cargando sesiones...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalSesiones()">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script src="assets/js/configuracion.js"></script>