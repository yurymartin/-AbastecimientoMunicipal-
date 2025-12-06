<div class="page-content">
    <div id="alertaPerfil"></div>
    
    <!-- Header del Perfil -->
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-info">
            <h2 id="nombreCompleto">Cargando...</h2>
            <p><i class="fas fa-user-tag"></i> <span id="rolUsuario">-</span></p>
            <p><i class="fas fa-envelope"></i> <span id="emailUsuario">-</span></p>
            <p><i class="fas fa-id-card"></i> DNI: <span id="dniUsuario">-</span></p>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-details">
                <h3>Pedidos Solicitados</h3>
                <p class="stat-number" id="statPedidos">0</p>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-details">
                <h3>Gastos Registrados</h3>
                <p class="stat-number" id="statGastos">0</p>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-details">
                <h3>Productos Registrados</h3>
                <p class="stat-number" id="statProductos">0</p>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-history"></i></div>
            <div class="stat-details">
                <h3>Total de Acciones</h3>
                <p class="stat-number" id="statAcciones">0</p>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs-perfil">
        <button class="tab-perfil active" onclick="cambiarTabPerfil('info')">
            <i class="fas fa-user"></i> Información Personal
        </button>
        <button class="tab-perfil" onclick="cambiarTabPerfil('password')">
            <i class="fas fa-key"></i> Cambiar Contraseña
        </button>
        <button class="tab-perfil" onclick="cambiarTabPerfil('actividad')">
            <i class="fas fa-history"></i> Actividad Reciente
        </button>
        <button class="tab-perfil" onclick="cambiarTabPerfil('sesiones')">
            <i class="fas fa-desktop"></i> Mis Sesiones
        </button>
    </div>
    
    <!-- Tab: Información Personal -->
    <div id="tab-info" class="tab-content-perfil active">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Editar Información Personal</h3>
            </div>
            <div class="card-body" style="padding: 28px;">
                <form id="formPerfil">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombres <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="nombres" id="nombres" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="apellidos" id="apellidos" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>DNI <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="dni" id="dni" pattern="[0-9]{8}" maxlength="8" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span style="color: var(--danger);">*</span></label>
                            <input type="email" name="email" id="email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="username" disabled>
                        <small class="form-help">El username no se puede modificar</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Rol</label>
                        <input type="text" id="rol" disabled>
                        <small class="form-help">Solo un administrador puede cambiar tu rol</small>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tab: Cambiar Contraseña -->
    <div id="tab-password" class="tab-content-perfil">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
            </div>
            <div class="card-body" style="padding: 28px;">
                <form id="formPassword">
                    <div class="form-group">
                        <label>Contraseña Actual <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password_actual" id="password_actual" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nueva Contraseña <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password_nueva" id="password_nueva" minlength="6" required>
                        <small class="form-help">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password_confirmar" id="password_confirmar" minlength="6" required>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px; background: #eff6ff; border: 1px solid #3b82f6;">
            <div class="card-body">
                <h4 style="color: #1e40af; margin-top: 0;">
                    <i class="fas fa-info-circle"></i> Consejos de Seguridad
                </h4>
                <ul style="color: #1e40af; margin: 0;">
                    <li>Use una contraseña segura de al menos 6 caracteres</li>
                    <li>Combine letras mayúsculas, minúsculas y números</li>
                    <li>No comparta su contraseña con nadie</li>
                    <li>Cambie su contraseña periódicamente</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Tab: Actividad Reciente -->
    <div id="tab-actividad" class="tab-content-perfil">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                <select id="limiteActividad" class="filter-select" style="width: auto;" onchange="cargarActividad()">
                    <option value="10">10 registros</option>
                    <option value="50" selected>50 registros</option>
                    <option value="100">100 registros</option>
                    <option value="500">500 registros</option>
                </select>
            </div>
            <div class="card-body" id="actividadContainer">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando actividad...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab: Mis Sesiones -->
    <div id="tab-sesiones" class="tab-content-perfil">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-desktop"></i> Mis Sesiones</h3>
            </div>
            <div class="card-body" id="sesionesContainer">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando sesiones...</p>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px; background: #eff6ff; border: 1px solid #3b82f6;">
            <div class="card-body">
                <h4 style="color: #1e40af; margin-top: 0;">
                    <i class="fas fa-shield-alt"></i> Seguridad de tu Cuenta
                </h4>
                <p style="color: #1e40af; margin: 0;">
                    Si observas sesiones que no reconoces o actividad sospechosa, 
                    contacta inmediatamente al administrador del sistema o cambia tu contraseña.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/perfil.js"></script>