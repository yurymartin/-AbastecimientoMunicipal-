// ===== VARIABLES GLOBALES =====
let proveedoresData = [];
let proveedoresFiltrados = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarProveedores();
    initEventListeners();
});

// ===== EVENT LISTENERS =====
function initEventListeners() {
    const inputBuscar = document.getElementById('buscarProveedor');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', debounce(function() {
            filtrarProveedores();
        }, 300));
    }
    
    const filtroEstado = document.getElementById('filtroEstado');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function() {
            filtrarProveedores();
        });
    }
    
    const inputRuc = document.getElementById('ruc');
    if (inputRuc) {
        inputRuc.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 11) {
                validarRucUnico(this.value);
            }
        });
    }
    
    const form = document.getElementById('formProveedor');
    if (form) {
        form.addEventListener('submit', guardarProveedor);
    }
}

// ===== CARGAR PROVEEDORES =====
async function cargarProveedores() {
    try {
        mostrarLoading('Cargando proveedores...');
        
        const response = await fetch('controller/cProveedor.php?action=listar');
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            proveedoresData = data.data;
            proveedoresFiltrados = [...proveedoresData];
            renderizarTabla();
        } else {
            mostrarAlerta('Error al cargar proveedores: ' + data.message, 'danger');
            renderizarTablaVacia('Error al cargar datos');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar proveedores', 'danger');
        renderizarTablaVacia('Error de conexión');
    }
}

// ===== RENDERIZAR TABLA =====
function renderizarTabla() {
    const tbody = document.getElementById('tbodyProveedores');
    
    if (!tbody) return;
    
    if (proveedoresFiltrados.length === 0) {
        renderizarTablaVacia('No se encontraron proveedores');
        return;
    }
    
    tbody.innerHTML = proveedoresFiltrados.map(proveedor => `
        <tr>
            <td>${proveedor.idProveedor}</td>
            <td><strong>${escapeHtml(proveedor.ruc)}</strong></td>
            <td>${escapeHtml(proveedor.razon_social)}</td>
            <td>${escapeHtml(proveedor.nombre_comercial)}</td>
            <td>${escapeHtml(proveedor.telefono || '-')}</td>
            <td>${escapeHtml(proveedor.email || '-')}</td>
            <td>
                ${proveedor.contacto_nombre ? `
                    <div style="font-size: 12px;">
                        <strong>${escapeHtml(proveedor.contacto_nombre)}</strong><br>
                        ${escapeHtml(proveedor.contacto_telefono || '')}
                    </div>
                ` : '<span style="color: #9ca3af;">Sin contacto</span>'}
            </td>
            <td>
                ${proveedor.estado === 'Activo' ? 
                    '<span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>' :
                    '<span class="badge badge-danger"><i class="fas fa-times"></i> Inactivo</span>'
                }
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-warning" 
                            onclick="editarProveedor(${proveedor.idProveedor})"
                            title="Editar proveedor">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-danger" 
                            onclick="confirmarEliminar(${proveedor.idProveedor}, '${escapeHtml(proveedor.razon_social)}')"
                            title="Eliminar proveedor">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderizarTablaVacia(mensaje) {
    const tbody = document.getElementById('tbodyProveedores');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <p>${mensaje}</p>
                </div>
            </td>
        </tr>
    `;
}

// ===== FILTRAR PROVEEDORES =====
function filtrarProveedores() {
    const busqueda = document.getElementById('buscarProveedor').value.toUpperCase();
    const estado = document.getElementById('filtroEstado').value;
    
    proveedoresFiltrados = proveedoresData.filter(proveedor => {
        const cumpleBusqueda = !busqueda || 
            proveedor.ruc.toUpperCase().includes(busqueda) ||
            proveedor.razon_social.toUpperCase().includes(busqueda) ||
            proveedor.nombre_comercial.toUpperCase().includes(busqueda);
        
        const cumpleEstado = !estado || proveedor.estado === estado;
        
        return cumpleBusqueda && cumpleEstado;
    });
    
    renderizarTabla();
}

// ===== MODAL =====
function abrirModalProveedor(id = null) {
    const modal = document.getElementById('modalProveedor');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formProveedor');
    
    if (!modal || !form) return;
    
    form.reset();
    document.getElementById('idProveedor').value = '';
    
    if (id) {
        titulo.textContent = 'Editar Proveedor';
        cargarDatosProveedor(id);
    } else {
        titulo.textContent = 'Nuevo Proveedor';
    }
    
    mostrarModal('modalProveedor');
}

function cerrarModalProveedor() {
    cerrarModal('modalProveedor');
    document.getElementById('formProveedor').reset();
}

// ===== CARGAR DATOS PARA EDITAR =====
async function cargarDatosProveedor(id) {
    try {
        mostrarLoading('Cargando datos...');
        
        const response = await fetch(`controller/cProveedor.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            const proveedor = data.data;
            
            document.getElementById('idProveedor').value = proveedor.idProveedor;
            document.getElementById('ruc').value = proveedor.ruc;
            document.getElementById('razon_social').value = proveedor.razon_social;
            document.getElementById('nombre_comercial').value = proveedor.nombre_comercial;
            document.getElementById('direccion').value = proveedor.direccion || '';
            document.getElementById('telefono').value = proveedor.telefono || '';
            document.getElementById('email').value = proveedor.email || '';
            document.getElementById('contacto_nombre').value = proveedor.contacto_nombre || '';
            document.getElementById('contacto_telefono').value = proveedor.contacto_telefono || '';
            document.getElementById('estado').value = proveedor.estado;
        } else {
            mostrarAlerta('Error al cargar datos: ' + data.message, 'danger');
            cerrarModalProveedor();
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión', 'danger');
        cerrarModalProveedor();
    }
}

// ===== GUARDAR PROVEEDOR =====
async function guardarProveedor(event) {
    event.preventDefault();
    
    const form = document.getElementById('formProveedor');
    const formData = new FormData(form);
    formData.append('action', 'guardar');
    
    const ruc = formData.get('ruc');
    if (!/^\d{11}$/.test(ruc)) {
        mostrarAlerta('El RUC debe tener exactamente 11 dígitos', 'warning');
        return;
    }
    
    try {
        mostrarLoading('Guardando proveedor...');
        
        const response = await fetch('controller/cProveedor.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalProveedor();
            cargarProveedores();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar', 'danger');
    }
}

// ===== EDITAR =====
function editarProveedor(id) {
    abrirModalProveedor(id);
}

// ===== ELIMINAR =====
function confirmarEliminar(id, razonSocial) {
    if (confirm(`¿Está seguro de eliminar el proveedor "${razonSocial}"?\n\nNota: No se puede eliminar si tiene pedidos asociados.`)) {
        eliminarProveedor(id);
    }
}

async function eliminarProveedor(id) {
    try {
        mostrarLoading('Eliminando proveedor...');
        
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', id);
        
        const response = await fetch('controller/cProveedor.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarProveedores();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al eliminar', 'danger');
    }
}

// ===== VALIDAR RUC ÚNICO =====
async function validarRucUnico(ruc) {
    const idProveedor = document.getElementById('idProveedor').value;
    
    try {
        const url = `controller/cProveedor.php?action=verificarRuc&ruc=${ruc}` + 
                    (idProveedor ? `&excluirId=${idProveedor}` : '');
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.existe) {
            const inputRuc = document.getElementById('ruc');
            inputRuc.style.borderColor = 'var(--danger)';
            mostrarAlerta('Este RUC ya está registrado', 'warning');
        }
    } catch (error) {
        console.error('Error al validar RUC:', error);
    }
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaProveedor');
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
            <button class="btn-close" onclick="cerrarAlertaProveedor()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaProveedor();
    }, 5000);
}

function cerrarAlertaProveedor() {
    const alertaDiv = document.getElementById('alertaProveedor');
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
    return text.replace(/[&<>"']/g, m => map[m]);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}