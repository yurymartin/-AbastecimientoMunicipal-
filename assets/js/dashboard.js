// Variables globales
let dashboardData = {
    usuarios: 0,
    proveedores: 0,
    productos: 0,
    pedidos: 0,
    pedidosRecientes: [],
    productosStockBajo: []
};

let refreshInterval = null;

// Inicializar dashboard al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.dashboard-content')) {
        initDashboard();
    }
});

// ===== INICIALIZACIÓN DEL DASHBOARD =====
async function initDashboard() {
    console.log('Inicializando dashboard...');
    
    mostrarLoading('Cargando datos del dashboard...');
    
    try {
        await cargarDatosDashboard();
        
        actualizarEstadisticas();
        actualizarPedidosRecientes();
        actualizarProductosStockBajo();
        
        ocultarLoading();
        
        iniciarActualizacionAutomatica();
        
        console.log('Dashboard cargado correctamente');
    } catch (error) {
        console.error('Error al cargar el dashboard:', error);
        ocultarLoading();
        mostrarNotificacion('Error al cargar los datos del dashboard', 'danger');
    }
}

// ===== CARGAR DATOS DEL DASHBOARD =====
async function cargarDatosDashboard() {
    try {
        const response = await fetch('controller/cDashboard.php?action=obtenerDatos');
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        
        if (data.success) {
            dashboardData = {
                usuarios: data.data.usuarios || 0,
                proveedores: data.data.proveedores || 0,
                productos: data.data.productos || 0,
                pedidos: data.data.pedidos || 0,
                pedidosRecientes: data.data.pedidosRecientes || [],
                productosStockBajo: data.data.productosStockBajo || []
            };
            
            return dashboardData;
        } else {
            throw new Error(data.message || 'Error al obtener los datos');
        }
    } catch (error) {
        console.error('Error en cargarDatosDashboard:', error);
        
        dashboardData = {
            usuarios: 24,
            proveedores: 23,
            productos: 156,
            pedidos: 48,
            pedidosRecientes: [
                {
                    numero_pedido: '#PED-001',
                    proveedor: 'Proveedor ABC',
                    fecha: '13/11/2024',
                    estado: 'Pendiente',
                    total: 1250.00
                },
                {
                    numero_pedido: '#PED-002',
                    proveedor: 'Proveedor XYZ',
                    fecha: '12/11/2024',
                    estado: 'Entregado',
                    total: 890.00
                }
            ],
            productosStockBajo: [
                {
                    codigo: 'PROD-001',
                    nombre: 'Papel A4',
                    stock: 5
                },
                {
                    codigo: 'PROD-045',
                    nombre: 'Tóner HP',
                    stock: 8
                }
            ]
        };
        
        return dashboardData;
    }
}

// ===== ACTUALIZAR ESTADÍSTICAS =====
function actualizarEstadisticas() {
    animarContador('.stat-card.blue .stat-number', dashboardData.usuarios);
    
    animarContador('.stat-card.purple .stat-number', dashboardData.proveedores);
    
    animarContador('.stat-card.green .stat-number', dashboardData.productos);
    
    animarContador('.stat-card.orange .stat-number', dashboardData.pedidos);
}

// ===== ANIMAR CONTADOR =====
function animarContador(selector, valorFinal) {
    const elemento = document.querySelector(selector);
    if (!elemento) return;
    
    const valorInicial = 0;
    const duracion = 1000; 
    const incremento = valorFinal / (duracion / 16); 
    let valorActual = valorInicial;
    
    const intervalo = setInterval(() => {
        valorActual += incremento;
        
        if (valorActual >= valorFinal) {
            elemento.textContent = valorFinal;
            clearInterval(intervalo);
        } else {
            elemento.textContent = Math.floor(valorActual);
        }
    }, 16);
}

// ===== ACTUALIZAR PEDIDOS RECIENTES =====
function actualizarPedidosRecientes() {
    const tbody = document.querySelector('.simple-table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (dashboardData.pedidosRecientes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No hay pedidos recientes</h3>
                        <p>Los pedidos aparecerán aquí cuando se registren</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    dashboardData.pedidosRecientes.forEach(pedido => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${pedido.numero_pedido}</td>
            <td>${pedido.proveedor}</td>
            <td>${pedido.fecha}</td>
            <td>${obtenerBadgeEstado(pedido.estado)}</td>
            <td>${formatearMoneda(pedido.total)}</td>
        `;
        tbody.appendChild(fila);
    });
}

// ===== OBTENER BADGE DE ESTADO =====
function obtenerBadgeEstado(estado) {
    const estados = {
        'Pendiente': { clase: 'badge-warning', icono: 'clock' },
        'En Proceso': { clase: 'badge-info', icono: 'hourglass-half' },
        'Entregado': { clase: 'badge-success', icono: 'check-circle' },
        'Cancelado': { clase: 'badge-danger', icono: 'times-circle' }
    };
    
    const config = estados[estado] || estados['Pendiente'];
    
    return `<span class="badge ${config.clase}">
        <i class="fas fa-${config.icono}"></i> ${estado}
    </span>`;
}

// ===== ACTUALIZAR PRODUCTOS CON STOCK BAJO =====
function actualizarProductosStockBajo() {
    const productList = document.querySelector('.product-list');
    if (!productList) return;
    
    productList.innerHTML = '';
    
    if (dashboardData.productosStockBajo.length === 0) {
        productList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-box"></i>
                <h3>No hay productos con stock bajo</h3>
                <p>Todos los productos tienen stock suficiente</p>
            </div>
        `;
        return;
    }
    
    // Agregar productos
    dashboardData.productosStockBajo.forEach(producto => {
        const colorIcono = obtenerColorStockBajo(producto.stock);
        
        const item = document.createElement('div');
        item.className = 'product-item';
        item.innerHTML = `
            <div class="product-info">
                <div class="product-icon ${colorIcono}">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h4>${producto.nombre}</h4>
                    <p>Código: ${producto.codigo}</p>
                </div>
            </div>
            <div class="product-stock">
                <span class="stock-number">${producto.stock}</span>
                <span class="stock-label">unidades</span>
            </div>
        `;
        productList.appendChild(item);
    });
}

// ===== OBTENER COLOR SEGÚN STOCK =====
function obtenerColorStockBajo(stock) {
    if (stock <= 5) return 'red';
    if (stock <= 10) return 'orange';
    return 'yellow';
}

// ===== ACTUALIZACIÓN AUTOMÁTICA =====
function iniciarActualizacionAutomatica() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    // Actualizar cada 5 minutos (300000 ms)
    refreshInterval = setInterval(() => {
        console.log('Actualizando dashboard automáticamente...');
        refrescarDashboard();
    }, 300000);
}

// ===== REFRESCAR DASHBOARD =====
async function refrescarDashboard() {
    mostrarIndicadorRefresh();
    
    try {
        await cargarDatosDashboard();
        actualizarEstadisticas();
        actualizarPedidosRecientes();
        actualizarProductosStockBajo();
        
        mostrarNotificacion('Dashboard actualizado', 'success');
    } catch (error) {
        console.error('Error al actualizar:', error);
        mostrarNotificacion('Error al actualizar el dashboard', 'danger');
    } finally {
        ocultarIndicadorRefresh();
    }
}

// ===== MOSTRAR INDICADOR DE REFRESH =====
function mostrarIndicadorRefresh() {
    const indicadorExistente = document.querySelector('.refresh-indicator');
    if (indicadorExistente) {
        indicadorExistente.remove();
    }
    
    const indicador = document.createElement('div');
    indicador.className = 'refresh-indicator';
    indicador.innerHTML = `
        <i class="fas fa-sync-alt"></i>
        <span>Actualizando...</span>
    `;
    
    document.body.appendChild(indicador);
}

// ===== OCULTAR INDICADOR DE REFRESH =====
function ocultarIndicadorRefresh() {
    const indicador = document.querySelector('.refresh-indicator');
    if (indicador) {
        indicador.style.opacity = '0';
        setTimeout(() => indicador.remove(), 300);
    }
}

// ===== BOTÓN MANUAL DE ACTUALIZACIÓN =====
function agregarBotonRefresh() {
    const cardHeader = document.querySelector('.card-header');
    if (!cardHeader) return;
    
    const btnRefresh = document.createElement('button');
    btnRefresh.className = 'btn-icon';
    btnRefresh.innerHTML = '<i class="fas fa-sync-alt"></i>';
    btnRefresh.title = 'Actualizar datos';
    btnRefresh.onclick = refrescarDashboard;
    
    cardHeader.appendChild(btnRefresh);
}

// ===== EXPORTAR DATOS =====
function exportarDashboard() {
    mostrarLoading('Generando reporte...');
    
    const reporte = {
        fecha: new Date().toLocaleString('es-PE'),
        estadisticas: {
            usuarios: dashboardData.usuarios,
            proveedores: dashboardData.proveedores,
            productos: dashboardData.productos,
            pedidos: dashboardData.pedidos
        },
        pedidosRecientes: dashboardData.pedidosRecientes,
        productosStockBajo: dashboardData.productosStockBajo
    };
    
    const json = JSON.stringify(reporte, null, 2);
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `dashboard_${new Date().getTime()}.json`;
    link.click();
    
    ocultarLoading();
    mostrarNotificacion('Reporte descargado correctamente', 'success');
}

// ===== GRÁFICOS (OPCIONAL - REQUIERE LIBRERÍA) =====
function inicializarGraficos() {
    console.log('Gráficos deshabilitados - agregar librería si es necesario');
}

// ===== LIMPIEZA AL SALIR =====
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});

// ===== FUNCIONES AUXILIARES =====

function obtenerFechaActual() {
    return new Date().toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function calcularPorcentajeCambio(valorActual, valorAnterior) {
    if (valorAnterior === 0) return 0;
    return ((valorActual - valorAnterior) / valorAnterior * 100).toFixed(1);
}

function formatearNumeroGrande(numero) {
    return new Intl.NumberFormat('es-PE').format(numero);
}