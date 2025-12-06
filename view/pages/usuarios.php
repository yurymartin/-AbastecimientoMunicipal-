<?php
require_once __DIR__ . '/../../business/bUsuario.php';
$usuarioBusiness = new bUsuario();

$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 10;
$totalUsuarios = $usuarioBusiness->contarUsuariosB();
$totalPaginas = ceil($totalUsuarios / $porPagina);

$usuarios = $usuarioBusiness->listarUsuB($paginaActual, $porPagina);
$roles = $usuarioBusiness->obtenerRoles();

$inicio = ($paginaActual - 1) * $porPagina + 1;
$fin = min($paginaActual * $porPagina, $totalUsuarios);
?>

<div class="page-content">
    <div class="page-header">
        <div>
            <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
            <p>Administra los usuarios del sistema</p>
        </div>
        <button class="btn btn-primary" onclick="abrirModalNuevo()">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </button>
    </div>
    
    <div id="mensajeAlerta" style="display: none;" class="alert">
        <i class="fas fa-check-circle"></i>
        <span id="mensajeTexto"></span>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="buscarUsuario" placeholder="Buscar usuario...">
                </div>
                <select class="filter-select" id="filtroRol" onchange="filtrarPorRol()">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['idRol']; ?>">
                            <?php echo htmlspecialchars($rol['nombreRol']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <p>No hay usuarios registrados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr data-rol="<?php echo $usuario['rolId']; ?>">
                                    <td>
                                        <div class="user-cell">
                                            <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['dni']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($usuario['nombreRol']); ?></span></td>
                                    <td>
                                        <?php if ($usuario['estado'] == 'Activo'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-icon btn-warning" 
                                                    onclick="editarUsuario(<?php echo $usuario['idUsuario']; ?>)"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-danger" 
                                                    onclick="confirmarEliminar(<?php echo $usuario['idUsuario']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPaginas > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando <?php echo $inicio; ?> - <?php echo $fin; ?> de <?php echo $totalUsuarios; ?> usuarios
            </div>
            <div class="pagination" id="pagination">
                <button onclick="cambiarPagina(1)" <?php echo $paginaActual == 1 ? 'disabled' : ''; ?>>
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button onclick="cambiarPagina(<?php echo $paginaActual - 1; ?>)" <?php echo $paginaActual == 1 ? 'disabled' : ''; ?>>
                    <i class="fas fa-angle-left"></i>
                </button>
                
                <?php
                $inicio_pag = max(1, $paginaActual - 2);
                $fin_pag = min($totalPaginas, $paginaActual + 2);
                
                for ($i = $inicio_pag; $i <= $fin_pag; $i++): ?>
                    <button onclick="cambiarPagina(<?php echo $i; ?>)" 
                            class="<?php echo $i == $paginaActual ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>
                
                <button onclick="cambiarPagina(<?php echo $paginaActual + 1; ?>)" <?php echo $paginaActual == $totalPaginas ? 'disabled' : ''; ?>>
                    <i class="fas fa-angle-right"></i>
                </button>
                <button onclick="cambiarPagina(<?php echo $totalPaginas; ?>)" <?php echo $paginaActual == $totalPaginas ? 'disabled' : ''; ?>>
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Crear/Editar Usuario -->
<div id="modalUsuario" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Usuario</h3>
            <button class="modal-close" onclick="cerrarModal('modalUsuario')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formUsuario" onsubmit="guardarUsuario(event)">
            <div class="modal-body">
                <input type="hidden" id="idUsuario" name="idUsuario">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Usuario *</label>
                        <input type="text" name="username" id="username" required 
                               pattern="[a-zA-Z0-9_]+" 
                               title="Solo letras, números y guión bajo">
                    </div>
                    <div class="form-group">
                        <label id="labelPassword">Contraseña *</label>
                        <input type="password" name="password" id="password" 
                               minlength="6">
                        <small class="form-help" id="helpPassword">Mínimo 6 caracteres</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" name="nombres" id="nombres" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>DNI *</label>
                        <input type="text" name="dni" id="dni" maxlength="8" 
                               pattern="[0-9]{8}" 
                               title="Debe tener 8 dígitos" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Rol *</label>
                        <select name="rolId" id="rolId" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['idRol']; ?>">
                                    <?php echo htmlspecialchars($rol['nombreRol']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="estado" id="estado" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalUsuario')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div id="modalConfirmar" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            <button class="modal-close" onclick="cerrarModal('modalConfirmar')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro de eliminar al usuario <strong id="usuarioEliminar"></strong>?</p>
            <p class="text-muted">El usuario no podrá acceder al sistema.</p>
            <input type="hidden" id="idUsuarioEliminar">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalConfirmar()">
                Cancelar
            </button>
            <button type="button" class="btn btn-danger" onclick="eliminarUsuario()">
                <i class="fas fa-trash"></i> Sí, Eliminar
            </button>
        </div>
    </div>
</div>

<script src="assets/js/usuarios.js"></script>