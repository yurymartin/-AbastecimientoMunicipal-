// ===== VARIABLES GLOBALES =====
let productosData = [];
let productosFiltrados = [];
let categoriasData = [];
let viendoStockBajo = false;

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarEstadisticas();
    cargarCategorias();
    cargarProductos();
    initEventListeners();
});

// ===== EVENT LISTENERS =====
function initEventListeners() {
    const inputBuscar = document.getElementById('buscarProducto');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', debounce(filtrarProductos, 300));
    }
    
    const filtroCategoria = document.getElementById('filtroCategoria');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroStock = document.getElementById('filtroStock');
    
    if (filtroCategoria) filtroCategoria.addEventListener('change', filtrarProductos);
    if (filtroEstado) filtroEstado.addEventListener('change', filtrarProductos);
    if (filtroStock) filtroStock.addEventListener('change', filtrarProductos);
    
    const btnStockBajo = document.getElementById('btnStockBajo');
    if (btnStockBajo) {
        btnStockBajo.addEventListener('click', toggleStockBajo);
    }
    
    const inputCodigo = document.getElementById('codigo_producto');
    if (inputCodigo) {
        inputCodigo.addEventListener('blur', function() {
            if (this.value.length >= 3) {
                validarCodigoUnico(this.value);
            }
        });
    }
    
    const formProducto = document.getElementById('formProducto');
    const formStock = document.getElementById('formStock');
    
    if (formProducto) formProducto.addEventListener('submit', guardarProducto);
    if (formStock) formStock.addEventListener('submit', guardarAjusteStock);
    
    const stockActual = document.getElementById('stock_actual');
    const stockMinimo = document.getElementById('stock_minimo');
    
    if (stockActual && stockMinimo) {
        stockActual.addEventListener('change', validarStocks);
        stockMinimo.addEventListener('change', validarStocks);
    }
}

// ===== CARGAR ESTADÍSTICAS =====
async function cargarEstadisticas() {
    try {
        const response = await fetch('controller/cProducto.php?action=estadisticas');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalProductos').textContent = data.data.totalProductos;
            document.getElementById('totalStockBajo').textContent = data.data.totalStockBajo;
            
            const btnStockBajo = document.getElementById('btnStockBajo');
            const cardStockBajo = document.getElementById('cardStockBajo');
            
            if (data.data.totalStockBajo > 0) {
                btnStockBajo.style.display = 'flex';
                document.getElementById('textoStockBajo').textContent = 
                    `Stock Bajo (${data.data.totalStockBajo})`;
                cardStockBajo.classList.remove('green');
                cardStockBajo.classList.add('orange');
            } else {
                btnStockBajo.style.display = 'none';
                cardStockBajo.classList.remove('orange');
                cardStockBajo.classList.add('green');
            }
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// ===== CARGAR CATEGORÍAS =====
async function cargarCategorias() {
    try {
        const response = await fetch('controller/cProducto.php?action=obtenerCategorias');
        const data = await response.json();
        
        if (data.success) {
            categoriasData = data.data;
            
            const filtroCategoria = document.getElementById('filtroCategoria');
            if (filtroCategoria) {
                filtroCategoria.innerHTML = '<option value="">Todas las categorías</option>';
                categoriasData.forEach(cat => {
                    filtroCategoria.innerHTML += 
                        `<option value="${cat.nombre}">${escapeHtml(cat.nombre)}</option>`;
                });
            }
            
            const selectCategoria = document.getElementById('categoriaId');
            if (selectCategoria) {
                selectCategoria.innerHTML = '<option value="">Seleccione...</option>';
                categoriasData.forEach(cat => {
                    selectCategoria.innerHTML += 
                        `<option value="${cat.idCategoria}">${escapeHtml(cat.nombre)}</option>`;
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}

// ===== CARGAR PRODUCTOS =====
async function cargarProductos() {
    try {
        mostrarLoading('Cargando productos...');
        
        const response = await fetch('controller/cProducto.php?action=listar');
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            productosData = data.data;
            productosFiltrados = [...productosData];
            viendoStockBajo = false;
            renderizarTabla();
            cargarEstadisticas();
        } else {
            mostrarAlerta('Error al cargar productos: ' + data.message, 'danger');
            renderizarTablaVacia('Error al cargar datos');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar productos', 'danger');
        renderizarTablaVacia('Error de conexión');
    }
}

// ===== TOGGLE STOCK BAJO =====
async function toggleStockBajo() {
    if (viendoStockBajo) {
        cargarProductos();
        document.getElementById('textoStockBajo').innerHTML = 
            '<i class="fas fa-exclamation-triangle"></i> ' + 
            document.getElementById('textoStockBajo').textContent.replace('Ver Todos', 'Stock Bajo');
    } else {
        try {
            mostrarLoading('Cargando productos con stock bajo...');
            
            const response = await fetch('controller/cProducto.php?action=stockBajo');
            const data = await response.json();
            
            ocultarLoading();
            
            if (data.success) {
                productosData = data.data;
                productosFiltrados = [...productosData];
                viendoStockBajo = true;
                renderizarTabla();
                
                document.getElementById('textoStockBajo').innerHTML = 
                    '<i class="fas fa-arrow-left"></i> Ver Todos';
            }
        } catch (error) {
            console.error('Error:', error);
            ocultarLoading();
            mostrarAlerta('Error al cargar stock bajo', 'danger');
        }
    }
}

// ===== CALCULAR ESTADO DE STOCK =====
function calcularEstadoStock(producto) {
    const stock = parseInt(producto.stock_actual);
    const stockMin = parseInt(producto.stock_minimo);
    
    if (stock <= 0) {
        return { estado: 'Sin Stock', clase: 'danger', icono: 'exclamation-circle' };
    } else if (stock <= stockMin) {
        return { estado: 'Crítico', clase: 'danger', icono: 'exclamation-circle' };
    } else if (stock <= (stockMin * 1.5)) {
        return { estado: 'Bajo', clase: 'warning', icono: 'exclamation-triangle' };
    } else {
        return { estado: 'Normal', clase: 'success', icono: 'check-circle' };
    }
}

// ===== RENDERIZAR TABLA =====
function renderizarTabla() {
    const tbody = document.getElementById('tbodyProductos');
    
    if (!tbody) return;
    
    if (productosFiltrados.length === 0) {
        const mensaje = viendoStockBajo ? 
            '¡Excelente! No hay productos con stock bajo' : 
            'No se encontraron productos';
        renderizarTablaVacia(mensaje);
        return;
    }
    
    tbody.innerHTML = productosFiltrados.map(prod => {
        const estadoStock = calcularEstadoStock(prod);
        
        return `
            <tr>
                <td><strong>${escapeHtml(prod.codigo_producto)}</strong></td>
                <td>
                    <div>
                        <strong>${escapeHtml(prod.nombre_producto)}</strong><br>
                        <small style="color: #6b7280;">${escapeHtml(prod.unidad_medida)}</small>
                    </div>
                </td>
                <td>${escapeHtml(prod.nombre_categoria || '-')}</td>
                <td style="text-align: center;">
                    <strong style="font-size: 18px; color: ${
                        estadoStock.clase === 'danger' ? '#ef4444' : 
                        estadoStock.clase === 'warning' ? '#f59e0b' : '#10b981'
                    };">${prod.stock_actual}</strong>
                </td>
                <td style="text-align: center;">${prod.stock_minimo}</td>
                <td>
                    <span class="badge badge-${estadoStock.clase}">
                        <i class="fas fa-${estadoStock.icono}"></i> ${estadoStock.estado}
                    </span>
                </td>
                <td>S/ ${parseFloat(prod.precio_referencial || 0).toFixed(2)}</td>
                <td>
                    ${prod.estado === 'Activo' ? 
                        '<span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>' :
                        '<span class="badge badge-danger"><i class="fas fa-times"></i> Inactivo</span>'
                    }
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-info" 
                                onclick="abrirModalAjustarStock(${prod.idProducto}, '${escapeHtml(prod.nombre_producto)}')"
                                title="Ajustar Stock">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button class="btn-icon btn-warning" 
                                onclick="editarProducto(${prod.idProducto})"
                                title="Editar producto">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" 
                                onclick="confirmarEliminar(${prod.idProducto}, '${escapeHtml(prod.nombre_producto)}')"
                                title="Eliminar producto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderizarTablaVacia(mensaje) {
    const tbody = document.getElementById('tbodyProductos');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-box"></i>
                    <p>${mensaje}</p>
                </div>
            </td>
        </tr>
    `;
}

// ===== FILTRAR PRODUCTOS =====
function filtrarProductos() {
    const busqueda = document.getElementById('buscarProducto').value.toUpperCase();
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const tipoStock = document.getElementById('filtroStock').value;
    
    productosFiltrados = productosData.filter(prod => {
        const cumpleBusqueda = !busqueda || 
            prod.codigo_producto.toUpperCase().includes(busqueda) ||
            prod.nombre_producto.toUpperCase().includes(busqueda) ||
            (prod.descripcion && prod.descripcion.toUpperCase().includes(busqueda));
        
        const cumpleCategoria = !categoria || prod.nombre_categoria === categoria;
        
        const cumpleEstado = !estado || prod.estado === estado;
        
        let cumpleStock = true;
        if (tipoStock) {
            const estadoStock = calcularEstadoStock(prod);
            if (tipoStock === 'critico') {
                cumpleStock = estadoStock.estado === 'Sin Stock' || estadoStock.estado === 'Crítico';
            } else if (tipoStock === 'bajo') {
                cumpleStock = estadoStock.estado === 'Bajo';
            } else if (tipoStock === 'normal') {
                cumpleStock = estadoStock.estado === 'Normal';
            }
        }
        
        return cumpleBusqueda && cumpleCategoria && cumpleEstado && cumpleStock;
    });
    
    renderizarTabla();
}

// ===== MODAL PRODUCTO =====
function abrirModalProducto(id = null) {
    const modal = document.getElementById('modalProducto');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formProducto');
    
    if (!modal || !form) return;
    
    form.reset();
    document.getElementById('idProducto').value = '';
    
    if (id) {
        titulo.textContent = 'Editar Producto';
        cargarDatosProducto(id);
    } else {
        titulo.textContent = 'Nuevo Producto';
    }
    
    mostrarModal('modalProducto');
}

function cerrarModalProducto() {
    cerrarModal('modalProducto');
    document.getElementById('formProducto').reset();
}

// ===== CARGAR DATOS PARA EDITAR =====
async function cargarDatosProducto(id) {
    try {
        mostrarLoading('Cargando datos...');
        
        const response = await fetch(`controller/cProducto.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            const prod = data.data;
            
            document.getElementById('idProducto').value = prod.idProducto;
            document.getElementById('codigo_producto').value = prod.codigo_producto;
            document.getElementById('nombre_producto').value = prod.nombre_producto;
            document.getElementById('descripcion').value = prod.descripcion || '';
            document.getElementById('categoriaId').value = prod.categoriaId;
            document.getElementById('unidad_medida').value = prod.unidad_medida;
            document.getElementById('stock_actual').value = prod.stock_actual;
            document.getElementById('stock_minimo').value = prod.stock_minimo;
            document.getElementById('precio_referencial').value = prod.precio_referencial;
            document.getElementById('estado').value = prod.estado;
        } else {
            mostrarAlerta('Error al cargar datos: ' + data.message, 'danger');
            cerrarModalProducto();
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión', 'danger');
        cerrarModalProducto();
    }
}

// ===== GUARDAR PRODUCTO =====
async function guardarProducto(event) {
    event.preventDefault();
    
    const form = document.getElementById('formProducto');
    const formData = new FormData(form);
    formData.append('action', 'guardar');
    
    const codigo = formData.get('codigo_producto');
    if (codigo.length < 3) {
        mostrarAlerta('El código debe tener al menos 3 caracteres', 'warning');
        return;
    }
    
    try {
        mostrarLoading('Guardando producto...');
        
        const response = await fetch('controller/cProducto.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 
                data.message.includes('ADVERTENCIA') ? 'warning' : 'success');
            cerrarModalProducto();
            cargarProductos();
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
function editarProducto(id) {
    abrirModalProducto(id);
}

// ===== ELIMINAR =====
function confirmarEliminar(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el producto "${nombre}"?\n\nNota: Si tiene pedidos asociados, no se podrá eliminar.`)) {
        eliminarProducto(id);
    }
}

async function eliminarProducto(id) {
    try {
        mostrarLoading('Eliminando producto...');
        
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', id);
        
        const response = await fetch('controller/cProducto.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cargarProductos();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al eliminar', 'danger');
    }
}

// ===== MODAL AJUSTAR STOCK =====
function abrirModalAjustarStock(id, nombre) {
    document.getElementById('idProductoStock').value = id;
    document.getElementById('nombreProductoStock').textContent = nombre;
    document.getElementById('cantidad').value = '';
    document.getElementById('operacion').value = 'sumar';
    
    mostrarModal('modalStock');
}

function cerrarModalStock() {
    cerrarModal('modalStock');
    document.getElementById('formStock').reset();
}

// ===== GUARDAR AJUSTE DE STOCK =====
async function guardarAjusteStock(event) {
    event.preventDefault();
    
    const form = document.getElementById('formStock');
    const formData = new FormData(form);
    formData.append('action', 'actualizarStock');
    
    const cantidad = parseInt(formData.get('cantidad'));
    if (cantidad <= 0) {
        mostrarAlerta('La cantidad debe ser mayor a 0', 'warning');
        return;
    }
    
    try {
        mostrarLoading('Actualizando stock...');
        
        const response = await fetch('controller/cProducto.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalStock();
            cargarProductos();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al actualizar stock', 'danger');
    }
}

// ===== VALIDAR CÓDIGO ÚNICO =====
async function validarCodigoUnico(codigo) {
    const idProducto = document.getElementById('idProducto').value;
    
    try {
        const url = `controller/cProducto.php?action=verificarCodigo&codigo=${codigo}` + 
                    (idProducto ? `&excluirId=${idProducto}` : '');
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.existe) {
            const inputCodigo = document.getElementById('codigo_producto');
            inputCodigo.style.borderColor = 'var(--danger)';
            mostrarAlerta('Este código ya está registrado', 'warning');
        }
    } catch (error) {
        console.error('Error al validar código:', error);
    }
}

// ===== VALIDAR STOCKS =====
function validarStocks() {
    const stockActual = parseInt(document.getElementById('stock_actual').value) || 0;
    const stockMinimo = parseInt(document.getElementById('stock_minimo').value) || 0;
    
    if (stockActual <= stockMinimo && stockActual > 0) {
        mostrarAlerta('⚠️ Advertencia: El stock actual está por debajo o igual al mínimo', 'warning');
    }
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaProducto');
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
            <button class="btn-close" onclick="cerrarAlertaProducto()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaProducto();
    }, 5000);
}

function cerrarAlertaProducto() {
    const alertaDiv = document.getElementById('alertaProducto');
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