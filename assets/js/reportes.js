// ===== VARIABLES GLOBALES =====
let chartPedidosEstado, chartGastosTipo, chartPedidosMes, chartTopProveedores, chartTopProductos;

const colores = {
    azul: '#3b82f6',
    verde: '#10b981',
    amarillo: '#f59e0b',
    rojo: '#ef4444',
    morado: '#8b5cf6',
    rosa: '#ec4899',
    cyan: '#06b6d4',
    naranja: '#f97316'
};

const coloresArray = [
    colores.azul, colores.verde, colores.amarillo, colores.rojo,
    colores.morado, colores.rosa, colores.cyan, colores.naranja
];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarEstadisticas();
});

// ===== CARGAR ESTADÍSTICAS =====
async function cargarEstadisticas() {
    try {
        mostrarLoading('Cargando estadísticas...');
        
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        
        if (new Date(fechaFin) < new Date(fechaInicio)) {
            mostrarAlerta('La fecha final no puede ser menor a la fecha inicial', 'warning');
            ocultarLoading();
            return;
        }
        
        const response = await fetch(
            `controller/cReportes.php?action=estadisticas&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`
        );
        
        const data = await response.json();
        
        ocultarLoading();
        
        if (data.success) {
            actualizarResumen(data.resumen);
            renderizarGraficos(data);
        } else {
            mostrarAlerta('Error al cargar estadísticas: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarAlerta('Error de conexión al cargar estadísticas', 'danger');
    }
}

// ===== ACTUALIZAR RESUMEN =====
function actualizarResumen(resumen) {
    document.getElementById('totalPedidos').textContent = resumen.total_pedidos;
    document.getElementById('montoPedidos').textContent = 
        'S/ ' + formatearNumero(resumen.monto_total_pedidos, 2);
    
    document.getElementById('totalProveedores').textContent = resumen.total_proveedores;
    
    document.getElementById('totalGastos').textContent = resumen.total_gastos;
    document.getElementById('montoGastos').textContent = 
        'S/ ' + formatearNumero(resumen.monto_total_gastos, 2);
    
    document.getElementById('totalProductos').textContent = resumen.total_productos_pedidos;
}

// ===== RENDERIZAR GRÁFICOS =====
function renderizarGraficos(data) {
    destruirGraficos();
    
    if (data.pedidos_estado && data.pedidos_estado.length > 0) {
        const ctxEstado = document.getElementById('chartPedidosEstado').getContext('2d');
        chartPedidosEstado = new Chart(ctxEstado, {
            type: 'pie',
            data: {
                labels: data.pedidos_estado.map(item => item.estado),
                datasets: [{
                    data: data.pedidos_estado.map(item => parseInt(item.cantidad)),
                    backgroundColor: coloresArray
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const dataset = context.dataset.data;
                                const total = dataset.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    if (data.gastos_tipo && data.gastos_tipo.length > 0) {
        const ctxGastos = document.getElementById('chartGastosTipo').getContext('2d');
        chartGastosTipo = new Chart(ctxGastos, {
            type: 'doughnut',
            data: {
                labels: data.gastos_tipo.map(item => item.tipo_gasto),
                datasets: [{
                    data: data.gastos_tipo.map(item => parseFloat(item.monto_total)),
                    backgroundColor: coloresArray
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return `${label}: S/ ${value.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    if (data.pedidos_mes && data.pedidos_mes.length > 0) {
        const ctxMes = document.getElementById('chartPedidosMes').getContext('2d');
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        const datosMeses = Array(12).fill(0);
        const montosMeses = Array(12).fill(0);
        
        data.pedidos_mes.forEach(item => {
            datosMeses[item.mes - 1] = parseInt(item.cantidad);
            montosMeses[item.mes - 1] = parseFloat(item.monto_total);
        });
        
        chartPedidosMes = new Chart(ctxMes, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Cantidad de Pedidos',
                    data: datosMeses,
                    backgroundColor: colores.azul,
                    yAxisID: 'y'
                }, {
                    label: 'Monto Total (S/)',
                    data: montosMeses,
                    backgroundColor: colores.verde,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Cantidad'
                        },
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Monto (S/)'
                        },
                        grid: {
                            drawOnChartArea: false
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    if (data.top_proveedores && data.top_proveedores.length > 0) {
        const ctxProveedores = document.getElementById('chartTopProveedores').getContext('2d');
        chartTopProveedores = new Chart(ctxProveedores, {
            type: 'bar',
            data: {
                labels: data.top_proveedores.map(item => 
                    item.razon_social.length > 30 ? 
                    item.razon_social.substring(0, 30) + '...' : 
                    item.razon_social
                ),
                datasets: [{
                    label: 'Monto Total (S/)',
                    data: data.top_proveedores.map(item => parseFloat(item.monto_total)),
                    backgroundColor: colores.azul
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Monto: S/ ${context.parsed.x.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto Total (S/)'
                        }
                    }
                }
            }
        });
    }
    
    if (data.top_productos && data.top_productos.length > 0) {
        const ctxProductos = document.getElementById('chartTopProductos').getContext('2d');
        chartTopProductos = new Chart(ctxProductos, {
            type: 'bar',
            data: {
                labels: data.top_productos.map(item => 
                    item.nombre_producto.length > 30 ? 
                    item.nombre_producto.substring(0, 30) + '...' : 
                    item.nombre_producto
                ),
                datasets: [{
                    label: 'Cantidad Total Pedida',
                    data: data.top_productos.map(item => parseInt(item.cantidad_total)),
                    backgroundColor: colores.verde
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Cantidad: ${context.parsed.x}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad Total'
                        }
                    }
                }
            }
        });
    }
}

// ===== DESTRUIR GRÁFICOS =====
function destruirGraficos() {
    if (chartPedidosEstado) {
        chartPedidosEstado.destroy();
        chartPedidosEstado = null;
    }
    if (chartGastosTipo) {
        chartGastosTipo.destroy();
        chartGastosTipo = null;
    }
    if (chartPedidosMes) {
        chartPedidosMes.destroy();
        chartPedidosMes = null;
    }
    if (chartTopProveedores) {
        chartTopProveedores.destroy();
        chartTopProveedores = null;
    }
    if (chartTopProductos) {
        chartTopProductos.destroy();
        chartTopProductos = null;
    }
}

// ===== RESETEAR FILTROS =====
function resetearFiltros() {
    document.getElementById('fechaInicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1)
        .toISOString().split('T')[0];
    document.getElementById('fechaFin').value = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
        .toISOString().split('T')[0];
    cargarEstadisticas();
}

// ===== EXPORTAR REPORTES =====
function exportarReporte(tipo, formato) {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    let url;
    if (formato === 'excel') {
        url = `controller/cReportes.php?action=exportar_excel&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        
        mostrarLoading(`Generando reporte Excel...`);
        
        window.location.href = url;
        
        setTimeout(() => {
            ocultarLoading();
            mostrarAlerta(`Reporte de ${tipo} descargado exitosamente`, 'success');
        }, 1500);
    } else {
        url = `controller/cReportes.php?action=exportar_pdf&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        
        mostrarLoading(`Abriendo vista previa de PDF...`);
        
        const ventana = window.open(url, '_blank');
        
        if (!ventana) {
            mostrarAlerta('Por favor permita las ventanas emergentes para ver el PDF', 'warning');
        }
        
        setTimeout(() => {
            ocultarLoading();
            mostrarAlerta(`Vista previa del reporte abierta. Use Ctrl+P para imprimir o guardar como PDF`, 'info');
        }, 1000);
    }
}

// ===== ALERTAS =====
function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.getElementById('alertaReportes');
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
            <button class="btn-close" onclick="cerrarAlertaReportes()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    setTimeout(() => {
        cerrarAlertaReportes();
    }, 5000);
}

function cerrarAlertaReportes() {
    const alertaDiv = document.getElementById('alertaReportes');
    if (alertaDiv) {
        alertaDiv.innerHTML = '';
    }
}

// ===== UTILIDADES =====
function formatearNumero(numero, decimales = 2) {
    return parseFloat(numero).toFixed(decimales).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}