// ===== VARIABLES GLOBALES =====
let gastosData = [];
let gastosFiltrados = [];
let pedidosDisponibles = [];
let tiposGastos = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    initEventListeners();
});

// ===== EVENT LISTENERS =====
function initEventListeners() {
    const inputBuscar = document.getElementById('buscarGasto');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', debounce(buscarGastos, 300));
    }
    
    const formGasto = document.getElementById('formGasto');
    if (formGasto) {
        formGasto.addEventListener('submit', guardarGasto);
    }
}

// ===== CARGAR DATOS INICIALES =====
async function cargarDatosIniciales() {
    await Promise.all([
        cargarGastos(),
        cargarPedidosDisponibles(),
        cargarTiposGastos()
    ]);
}

// ===== CARGAR GASTOS =====
async function cargarGastos() {
    try {
        mostrarLoading('Cargando gastos...');
        
        const response = await fetch('controller/cGastos.php?action=listar');
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            gastosData = data.data;
            gastosFiltrados = [...gastosData];
            renderizarTabla();
            actualizarEstadisticas(data);
        } else {
            mostrarAlerta('Error al cargar gastos: ' + data.message, 'danger');
            renderizarTablaVacia('Error al cargar datos');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar gastos', 'danger');
        renderizarTablaVacia('Error de conexión');
    }
}

// ===== CARGAR PEDIDOS DISPONIBLES =====
async function cargarPedidosDisponibles() {
    try {
        const response = await fetch('controller/cGastos.php?action=pedidos_disponibles');
        const data = await response.json();
        
        if (data.success) {
            pedidosDisponibles = data.data;
            llenarSelectPedidos();
        }
    } catch (error) {
        console.error('Error al cargar pedidos:', error);
    }
}

function llenarSelectPedidos() {
    const select = document.getElementById('pedidoId');
    if (!select) return;
    
    select.innerHTML = '<option value="">Seleccione un pedido...</option>';
    
    pedidosDisponibles.forEach(pedido => {
        select.innerHTML += `
            <option value="${pedido.idPedido}">
                ${escapeHtml(pedido.numero_pedido)} - ${escapeHtml(pedido.razon_social)}
            </option>
        `;
    });
}

// ===== CARGAR TIPOS DE GASTOS =====
async function cargarTiposGastos() {
    try {
        const response = await fetch('controller/cGastos.php?action=tipos_gastos');
        const data = await response.json();
        
        if (data.success) {
            tiposGastos = data.data;
            llenarSelectTipos();
        }
    } catch (error) {
        console.error('Error al cargar tipos:', error);
    }
}

function llenarSelectTipos() {
    const select = document.getElementById('filtroTipoGasto');
    if (!select) return;
    
    select.innerHTML = '<option value="">Todos los tipos</option>';
    
    tiposGastos.forEach(tipo => {
        select.innerHTML += `
            <option value="${escapeHtml(tipo.tipo_gasto)}">
                ${escapeHtml(tipo.tipo_gasto)}
            </option>
        `;
    });
}

// ===== ACTUALIZAR ESTADÍSTICAS =====
function actualizarEstadisticas(data) {
    document.getElementById('totalGastosDisplay').textContent = 
        'S/ ' + formatearNumero(data.monto_total);
    document.getElementById('cantidadGastos').textContent = data.total;
    
    const mesActual = new Date().getMonth();
    const anioActual = new Date().getFullYear();
    
    const gastosMes = gastosData.filter(g => {
        const fecha = new Date(g.fecha_gasto);
        return fecha.getMonth() === mesActual && fecha.getFullYear() === anioActual;
    });
    
    const totalMes = gastosMes.reduce((sum, g) => sum + parseFloat(g.monto), 0);
    document.getElementById('gastosMesActual').textContent = 'S/ ' + formatearNumero(totalMes);
}

// ===== RENDERIZAR TABLA =====
function renderizarTabla() {
    const tbody = document.getElementById('tbodyGastos');
    const tfoot = document.getElementById('tfootGastos');
    const totalTabla = document.getElementById('totalTabla');
    
    if (!tbody) return;
    
    if (gastosFiltrados.length === 0) {
        renderizarTablaVacia('No se encontraron gastos');
        tfoot.style.display = 'none';
        return;
    }
    
    let total = 0;
    
    tbody.innerHTML = gastosFiltrados.map(gasto => {
        total += parseFloat(gasto.monto);
        
        return `
            <tr>
                <td><strong>${escapeHtml(gasto.numero_pedido)}</strong></td>
                <td>
                    <span class="badge badge-info">
                        ${escapeHtml(gasto.tipo_gasto)}
                    </span>
                </td>
                <td>${formatearFecha(gasto.fecha_gasto)}</td>
                <td>
                    <div>
                        <strong>${escapeHtml(gasto.razon_social)}</strong>
                    </div>
                </td>
                <td>
                    ${gasto.tipo_documento ? `
                        <small>${escapeHtml(gasto.tipo_documento)}: ${escapeHtml(gasto.numero_documento)}</small>
                    ` : '<span style="color: #9ca3af;">Sin documento</span>'}
                </td>
                <td>
                    <small>${escapeHtml(gasto.descripcion ? gasto.descripcion.substring(0, 50) : '-')}${
                        gasto.descripcion && gasto.descripcion.length > 50 ? '...' : ''
                    }</small>
                </td>
                <td>
                    <strong style="color: #ef4444;">S/ ${formatearNumero(gasto.monto)}</strong>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-warning" 
                                onclick="editarGasto(${gasto.idGasto})"
                                title="Editar gasto">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" 
                                onclick="confirmarEliminar(${gasto.idGasto}, '${escapeHtml(gasto.tipo_gasto)}')"
                                title="Eliminar gasto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    totalTabla.textContent = 'S/ ' + formatearNumero(total);
    tfoot.style.display = 'table-footer-group';
}

function renderizarTablaVacia(mensaje) {
    const tbody = document.getElementById('tbodyGastos');
    const tfoot = document.getElementById('tfootGastos');
    
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>${mensaje}</p>
                </div>
            </td>
        </tr>
    `;
    
    tfoot.style.display = 'none';
}

// ===== FILTRAR GASTOS =====
function filtrarGastos() {
    const tipoGasto = document.getElementById('filtroTipoGasto').value;
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    
    gastosFiltrados = gastosData.filter(gasto => {
        if (tipoGasto && gasto.tipo_gasto !== tipoGasto) {
            return false;
        }
        
        if (fechaDesde && gasto.fecha_gasto < fechaDesde) {
            return false;
        }
        
        if (fechaHasta && gasto.fecha_gasto > fechaHasta) {
            return false;
        }
        
        return true;
    });
    
    renderizarTabla();
}

// ===== BUSCAR GASTOS =====
function buscarGastos() {
    const termino = document.getElementById('buscarGasto').value.toUpperCase();
    
    if (!termino) {
        gastosFiltrados = [...gastosData];
        renderizarTabla();
        return;
    }
    
    gastosFiltrados = gastosData.filter(gasto => {
        return gasto.numero_pedido.toUpperCase().includes(termino) ||
               gasto.tipo_gasto.toUpperCase().includes(termino) ||
               gasto.razon_social.toUpperCase().includes(termino) ||
               (gasto.descripcion && gasto.descripcion.toUpperCase().includes(termino)) ||
               (gasto.tipo_documento && gasto.tipo_documento.toUpperCase().includes(termino)) ||
               (gasto.numero_documento && gasto.numero_documento.toUpperCase().includes(termino));
    });
    
    renderizarTabla();
}

// ===== MODAL GASTO =====
function abrirModalGasto(id = null) {
    const modal = document.getElementById('modalGasto');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formGasto');
    
    if (!modal || !form) return;
    
    form.reset();
    document.getElementById('idGasto').value = '';
    document.getElementById('fecha_gasto').value = new Date().toISOString().split('T')[0];
    
    if (id) {
        titulo.textContent = 'Editar Gasto';
        cargarDatosGasto(id);
    } else {
        titulo.textContent = 'Nuevo Gasto';
    }
    
    mostrarModal('modalGasto');
}

function cerrarModalGasto() {
    cerrarModal('modalGasto');
    document.getElementById('formGasto').reset();
}

// ===== CARGAR DATOS PARA EDITAR =====
async function cargarDatosGasto(id) {
    try {
        mostrarLoading('Cargando datos...');
        
        const response = await fetch(`controller/cGastos.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            const gasto = data.data;
            
            document.getElementById('idGasto').value = gasto.idGasto;
            document.getElementById('pedidoId').value = gasto.pedidoId;
            document.getElementById('tipo_gasto').value = gasto.tipo_gasto;
            document.getElementById('monto').value = gasto.monto;
            document.getElementById('fecha_gasto').value = gasto.fecha_gasto;
            document.getElementById('tipo_documento').value = gasto.tipo_documento || '';
            document.getElementById('numero_documento').value = gasto.numero_documento || '';
            document.getElementById('descripcion').value = gasto.descripcion || '';
        } else {
            mostrarAlerta('Error al cargar datos: ' + data.message, 'danger');
            cerrarModalGasto();
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión', 'danger');
        cerrarModalGasto();
    }
}

// ===== GUARDAR GASTO =====
async function guardarGasto(event) {
    event.preventDefault();
    
    const form = document.getElementById('formGasto');
    const formData = new FormData(form);
    formData.append('action', 'guardar');
    
    try {
        mostrarLoading('Guardando gasto...');
        
        const response = await fetch('controller/cGastos.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalGasto();
            cargarGastos();
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
function editarGasto(id) {
    abrirModalGasto(id);
}

// ===== ELIMINAR =====
function confirmarEliminar(id, tipoGasto) {
    if (confirm(`¿Está seguro de eliminar el gasto de "${tipoGasto}"?`)) {
        eliminarGasto(id);
    }
}

async function eliminarGasto(id) {
    try {
        mostrarLoading('Eliminando gasto...');
        
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', id);
        
        const response = await fetch('controller/cGastos.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarGastos();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al eliminar', 'danger');
    }
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaGastos');
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
            <button class="btn-close" onclick="cerrarAlertaGastos()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaGastos();
    }, 5000);
}

function cerrarAlertaGastos() {
    const alertaDiv = document.getElementById('alertaGastos');
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
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function formatearNumero(numero, decimales = 2) {
    return parseFloat(numero).toFixed(decimales).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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