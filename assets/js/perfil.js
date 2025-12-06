// ===== VARIABLES GLOBALES =====
let perfilData = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    initEventListeners();
});

function initEventListeners() {
    const formPerfil = document.getElementById('formPerfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', guardarPerfil);
    }
    
    const formPassword = document.getElementById('formPassword');
    if (formPassword) {
        formPassword.addEventListener('submit', cambiarPassword);
    }
}

// ===== CARGAR DATOS INICIALES =====
async function cargarDatosIniciales() {
    await Promise.all([
        cargarPerfil(),
        cargarEstadisticas()
    ]);
}

// ===== CARGAR PERFIL =====
async function cargarPerfil() {
    try {
        const response = await fetch('controller/cPerfil.php?action=obtener');
        const data = await response.json();
        
        if (data.success) {
            perfilData = data.data;
            llenarFormularioPerfil(perfilData);
            actualizarHeaderPerfil(perfilData);
        } else {
            mostrarAlerta('Error al cargar perfil: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al cargar perfil', 'danger');
    }
}

// ===== LLENAR FORMULARIO =====
function llenarFormularioPerfil(perfil) {
    document.getElementById('nombres').value = perfil.nombres || '';
    document.getElementById('apellidos').value = perfil.apellidos || '';
    document.getElementById('dni').value = perfil.dni || '';
    document.getElementById('email').value = perfil.email || '';
    document.getElementById('username').value = perfil.username || '';
    document.getElementById('rol').value = perfil.nombreRol || '';
}

// ===== ACTUALIZAR HEADER =====
function actualizarHeaderPerfil(perfil) {
    document.getElementById('nombreCompleto').textContent = 
        `${perfil.nombres} ${perfil.apellidos}`;
    document.getElementById('rolUsuario').textContent = perfil.nombreRol;
    document.getElementById('emailUsuario').textContent = perfil.email;
    document.getElementById('dniUsuario').textContent = perfil.dni;
}

// ===== CARGAR ESTADÍSTICAS =====
async function cargarEstadisticas() {
    try {
        const response = await fetch('controller/cPerfil.php?action=estadisticas');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('statPedidos').textContent = data.data.pedidos_solicitados;
            document.getElementById('statGastos').textContent = data.data.gastos_registrados;
            document.getElementById('statProductos').textContent = data.data.productos_registrados;
            document.getElementById('statAcciones').textContent = 
                formatearNumero(data.data.total_acciones, 0);
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// ===== GUARDAR PERFIL =====
async function guardarPerfil(event) {
    event.preventDefault();
    
    const form = document.getElementById('formPerfil');
    const formData = new FormData(form);
    formData.append('action', 'actualizar');
    
    try {
        mostrarLoading('Guardando cambios...');
        
        const response = await fetch('controller/cPerfil.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            const nombres = formData.get('nombres');
            const apellidos = formData.get('apellidos');
            const email = formData.get('email');
            const dni = formData.get('dni');
            
            document.getElementById('nombreCompleto').textContent = `${nombres} ${apellidos}`;
            document.getElementById('emailUsuario').textContent = email;
            document.getElementById('dniUsuario').textContent = dni;
            
            const userNameElements = document.querySelectorAll('.user-name');
            userNameElements.forEach(el => {
                el.textContent = `${nombres} ${apellidos}`;
            });
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar', 'danger');
    }
}

// ===== CAMBIAR CONTRASEÑA =====
async function cambiarPassword(event) {
    event.preventDefault();
    
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmar = document.getElementById('password_confirmar').value;
    
    if (passwordNueva !== passwordConfirmar) {
        mostrarAlerta('Las contraseñas no coinciden', 'warning');
        return;
    }
    
    const form = document.getElementById('formPassword');
    const formData = new FormData(form);
    formData.append('action', 'cambiar_password');
    
    try {
        mostrarLoading('Cambiando contraseña...');
        
        const response = await fetch('controller/cPerfil.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            form.reset();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cambiar contraseña', 'danger');
    }
}

// ===== CAMBIAR TAB =====
function cambiarTabPerfil(tab) {
    document.querySelectorAll('.tab-perfil').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content-perfil').forEach(content => content.classList.remove('active'));
    
    event.target.closest('.tab-perfil').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
    
    if (tab === 'actividad') {
        cargarActividad();
    } else if (tab === 'sesiones') {
        cargarSesiones();
    }
}

// ===== CARGAR ACTIVIDAD =====
async function cargarActividad() {
    const limite = document.getElementById('limiteActividad').value;
    const container = document.getElementById('actividadContainer');
    
    try {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando...</p></div>';
        
        const response = await fetch(`controller/cPerfil.php?action=actividad&limite=${limite}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarActividad(data.data);
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-history"></i><p>No hay actividad registrada</p></div>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error al cargar actividad</p></div>';
    }
}

function renderizarActividad(actividad) {
    const container = document.getElementById('actividadContainer');
    
    let html = '<div class="timeline">';
    
    actividad.forEach(act => {
        let iconClass = 'fa-circle';
        let colorClass = 'secondary';
        
        if (act.accion === 'INSERT') {
            iconClass = 'fa-plus';
            colorClass = 'success';
        } else if (act.accion === 'UPDATE') {
            iconClass = 'fa-edit';
            colorClass = 'warning';
        } else if (act.accion === 'DELETE') {
            iconClass = 'fa-trash';
            colorClass = 'danger';
        }
        
        html += `
            <div class="timeline-item">
                <div class="timeline-marker">
                    <i class="fas ${iconClass}" style="color: var(--${colorClass});"></i>
                </div>
                <div class="timeline-content">
                    <strong>${act.accion_texto}</strong> en 
                    <span class="badge badge-info">${escapeHtml(act.tabla_afectada)}</span>
                    <small style="display: block; color: #6b7280; margin-top: 5px;">
                        <i class="fas fa-clock"></i> 
                        ${formatearFechaHora(act.fecha_accion)}
                    </small>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// ===== CARGAR SESIONES =====
async function cargarSesiones() {
    const container = document.getElementById('sesionesContainer');
    
    try {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Cargando...</p></div>';
        
        const response = await fetch('controller/cPerfil.php?action=sesiones&limite=10');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarSesiones(data.data);
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-desktop"></i><p>No hay sesiones registradas</p></div>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error al cargar sesiones</p></div>';
    }
}

function renderizarSesiones(sesiones) {
    const container = document.getElementById('sesionesContainer');
    
    let html = `
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Navegador/SO</th>
                        <th>Inicio de Sesión</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    sesiones.forEach(ses => {
        const ua = ses.user_agent || '';
        let navegadorIcon = detectarNavegador(ua);
        let soIcon = detectarSO(ua);
        
        html += `
            <tr>
                <td><code>${escapeHtml(ses.ip_address)}</code></td>
                <td>
                    <small>
                        ${soIcon} - ${navegadorIcon}
                    </small>
                </td>
                <td>${formatearFechaHora(ses.fecha_inicio)}</td>
                <td>
                    ${ses.activa == 1 ? 
                        '<span class="badge badge-success"><i class="fas fa-circle"></i> Activa</span>' :
                        '<span class="badge badge-secondary">Cerrada</span>'
                    }
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// ===== DETECTAR NAVEGADOR Y SO =====
function detectarNavegador(ua) {
    if (ua.includes('Chrome')) return '<i class="fab fa-chrome"></i> Chrome';
    if (ua.includes('Firefox')) return '<i class="fab fa-firefox"></i> Firefox';
    if (ua.includes('Safari')) return '<i class="fab fa-safari"></i> Safari';
    if (ua.includes('Edge')) return '<i class="fab fa-edge"></i> Edge';
    return '<i class="fas fa-browser"></i> Otro';
}

function detectarSO(ua) {
    if (ua.includes('Windows')) return '<i class="fab fa-windows"></i> Windows';
    if (ua.includes('Mac')) return '<i class="fab fa-apple"></i> Mac';
    if (ua.includes('Linux')) return '<i class="fab fa-linux"></i> Linux';
    if (ua.includes('Android')) return '<i class="fab fa-android"></i> Android';
    if (ua.includes('iPhone') || ua.includes('iPad')) return '<i class="fab fa-apple"></i> iOS';
    return '<i class="fas fa-desktop"></i> Otro';
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaPerfil');
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
            <button class="btn-close" onclick="cerrarAlertaPerfil()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaPerfil();
    }, 5000);
}

function cerrarAlertaPerfil() {
    const alertaDiv = document.getElementById('alertaPerfil');
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

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function formatearNumero(numero, decimales = 2) {
    return parseFloat(numero).toFixed(decimales).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}