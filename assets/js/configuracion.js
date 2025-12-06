// ===== VARIABLES GLOBALES =====
let categoriasData = [];
let rolesData = [];
let estadosData = [];
let auditoriaData = [];
let sesionesData = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    initEventListeners();
});

// ===== EVENT LISTENERS =====
function initEventListeners() {
    // Formularios
    const formCategoria = document.getElementById('formCategoria');
    const formRol = document.getElementById('formRol');
    const formEstado = document.getElementById('formEstado');
    
    if (formCategoria) formCategoria.addEventListener('submit', guardarCategoria);
    if (formRol) formRol.addEventListener('submit', guardarRol);
    if (formEstado) formEstado.addEventListener('submit', guardarEstado);
}

// ===== CARGAR DATOS INICIALES =====
async function cargarDatosIniciales() {
    try {
        mostrarLoading('Cargando configuración...');
        
        const response = await fetch('controller/cConfiguracion.php?action=cargarDatos');
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            // Guardar datos
            categoriasData = data.data.categorias;
            rolesData = data.data.roles;
            estadosData = data.data.estados;
            
            // Actualizar estadísticas
            actualizarEstadisticas(data.data.estadisticas);
            
            // Renderizar tablas
            renderizarCategorias();
            renderizarRoles();
            renderizarEstados();
        } else {
            mostrarAlerta('Error al cargar datos: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar configuración', 'danger');
    }
}

// ===== ACTUALIZAR ESTADÍSTICAS =====
function actualizarEstadisticas(stats) {
    document.getElementById('statUsuarios').textContent = stats.usuarios || 0;
    document.getElementById('statProveedores').textContent = stats.proveedores || 0;
    document.getElementById('statProductos').textContent = stats.productos || 0;
    document.getElementById('statPedidos').textContent = stats.pedidos || 0;
    document.getElementById('statDBSize').textContent = (stats.db_size || 0) + ' MB';
    document.getElementById('statAuditoria').textContent = formatearNumero(stats.auditoria || 0) + ' registros';
}

// ===== CAMBIAR TABS =====
function cambiarTab(tab) {
    // Ocultar todos los tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    
    // Desactivar botones
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(b => b.classList.remove('active'));
    
    // Activar tab seleccionado
    const tabContent = document.getElementById('tab-' + tab);
    const tabButton = event.target.closest('.tab-btn');
    
    if (tabContent) tabContent.classList.add('active');
    if (tabButton) tabButton.classList.add('active');
}

// ===== CATEGORÍAS =====

function renderizarCategorias() {
    const tbody = document.getElementById('tbodyCategorias');
    
    if (!tbody) return;
    
    if (categoriasData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No hay categorías registradas</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = categoriasData.map(cat => `
        <tr>
            <td>${cat.idCategoria}</td>
            <td><strong>${escapeHtml(cat.nombre)}</strong></td>
            <td>${escapeHtml(cat.descripcion || '-')}</td>
            <td style="text-align: center;">
                <span class="badge badge-info">${cat.total_productos || 0}</span>
            </td>
            <td>${formatearFecha(cat.fecha_creacion)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-warning" 
                            onclick='editarCategoria(${JSON.stringify(cat)})'
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-danger" 
                            onclick="eliminarCategoria(${cat.idCategoria}, '${escapeHtml(cat.nombre)}')"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalCategoria(id = null) {
    const modal = document.getElementById('modalCategoria');
    const titulo = document.getElementById('tituloCategoria');
    const form = document.getElementById('formCategoria');
    
    if (!modal || !form) return;
    
    // Resetear formulario
    form.reset();
    document.getElementById('idCategoria').value = '';
    
    titulo.innerHTML = id ? '<i class="fas fa-edit"></i> Editar Categoría' : '<i class="fas fa-plus"></i> Nueva Categoría';
    
    mostrarModal('modalCategoria');
}

function editarCategoria(categoria) {
    document.getElementById('idCategoria').value = categoria.idCategoria;
    document.getElementById('nombreCategoria').value = categoria.nombre;
    document.getElementById('descripcionCategoria').value = categoria.descripcion || '';
    document.getElementById('tituloCategoria').innerHTML = '<i class="fas fa-edit"></i> Editar Categoría';
    
    mostrarModal('modalCategoria');
}

async function guardarCategoria(event) {
    event.preventDefault();
    
    const form = document.getElementById('formCategoria');
    const formData = new FormData(form);
    formData.append('action', 'guardarCategoria');
    
    try {
        mostrarLoading('Guardando categoría...');
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalCategoria();
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar', 'danger');
    }
}

function eliminarCategoria(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la categoría "${nombre}"?\n\nNOTA: No se puede eliminar si tiene productos asociados.`)) {
        eliminarCategoriaConfirmado(id);
    }
}

async function eliminarCategoriaConfirmado(id) {
    try {
        mostrarLoading('Eliminando categoría...');
        
        const formData = new FormData();
        formData.append('action', 'eliminarCategoria');
        formData.append('id', id);
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al eliminar', 'danger');
    }
}

function cerrarModalCategoria() {
    cerrarModal('modalCategoria');
    document.getElementById('formCategoria').reset();
}

// ===== ROLES =====

function renderizarRoles() {
    const tbody = document.getElementById('tbodyRoles');
    
    if (!tbody) return;
    
    if (rolesData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-user-tag"></i>
                        <p>No hay roles registrados</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = rolesData.map(rol => `
        <tr>
            <td>${rol.idRol}</td>
            <td><strong>${escapeHtml(rol.nombreRol)}</strong></td>
            <td>${escapeHtml(rol.descripcion || '-')}</td>
            <td style="text-align: center;">
                <span class="badge badge-info">${rol.total_usuarios || 0}</span>
            </td>
            <td>${formatearFecha(rol.fecha_creacion)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-warning" 
                            onclick='editarRol(${JSON.stringify(rol)})'
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-danger" 
                            onclick="eliminarRol(${rol.idRol}, '${escapeHtml(rol.nombreRol)}')"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalRol(id = null) {
    const modal = document.getElementById('modalRol');
    const titulo = document.getElementById('tituloRol');
    const form = document.getElementById('formRol');
    
    if (!modal || !form) return;
    
    // Resetear formulario
    form.reset();
    document.getElementById('idRol').value = '';
    
    titulo.innerHTML = id ? '<i class="fas fa-edit"></i> Editar Rol' : '<i class="fas fa-plus"></i> Nuevo Rol';
    
    mostrarModal('modalRol');
}

function editarRol(rol) {
    document.getElementById('idRol').value = rol.idRol;
    document.getElementById('nombreRol').value = rol.nombreRol;
    document.getElementById('descripcionRol').value = rol.descripcion || '';
    document.getElementById('tituloRol').innerHTML = '<i class="fas fa-edit"></i> Editar Rol';
    
    mostrarModal('modalRol');
}

async function guardarRol(event) {
    event.preventDefault();
    
    const form = document.getElementById('formRol');
    const formData = new FormData(form);
    formData.append('action', 'guardarRol');
    
    try {
        mostrarLoading('Guardando rol...');
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalRol();
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar', 'danger');
    }
}

function eliminarRol(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el rol "${nombre}"?\n\nNOTA: No se puede eliminar si tiene usuarios asignados.`)) {
        eliminarRolConfirmado(id);
    }
}

async function eliminarRolConfirmado(id) {
    try {
        mostrarLoading('Eliminando rol...');
        
        const formData = new FormData();
        formData.append('action', 'eliminarRol');
        formData.append('id', id);
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al eliminar', 'danger');
    }
}

function cerrarModalRol() {
    cerrarModal('modalRol');
    document.getElementById('formRol').reset();
}

// ===== ESTADOS =====

function renderizarEstados() {
    const tbody = document.getElementById('tbodyEstados');
    
    if (!tbody) return;
    
    if (estadosData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-list"></i>
                        <p>No hay estados registrados</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = estadosData.map(est => `
        <tr>
            <td>${est.idEstado}</td>
            <td><strong>${escapeHtml(est.nombre)}</strong></td>
            <td>${escapeHtml(est.descripcion || '-')}</td>
            <td style="text-align: center;">
                <span class="badge badge-info">${est.total_pedidos || 0}</span>
            </td>
            <td>
                <button class="btn-icon btn-warning" 
                        onclick='editarEstado(${JSON.stringify(est)})'
                        title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function editarEstado(estado) {
    document.getElementById('idEstado').value = estado.idEstado;
    document.getElementById('nombreEstado').value = estado.nombre;
    document.getElementById('descripcionEstado').value = estado.descripcion || '';
    
    mostrarModal('modalEstado');
}

async function guardarEstado(event) {
    event.preventDefault();
    
    const form = document.getElementById('formEstado');
    const formData = new FormData(form);
    formData.append('action', 'guardarEstado');
    
    try {
        mostrarLoading('Guardando estado...');
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalEstado();
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar', 'danger');
    }
}

function cerrarModalEstado() {
    cerrarModal('modalEstado');
    document.getElementById('formEstado').reset();
}

// ===== AUDITORÍA =====

async function verAuditoria() {
    mostrarModal('modalAuditoria');
    await cargarAuditoria();
}

async function cargarAuditoria() {
    const tbody = document.getElementById('tbodyAuditoria');
    
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando auditoría...</p>
                </div>
            </td>
        </tr>
    `;
    
    try {
        const tabla = document.getElementById('filtroTabla').value;
        const accion = document.getElementById('filtroAccion').value;
        const fechaDesde = document.getElementById('filtroFechaDesde').value;
        const fechaHasta = document.getElementById('filtroFechaHasta').value;
        const limite = document.getElementById('filtroLimite').value;
        
        let url = `controller/cConfiguracion.php?action=listarAuditoria&limite=${limite}`;
        if (tabla) url += `&tabla=${tabla}`;
        if (accion) url += `&accion=${accion}`;
        if (fechaDesde) url += `&fecha_desde=${fechaDesde}`;
        if (fechaHasta) url += `&fecha_hasta=${fechaHasta}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            auditoriaData = data.data;
            renderizarAuditoria();
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>${data.message}</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar auditoría</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function renderizarAuditoria() {
    const tbody = document.getElementById('tbodyAuditoria');
    
    if (!tbody) return;
    
    if (auditoriaData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No hay registros de auditoría</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = auditoriaData.map(reg => {
        let claseAccion = 'secondary';
        if (reg.accion === 'INSERT') claseAccion = 'success';
        if (reg.accion === 'UPDATE') claseAccion = 'warning';
        if (reg.accion === 'DELETE') claseAccion = 'danger';
        
        const usuario = reg.nombres ? `${reg.nombres} ${reg.apellidos}` : 'Sistema';
        
        return `
            <tr>
                <td>${reg.idAuditoria}</td>
                <td><small>${formatearFechaHora(reg.fecha_accion)}</small></td>
                <td>${escapeHtml(usuario)}</td>
                <td>
                    <span class="badge badge-info">${escapeHtml(reg.tabla_afectada)}</span>
                </td>
                <td style="text-align: center;">${reg.registroId}</td>
                <td>
                    <span class="badge badge-${claseAccion}">${reg.accion}</span>
                </td>
                <td><code style="font-size: 11px;">${escapeHtml(reg.ip_address)}</code></td>
            </tr>
        `;
    }).join('');
}

function aplicarFiltrosAuditoria() {
    cargarAuditoria();
}

function confirmarLimpiarAuditoria() {
    if (confirm('¿Está seguro de eliminar los registros de auditoría mayores a 90 días?\n\nEsta acción NO se puede deshacer.')) {
        limpiarAuditoria();
    }
}

async function limpiarAuditoria() {
    try {
        mostrarLoading('Limpiando auditoría...');
        
        const formData = new FormData();
        formData.append('action', 'limpiarAuditoria');
        formData.append('dias', 90);
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarDatosIniciales();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error al limpiar auditoría', 'danger');
    }
}

function cerrarModalAuditoria() {
    cerrarModal('modalAuditoria');
}

// ===== SESIONES =====

async function verSesiones() {
    mostrarModal('modalSesiones');
    await cargarSesiones();
}

async function cargarSesiones() {
    const tbody = document.getElementById('tbodySesiones');
    
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando sesiones...</p>
                </div>
            </td>
        </tr>
    `;
    
    try {
        const response = await fetch('controller/cConfiguracion.php?action=listarSesiones');
        const data = await response.json();
        
        if (data.success) {
            sesionesData = data.data;
            document.getElementById('totalSesiones').textContent = data.total || 0;
            renderizarSesiones();
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>${data.message}</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar sesiones</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

function renderizarSesiones() {
    const tbody = document.getElementById('tbodySesiones');
    
    if (!tbody) return;
    
    if (sesionesData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <p>No hay sesiones activas</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = sesionesData.map(sesion => {
        const navegador = detectarNavegador(sesion.user_agent);
        const expiracion = calcularExpiracion(sesion.fecha_expiracion);
        
        return `
            <tr>
                <td><strong>${escapeHtml(sesion.nombres + ' ' + sesion.apellidos)}</strong></td>
                <td>
                    <span class="badge badge-info">${escapeHtml(sesion.username)}</span>
                </td>
                <td><code style="font-size: 11px;">${escapeHtml(sesion.ip_address)}</code></td>
                <td><small>${navegador}</small></td>
                <td><small>${formatearFechaHora(sesion.fecha_inicio)}</small></td>
                <td>${expiracion}</td>
                <td>
                    <button class="btn-icon btn-danger" 
                            onclick="cerrarSesionUsuario(${sesion.idSesion}, '${escapeHtml(sesion.username)}')"
                            title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function detectarNavegador(userAgent) {
    if (userAgent.includes('Chrome')) return '<i class="fab fa-chrome"></i> Chrome';
    if (userAgent.includes('Firefox')) return '<i class="fab fa-firefox"></i> Firefox';
    if (userAgent.includes('Safari')) return '<i class="fab fa-safari"></i> Safari';
    if (userAgent.includes('Edge')) return '<i class="fab fa-edge"></i> Edge';
    return '<i class="fas fa-browser"></i> Otro';
}

function calcularExpiracion(fechaExpiracion) {
    const expiracion = new Date(fechaExpiracion).getTime();
    const ahora = new Date().getTime();
    
    if (expiracion < ahora) {
        return '<span style="color: #ef4444;">Expirada</span>';
    }
    
    const diff = expiracion - ahora;
    const horas = Math.floor(diff / (1000 * 60 * 60));
    const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return `<span style="color: #10b981;">En ${horas}h ${minutos}m</span>`;
}

function cerrarSesionUsuario(idSesion, username) {
    if (confirm(`¿Está seguro de cerrar la sesión de ${username}?\n\nEl usuario deberá iniciar sesión nuevamente.`)) {
        cerrarSesionConfirmado(idSesion);
    }
}

async function cerrarSesionConfirmado(idSesion) {
    try {
        mostrarLoading('Cerrando sesión...');
        
        const formData = new FormData();
        formData.append('action', 'cerrarSesion');
        formData.append('id', idSesion);
        
        const response = await fetch('controller/cConfiguracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarSesiones();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error al cerrar sesión', 'danger');
    }
}

function cerrarModalSesiones() {
    cerrarModal('modalSesiones');
}

// ===== RESPALDO =====

function generarRespaldo() {
    if (confirm('¿Desea generar un respaldo completo de la base de datos?\n\nEste proceso puede tomar unos momentos.')) {
        mostrarLoading('Generando respaldo...');
        
        // Crear enlace de descarga
        window.location.href = 'controller/cConfiguracion.php?action=generarRespaldo';
        
        // Ocultar loading después de 2 segundos
        setTimeout(() => {
            ocultarLoading();
            mostrarAlerta('Respaldo generado exitosamente. Revise su carpeta de descargas.', 'success');
        }, 2000);
    }
}

// ===== ALERTAS =====

function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaConfiguracion');
    if (!alertaDiv) return;
    
    const iconos = {
        success: 'check-circle',
        danger: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    alertaDiv.innerHTML = `
        <div class="alert alert-${tipo}" style="animation: slideInDown 0.3s ease;">
            <i class="fas fa-${iconos[tipo]}"></i>
            ${mensaje}
            <button class="btn-close" onclick="cerrarAlertaConfiguracion()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        cerrarAlertaConfiguracion();
    }, 5000);
}

function cerrarAlertaConfiguracion() {
    const alertaDiv = document.getElementById('alertaConfiguracion');
    if (alertaDiv) {
        alertaDiv.innerHTML = '';
    }
}

// ===== UTILIDADES =====

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-PE').format(numero);
}