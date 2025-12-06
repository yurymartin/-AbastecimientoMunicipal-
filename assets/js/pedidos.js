// ===== VARIABLES GLOBALES =====
let pedidosData = [];
let pedidosFiltrados = [];
let estadosData = [];
let proveedoresData = [];
let productosData = [];
let productosSeleccionados = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarEstadisticas();
    cargarEstados();
    cargarProveedores();
    cargarProductos();
    cargarPedidos();
    initEventListeners();
});

// ===== EVENT LISTENERS =====
function initEventListeners() {
    const inputBuscar = document.getElementById('buscarPedido');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', debounce(filtrarPedidos, 300));
    }
    
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroProveedor = document.getElementById('filtroProveedor');
    
    if (filtroEstado) filtroEstado.addEventListener('change', filtrarPedidos);
    if (filtroProveedor) filtroProveedor.addEventListener('change', filtrarPedidos);
    
    const formPedido = document.getElementById('formPedido');
    if (formPedido) {
        formPedido.addEventListener('submit', guardarPedido);
    }
    
    const fechaPedido = document.getElementById('fecha_pedido');
    if (fechaPedido && !fechaPedido.value) {
        fechaPedido.value = new Date().toISOString().split('T')[0];
    }
}

// ===== CARGAR ESTADÍSTICAS =====
async function cargarEstadisticas() {
    try {
        const response = await fetch('controller/cPedido.php?action=estadisticas');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalPendientes').textContent = data.data.totalPendientes;
            document.getElementById('totalEnProceso').textContent = data.data.totalEnProceso;
            document.getElementById('totalEntregados').textContent = data.data.totalEntregados;
            document.getElementById('totalGeneral').textContent = data.data.totalGeneral;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// ===== CARGAR ESTADOS =====
async function cargarEstados() {
    try {
        const response = await fetch('controller/cPedido.php?action=obtenerEstados');
        const data = await response.json();
        
        if (data.success) {
            estadosData = data.data;
            
            const filtroEstado = document.getElementById('filtroEstado');
            if (filtroEstado) {
                filtroEstado.innerHTML = '<option value="">Todos los estados</option>';
                estadosData.forEach(estado => {
                    filtroEstado.innerHTML += 
                        `<option value="${estado.idEstado}">${escapeHtml(estado.nombre)}</option>`;
                });
            }
            
            const selectEstado = document.getElementById('estadoId');
            if (selectEstado) {
                selectEstado.innerHTML = '';
                estadosData.forEach(estado => {
                    selectEstado.innerHTML += 
                        `<option value="${estado.idEstado}">${escapeHtml(estado.nombre)}</option>`;
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar estados:', error);
    }
}

// ===== CARGAR PROVEEDORES =====
async function cargarProveedores() {
    try {
        const response = await fetch('controller/cPedido.php?action=obtenerProveedores');
        const data = await response.json();
        
        if (data.success) {
            proveedoresData = data.data;
            
            const filtroProveedor = document.getElementById('filtroProveedor');
            if (filtroProveedor) {
                filtroProveedor.innerHTML = '<option value="">Todos los proveedores</option>';
                proveedoresData.forEach(prov => {
                    filtroProveedor.innerHTML += 
                        `<option value="${prov.idProveedor}">${escapeHtml(prov.razon_social)}</option>`;
                });
            }
            
            const selectProveedor = document.getElementById('proveedorId');
            if (selectProveedor) {
                selectProveedor.innerHTML = '<option value="">Seleccione un proveedor...</option>';
                proveedoresData.forEach(prov => {
                    selectProveedor.innerHTML += 
                        `<option value="${prov.idProveedor}">${escapeHtml(prov.razon_social)} - ${prov.ruc}</option>`;
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar proveedores:', error);
    }
}

// ===== CARGAR PRODUCTOS =====
async function cargarProductos() {
    try {
        const response = await fetch('controller/cPedido.php?action=obtenerProductos');
        const data = await response.json();
        
        if (data.success) {
            productosData = data.data;
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}

// ===== CARGAR PEDIDOS =====
async function cargarPedidos() {
    try {
        mostrarLoading('Cargando pedidos...');
        
        const response = await fetch('controller/cPedido.php?action=listar');
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            pedidosData = data.data;
            pedidosFiltrados = [...pedidosData];
            renderizarTabla();
            cargarEstadisticas();
        } else {
            mostrarAlerta('Error al cargar pedidos: ' + data.message, 'danger');
            renderizarTablaVacia('Error al cargar datos');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar pedidos', 'danger');
        renderizarTablaVacia('Error de conexión');
    }
}

// ===== OBTENER CLASE Y ICONO DE ESTADO =====
function obtenerEstiloEstado(estadoId) {
    const estilos = {
        1: { clase: 'warning', icono: 'clock' },          
        2: { clase: 'info', icono: 'spinner fa-pulse' },  
        3: { clase: 'success', icono: 'check-circle' }, 
        4: { clase: 'danger', icono: 'times-circle' }  
    };
    return estilos[estadoId] || { clase: 'secondary', icono: 'circle' };
}

// ===== RENDERIZAR TABLA =====
function renderizarTabla() {
    const tbody = document.getElementById('tbodyPedidos');
    
    if (!tbody) return;
    
    if (pedidosFiltrados.length === 0) {
        renderizarTablaVacia('No se encontraron pedidos');
        return;
    }
    
    tbody.innerHTML = pedidosFiltrados.map(pedido => {
        const estiloEstado = obtenerEstiloEstado(parseInt(pedido.estadoId));
        
        return `
            <tr>
                <td><strong>${escapeHtml(pedido.numero_pedido)}</strong></td>
                <td>${formatearFecha(pedido.fecha_pedido)}</td>
                <td>
                    <div>
                        <strong>${escapeHtml(pedido.razon_social)}</strong><br>
                        <small style="color: #6b7280;">RUC: ${escapeHtml(pedido.ruc)}</small>
                    </div>
                </td>
                <td style="text-align: center;">
                    <span style="font-weight: 600;">${pedido.cantidad_items || 0}</span>
                </td>
                <td>
                    <strong style="color: #059669;">S/ ${parseFloat(pedido.total).toFixed(2)}</strong>
                </td>
                <td>
                    <span class="badge badge-${estiloEstado.clase}">
                        <i class="fas fa-${estiloEstado.icono}"></i>
                        ${escapeHtml(pedido.estado_nombre)}
                    </span>
                </td>
                <td>
                    ${pedido.fecha_entrega_estimada ? 
                        formatearFecha(pedido.fecha_entrega_estimada) : 
                        '<span style="color: #9ca3af;">Sin fecha</span>'}
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-info" 
                                onclick="verDetallePedido(${pedido.idPedido})"
                                title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${pedido.estadoId != 3 && pedido.estadoId != 4 ? `
                            <button class="btn-icon btn-warning" 
                                    onclick="editarPedido(${pedido.idPedido})"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderizarTablaVacia(mensaje) {
    const tbody = document.getElementById('tbodyPedidos');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>${mensaje}</p>
                </div>
            </td>
        </tr>
    `;
}

// ===== FILTRAR PEDIDOS =====
function filtrarPedidos() {
    const busqueda = document.getElementById('buscarPedido').value.toUpperCase();
    const estadoFiltro = document.getElementById('filtroEstado').value;
    const proveedorFiltro = document.getElementById('filtroProveedor').value;
    
    pedidosFiltrados = pedidosData.filter(pedido => {
        const cumpleBusqueda = !busqueda || 
            pedido.numero_pedido.toUpperCase().includes(busqueda) ||
            pedido.razon_social.toUpperCase().includes(busqueda) ||
            pedido.ruc.includes(busqueda);
        
        const cumpleEstado = !estadoFiltro || pedido.estadoId == estadoFiltro;
        
        const cumpleProveedor = !proveedorFiltro || pedido.proveedorId == proveedorFiltro;
        
        return cumpleBusqueda && cumpleEstado && cumpleProveedor;
    });
    
    renderizarTabla();
}

// ===== MODAL PEDIDO =====
function abrirModalPedido(id = null) {
    const modal = document.getElementById('modalPedido');
    const titulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formPedido');
    
    if (!modal || !form) return;
    
    form.reset();
    document.getElementById('idPedido').value = '';
    productosSeleccionados = [];
    document.getElementById('productosContainer').innerHTML = '';
    
    document.getElementById('fecha_pedido').value = new Date().toISOString().split('T')[0];
    
    if (id) {
        titulo.textContent = 'Editar Pedido';
        cargarDatosPedido(id);
    } else {
        titulo.textContent = 'Nuevo Pedido';
        calcularTotalesPedido();
    }
    
    mostrarModal('modalPedido');
}

function cerrarModalPedido() {
    cerrarModal('modalPedido');
    document.getElementById('formPedido').reset();
    productosSeleccionados = [];
    document.getElementById('productosContainer').innerHTML = '';
}

// ===== CARGAR DATOS PARA EDITAR =====
async function cargarDatosPedido(id) {
    try {
        mostrarLoading('Cargando datos...');
        
        const responsePedido = await fetch(`controller/cPedido.php?action=obtener&id=${id}`);
        const dataPedido = await responsePedido.json();
        
        const responseDetalles = await fetch(`controller/cPedido.php?action=obtenerDetalles&id=${id}`);
        const dataDetalles = await responseDetalles.json();
        
        ocultarLoading();
        
        if (dataPedido.success && dataDetalles.success) {
            const pedido = dataPedido.data;
            
            document.getElementById('idPedido').value = pedido.idPedido;
            document.getElementById('proveedorId').value = pedido.proveedorId;
            document.getElementById('estadoId').value = pedido.estadoId;
            document.getElementById('fecha_pedido').value = pedido.fecha_pedido;
            document.getElementById('fecha_entrega_estimada').value = pedido.fecha_entrega_estimada || '';
            document.getElementById('observaciones').value = pedido.observaciones || '';
            
            productosSeleccionados = dataDetalles.data.map(det => ({
                productoId: det.productoId,
                cantidad: det.cantidad,
                precio_unitario: det.precio_unitario,
                subtotal: det.subtotal
            }));
            
            dataDetalles.data.forEach(detalle => {
                agregarProductoPedido(detalle);
            });
            
            calcularTotalesPedido();
        } else {
            mostrarAlerta('Error al cargar datos del pedido', 'danger');
            cerrarModalPedido();
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión', 'danger');
        cerrarModalPedido();
    }
}

// ===== AGREGAR PRODUCTO =====
function agregarProductoPedido(detalleExistente = null) {
    const container = document.getElementById('productosContainer');
    const index = productosSeleccionados.length;
    
    const div = document.createElement('div');
    div.className = 'producto-item';
    div.setAttribute('data-index', index);
    
    let opcionesProductos = '<option value="">Seleccione un producto...</option>';
    productosData.forEach(prod => {
        const selected = detalleExistente && detalleExistente.productoId == prod.idProducto ? 'selected' : '';
        opcionesProductos += `
            <option value="${prod.idProducto}" 
                    data-precio="${prod.precio_referencial}" 
                    data-nombre="${escapeHtml(prod.nombre_producto)}"
                    data-unidad="${escapeHtml(prod.unidad_medida)}"
                    ${selected}>
                ${escapeHtml(prod.nombre_producto)} - ${escapeHtml(prod.unidad_medida)}
            </option>
        `;
    });
    
    div.innerHTML = `
        <div class="form-row" style="align-items: end; gap: 10px;">
            <div class="form-group" style="flex: 2; margin: 0;">
                <label>Producto <span style="color: var(--danger);">*</span></label>
                <select class="producto-select" required onchange="cargarPrecioProducto(${index})">
                    ${opcionesProductos}
                </select>
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Cantidad <span style="color: var(--danger);">*</span></label>
                <input type="number" class="cantidad-input" 
                       value="${detalleExistente ? detalleExistente.cantidad : 1}" 
                       min="1" required 
                       onchange="calcularSubtotalProducto(${index})">
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Precio Unit.</label>
                <input type="number" class="precio-input" 
                       value="${detalleExistente ? detalleExistente.precio_unitario : 0}" 
                       step="0.01" min="0" required 
                       onchange="calcularSubtotalProducto(${index})">
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Subtotal</label>
                <input type="text" class="subtotal-display" 
                       value="${detalleExistente ? parseFloat(detalleExistente.subtotal).toFixed(2) : '0.00'}" 
                       readonly 
                       style="background: #f3f4f6;">
            </div>
            <button type="button" class="btn-icon btn-danger" 
                    onclick="eliminarProductoPedido(${index})" 
                    title="Eliminar producto"
                    style="margin-bottom: 0;">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(div);
    
    if (!detalleExistente) {
        productosSeleccionados.push({
            productoId: null,
            cantidad: 1,
            precio_unitario: 0,
            subtotal: 0
        });
    }
}

// ===== CARGAR PRECIO DEL PRODUCTO =====
function cargarPrecioProducto(index) {
    const item = document.querySelector(`.producto-item[data-index="${index}"]`);
    if (!item) return;
    
    const select = item.querySelector('.producto-select');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const precio = option.getAttribute('data-precio') || 0;
        const precioInput = item.querySelector('.precio-input');
        precioInput.value = precio;
        
        productosSeleccionados[index].productoId = option.value;
        productosSeleccionados[index].precio_unitario = parseFloat(precio);
        
        calcularSubtotalProducto(index);
    }
}

// ===== CALCULAR SUBTOTAL DEL PRODUCTO =====
function calcularSubtotalProducto(index) {
    const item = document.querySelector(`.producto-item[data-index="${index}"]`);
    if (!item) return;
    
    const cantidad = parseFloat(item.querySelector('.cantidad-input').value) || 0;
    const precio = parseFloat(item.querySelector('.precio-input').value) || 0;
    const subtotal = cantidad * precio;
    
    item.querySelector('.subtotal-display').value = subtotal.toFixed(2);
    
    productosSeleccionados[index].cantidad = cantidad;
    productosSeleccionados[index].precio_unitario = precio;
    productosSeleccionados[index].subtotal = subtotal;
    
    calcularTotalesPedido();
}

// ===== ELIMINAR PRODUCTO =====
function eliminarProductoPedido(index) {
    const item = document.querySelector(`.producto-item[data-index="${index}"]`);
    if (item) {
        item.remove();
        productosSeleccionados.splice(index, 1);
        
        document.querySelectorAll('.producto-item').forEach((elem, newIndex) => {
            elem.setAttribute('data-index', newIndex);
        });
        
        calcularTotalesPedido();
    }
}

// ===== CALCULAR TOTALES DEL PEDIDO =====
function calcularTotalesPedido() {
    let subtotal = 0;
    
    productosSeleccionados.forEach(prod => {
        subtotal += parseFloat(prod.subtotal) || 0;
    });
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('subtotalPedido').textContent = subtotal.toFixed(2);
    document.getElementById('igvPedido').textContent = igv.toFixed(2);
    document.getElementById('totalPedido').textContent = total.toFixed(2);
}

// ===== GUARDAR PEDIDO =====
async function guardarPedido(event) {
    event.preventDefault();
    
    if (productosSeleccionados.length === 0) {
        mostrarAlerta('Debe agregar al menos un producto al pedido', 'warning');
        return;
    }
    
    let productosValidos = true;
    productosSeleccionados.forEach(prod => {
        if (!prod.productoId || prod.cantidad <= 0 || prod.precio_unitario < 0) {
            productosValidos = false;
        }
    });
    
    if (!productosValidos) {
        mostrarAlerta('Complete todos los datos de los productos', 'warning');
        return;
    }
    
    const datos = {
        idPedido: document.getElementById('idPedido').value || null,
        proveedorId: document.getElementById('proveedorId').value,
        estadoId: document.getElementById('estadoId').value,
        fecha_pedido: document.getElementById('fecha_pedido').value,
        fecha_entrega_estimada: document.getElementById('fecha_entrega_estimada').value || null,
        observaciones: document.getElementById('observaciones').value,
        detalles: productosSeleccionados.map(prod => ({
            productoId: prod.productoId,
            cantidad: prod.cantidad,
            precio_unitario: prod.precio_unitario,
            subtotal: prod.subtotal
        }))
    };
    
    try {
        mostrarLoading('Guardando pedido...');
        
        const response = await fetch('controller/cPedido.php?action=guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalPedido();
            cargarPedidos();
            
            if (data.numero_pedido) {
                setTimeout(() => {
                    mostrarAlerta(`Pedido creado: ${data.numero_pedido}`, 'info');
                }, 1000);
            }
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al guardar pedido', 'danger');
    }
}

// ===== EDITAR =====
function editarPedido(id) {
    abrirModalPedido(id);
}

// ===== VER DETALLE DEL PEDIDO =====
async function verDetallePedido(id) {
    try {
        mostrarLoading('Cargando detalle...');
        
        const responsePedido = await fetch(`controller/cPedido.php?action=obtener&id=${id}`);
        const dataPedido = await responsePedido.json();
        
        const responseDetalles = await fetch(`controller/cPedido.php?action=obtenerDetalles&id=${id}`);
        const dataDetalles = await responseDetalles.json();
        
        ocultarLoading();
        
        if (dataPedido.success && dataDetalles.success) {
            const pedido = dataPedido.data;
            const detalles = dataDetalles.data;
            
            renderizarDetallePedido(pedido, detalles);
            mostrarModal('modalDetalle');
        } else {
            mostrarAlerta('Error al cargar el detalle del pedido', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión', 'danger');
    }
}

function renderizarDetallePedido(pedido, detalles) {
    const container = document.getElementById('detalleContent');
    const estiloEstado = obtenerEstiloEstado(parseInt(pedido.estadoId));
    
    let htmlDetalles = `
        <!-- Timeline de Estado -->
        <div class="estado-timeline" style="margin-bottom: 30px;">
            <div class="timeline-step ${pedido.estadoId >= 1 ? 'completed' : ''}">
                <div class="timeline-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div style="font-size: 12px; margin-top: 5px;">Pendiente</div>
            </div>
            <div class="timeline-step ${pedido.estadoId >= 2 ? 'completed' : ''} ${pedido.estadoId == 2 ? 'active' : ''}">
                <div class="timeline-icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <div style="font-size: 12px; margin-top: 5px;">En Proceso</div>
            </div>
            <div class="timeline-step ${pedido.estadoId == 3 ? 'completed active' : ''}">
                <div class="timeline-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div style="font-size: 12px; margin-top: 5px;">Entregado</div>
            </div>
        </div>
        
        <!-- Cambiar Estado -->
        ${pedido.estadoId != 3 && pedido.estadoId != 4 ? `
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Cambiar Estado del Pedido:</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <select id="nuevoEstado" class="filter-select" style="flex: 1;">
                        <option value="">Seleccione nuevo estado...</option>
                        ${pedido.estadoId == 1 ? `
                            <option value="2">En Proceso</option>
                            <option value="3">Entregado</option>
                            <option value="4">Cancelado</option>
                        ` : ''}
                        ${pedido.estadoId == 2 ? `
                            <option value="3">Entregado</option>
                            <option value="4">Cancelado</option>
                        ` : ''}
                    </select>
                    <button type="button" class="btn btn-primary" onclick="cambiarEstadoPedido(${pedido.idPedido})">
                        <i class="fas fa-check"></i> Actualizar
                    </button>
                </div>
            </div>
        ` : ''}
        
        <!-- Información General -->
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px; color: #667eea;">
                <i class="fas fa-info-circle"></i> Información del Pedido
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <small style="color: #6b7280; font-size: 12px;">N° Pedido</small>
                    <p style="margin: 5px 0; font-weight: 600;">${escapeHtml(pedido.numero_pedido)}</p>
                </div>
                <div>
                    <small style="color: #6b7280; font-size: 12px;">Proveedor</small>
                    <p style="margin: 5px 0; font-weight: 600;">${escapeHtml(pedido.razon_social)}</p>
                    <small style="color: #6b7280;">RUC: ${escapeHtml(pedido.ruc)}</small>
                </div>
                <div>
                    <small style="color: #6b7280; font-size: 12px;">Fecha Pedido</small>
                    <p style="margin: 5px 0; font-weight: 600;">${formatearFecha(pedido.fecha_pedido)}</p>
                </div>
                <div>
                    <small style="color: #6b7280; font-size: 12px;">Fecha Entrega</small>
                    <p style="margin: 5px 0; font-weight: 600;">
                        ${pedido.fecha_entrega_estimada ? formatearFecha(pedido.fecha_entrega_estimada) : 'Sin definir'}
                    </p>
                </div>
                <div>
                    <small style="color: #6b7280; font-size: 12px;">Solicitado por</small>
                    <p style="margin: 5px 0; font-weight: 600;">${escapeHtml(pedido.usuario_nombre)} ${escapeHtml(pedido.usuario_apellidos || '')}</p>
                </div>
                <div>
                    <small style="color: #6b7280; font-size: 12px;">Estado Actual</small>
                    <p style="margin: 5px 0;">
                        <span class="badge badge-${estiloEstado.clase}">
                            <i class="fas fa-${estiloEstado.icono}"></i>
                            ${escapeHtml(pedido.estado_nombre)}
                        </span>
                    </p>
                </div>
            </div>
            ${pedido.observaciones ? `
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                    <small style="color: #6b7280; font-size: 12px;">Observaciones</small>
                    <p style="margin: 5px 0;">${escapeHtml(pedido.observaciones)}</p>
                </div>
            ` : ''}
        </div>
        
        <!-- Detalle de Productos -->
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="background: #f3f4f6; padding: 15px; border-bottom: 1px solid #e5e7eb;">
                <h4 style="margin: 0; color: #667eea;">
                    <i class="fas fa-boxes"></i> Productos del Pedido
                </h4>
            </div>
            <div class="table-responsive">
                <table class="data-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th style="text-align: right;">Precio Unit.</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detalles.map(det => `
                            <tr>
                                <td><strong>${escapeHtml(det.codigo_producto)}</strong></td>
                                <td>${escapeHtml(det.nombre_producto)}</td>
                                <td>${escapeHtml(det.unidad_medida)}</td>
                                <td style="text-align: center;"><strong>${det.cantidad}</strong></td>
                                <td style="text-align: right;">S/ ${parseFloat(det.precio_unitario).toFixed(2)}</td>
                                <td style="text-align: right;"><strong>S/ ${parseFloat(det.subtotal).toFixed(2)}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f9fafb;">
                            <td colspan="5" style="text-align: right;"><strong>Subtotal:</strong></td>
                            <td style="text-align: right;"><strong>S/ ${parseFloat(pedido.subtotal).toFixed(2)}</strong></td>
                        </tr>
                        <tr style="background: #f9fafb;">
                            <td colspan="5" style="text-align: right;"><strong>IGV (18%):</strong></td>
                            <td style="text-align: right;"><strong>S/ ${parseFloat(pedido.igv).toFixed(2)}</strong></td>
                        </tr>
                        <tr style="background: #f3f4f6;">
                            <td colspan="5" style="text-align: right; font-size: 18px;"><strong>TOTAL:</strong></td>
                            <td style="text-align: right; font-size: 18px; color: #059669;">
                                <strong>S/ ${parseFloat(pedido.total).toFixed(2)}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    `;
    
    container.innerHTML = htmlDetalles;
}

function cerrarModalDetalle() {
    cerrarModal('modalDetalle');
}

// ===== CAMBIAR ESTADO DEL PEDIDO =====
async function cambiarEstadoPedido(idPedido) {
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    
    if (!nuevoEstado) {
        mostrarAlerta('Debe seleccionar un estado', 'warning');
        return;
    }
    
    const estadoTexto = document.getElementById('nuevoEstado').options[document.getElementById('nuevoEstado').selectedIndex].text;
    if (!confirm(`¿Está seguro de cambiar el estado del pedido a "${estadoTexto}"?`)) {
        return;
    }
    
    try {
        mostrarLoading('Actualizando estado...');
        
        const response = await fetch('controller/cPedido.php?action=cambiarEstado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idPedido: idPedido,
                nuevoEstado: nuevoEstado
            })
        });
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalDetalle();
            cargarPedidos();
        } else {
            mostrarAlerta(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cambiar estado', 'danger');
    }
}

// ===== IMPRIMIR DETALLE =====
function imprimirDetalle() {
    const contenido = document.getElementById('detalleContent').innerHTML;
    const ventana = window.open('', '_blank');
    
    ventana.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Detalle de Pedido</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="assets/css/main.css">
            <style>
                body { padding: 20px; }
                @media print {
                    .btn, button { display: none !important; }
                    .estado-timeline { display: none !important; }
                }
            </style>
        </head>
        <body>
            <h2 style="text-align: center; margin-bottom: 30px;">
                <i class="fas fa-file-invoice"></i> Detalle de Pedido
            </h2>
            ${contenido}
            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        </body>
        </html>
    `);
    
    ventana.document.close();
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaPedido');
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
            <button class="btn-close" onclick="cerrarAlertaPedido()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaPedido();
    }, 5000);
}

function cerrarAlertaPedido() {
    const alertaDiv = document.getElementById('alertaPedido');
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
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
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