// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initTopbar();
    initMenuActive();
    mostrarBienvenida();
    autoHideAlerts();
    initPageTransitions();
    
    setTimeout(() => {
        ocultarLoading();
    }, 100);
});

// ===== NOTIFICACIÓN DE BIENVENIDA =====
function mostrarBienvenida() {
    if (sessionStorage.getItem('welcomeShown')) {
        return;
    }
    
    const userName = document.querySelector('.user-name')?.textContent || 'Usuario';
    
    const welcomeDiv = document.createElement('div');
    welcomeDiv.className = 'welcome-notification';
    welcomeDiv.innerHTML = `
        <i class="fas fa-hand-wave"></i>
        <div class="content">
            <h4>¡Bienvenido!</h4>
            <p>${userName}</p>
        </div>
        <button class="close-btn" onclick="cerrarBienvenida(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(welcomeDiv);
    
    setTimeout(() => {
        cerrarBienvenida(welcomeDiv.querySelector('.close-btn'));
    }, 4000);
    
    sessionStorage.setItem('welcomeShown', 'true');
}

function cerrarBienvenida(btn) {
    const notification = btn.closest('.welcome-notification');
    if (notification) {
        notification.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// ===== LOADING OVERLAY =====
function mostrarLoading(mensaje = 'Cargando...') {
    ocultarLoading();
    
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading-overlay';
    loadingDiv.id = 'loadingOverlay';
    loadingDiv.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>${mensaje}</p>
        </div>
    `;
    
    document.body.appendChild(loadingDiv);
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        ocultarLoading();
    }, 5000);
}

function ocultarLoading() {
    const loading = document.getElementById('loadingOverlay');
    if (loading) {
        loading.style.opacity = '0';
        loading.style.transition = 'opacity 0.2s ease';
        setTimeout(() => {
            loading.remove();
            document.body.style.overflow = 'auto';
        }, 200);
    }
}

// ===== SIDEBAR =====
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    
    if (!sidebar) return;
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }
    
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && 
                !mobileToggle?.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
}

// ===== TOPBAR =====
function initTopbar() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && userDropdown.classList.contains('show')) {
                userDropdown.classList.remove('show');
            }
        });
    }
}

// ===== MENÚ ACTIVO =====
function initMenuActive() {
    const menuItems = document.querySelectorAll('.menu-item');
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    
    menuItems.forEach(item => {
        const page = item.getAttribute('data-page');
        if (page === currentPage) {
            menuItems.forEach(mi => mi.classList.remove('active'));
            item.classList.add('active');
        }
        
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                e.preventDefault();
                mostrarLoading('Cargando página...');
                
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        });
    });
    
    actualizarTituloPagina();
}

function actualizarTituloPagina() {
    const pageTitle = document.getElementById('pageTitle');
    if (pageTitle) {
        const activeItem = document.querySelector('.menu-item.active');
        if (activeItem) {
            const title = activeItem.querySelector('span')?.textContent || 'Dashboard';
            pageTitle.textContent = title;
            
            document.title = `${title} - Sistema de Abastecimiento`;
        }
    }
}

// ===== TRANSICIONES DE PÁGINA =====
function initPageTransitions() {
    if (document.readyState === 'complete') {
        ocultarLoading();
    } else {
        window.addEventListener('load', function() {
            ocultarLoading();
        });
    }
}

// ===== ALERTAS =====
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.querySelector('.close-alert')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'close-alert';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.style.cssText = `
                background: none;
                border: none;
                cursor: pointer;
                margin-left: auto;
                padding: 4px 8px;
                opacity: 0.7;
                transition: opacity 0.3s;
            `;
            closeBtn.addEventListener('click', () => cerrarAlerta(alert));
            closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
            closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
            alert.appendChild(closeBtn);
        }
        
        setTimeout(() => {
            cerrarAlerta(alert);
        }, 5000);
    });
}

function cerrarAlerta(alert) {
    if (!alert) return;
    
    alert.style.opacity = '0';
    alert.style.transform = 'translateX(100%)';
    alert.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        alert.remove();
    }, 300);
}

// ===== MODAL =====
function mostrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.opacity = '0';
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
        }, 200);
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        const modalId = e.target.id;
        cerrarModal(modalId);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'flex') {
                cerrarModal(modal.id);
            }
        });
    }
});

// ===== BÚSQUEDA EN TABLAS =====
function buscarEnTabla(inputId, tablaId) {
    const input = document.getElementById(inputId);
    const tabla = document.getElementById(tablaId);
    
    if (!input || !tabla) return;
    
    input.addEventListener('keyup', function() {
        const filtro = this.value.toUpperCase();
        const tbody = tabla.querySelector('tbody');
        const filas = tbody ? tbody.getElementsByTagName('tr') : tabla.getElementsByTagName('tr');
        let resultados = 0;
        
        for (let i = 0; i < filas.length; i++) {
            let mostrar = false;
            const celdas = filas[i].getElementsByTagName('td');
            
            for (let j = 0; j < celdas.length; j++) {
                const celda = celdas[j];
                if (celda && celda.textContent.toUpperCase().indexOf(filtro) > -1) {
                    mostrar = true;
                    resultados++;
                    break;
                }
            }
            
            filas[i].style.display = mostrar ? '' : 'none';
            filas[i].style.transition = 'all 0.3s ease';
        }
        
        mostrarMensajeBusqueda(tabla, resultados, filtro);
    });
}

function mostrarMensajeBusqueda(tabla, resultados, filtro) {
    const mensajeAnterior = tabla.parentElement.querySelector('.mensaje-busqueda');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }
    
    if (resultados === 0 && filtro.length > 0) {
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje-busqueda';
        mensaje.style.cssText = `
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        `;
        mensaje.innerHTML = `
            <i class="fas fa-search"></i>
            No se encontraron resultados para "${filtro}"
        `;
        tabla.parentElement.appendChild(mensaje);
    }
}

// ===== VALIDACIONES =====
function validarDNI(dni) {
    const dniRegex = /^\d{8}$/;
    return dniRegex.test(dni);
}

function validarRUC(ruc) {
    const rucRegex = /^\d{11}$/;
    return rucRegex.test(ruc);
}

function validarEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validarTelefono(telefono) {
    const telefonoRegex = /^\d{9}$/;
    return telefonoRegex.test(telefono);
}

function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let valido = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            valido = false;
            input.style.borderColor = 'var(--danger)';
            
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            }, { once: true });
        }
    });
    
    if (!valido) {
        mostrarNotificacion('Por favor complete todos los campos requeridos', 'danger');
    }
    
    return valido;
}

// ===== CONFIRMACIONES =====
function confirmarEliminacion(mensaje) {
    return confirm(mensaje || '¿Está seguro de eliminar este registro?');
}

function confirmarAccion(mensaje, callback) {
    if (confirm(mensaje)) {
        mostrarLoading('Procesando...');
        if (typeof callback === 'function') {
            callback();
        }
    }
}

// ===== NOTIFICACIONES =====
function mostrarNotificacion(mensaje, tipo = 'success') {
    const iconos = {
        success: 'check-circle',
        danger: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo}`;
    notificacion.style.cssText = `
        position: fixed;
        top: 90px;
        right: 30px;
        z-index: 9999;
        max-width: 400px;
        animation: slideInRight 0.5s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notificacion.innerHTML = `
        <i class="fas fa-${iconos[tipo] || iconos.info}"></i>
        ${mensaje}
        <button class="close-alert" style="
            background: none;
            border: none;
            cursor: pointer;
            margin-left: auto;
            padding: 4px 8px;
            opacity: 0.7;
        ">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notificacion);
    
    notificacion.querySelector('.close-alert').addEventListener('click', () => {
        cerrarAlerta(notificacion);
    });
    
    setTimeout(() => {
        cerrarAlerta(notificacion);
    }, 5000);
}

// ===== FORMATEO =====
function formatearMoneda(numero) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(numero);
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function formatearFechaHora(fecha) {
    return new Date(fecha).toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-PE').format(numero);
}

// ===== AJAX HELPERS =====
async function enviarFormulario(url, formData) {
    mostrarLoading('Enviando datos...');
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        ocultarLoading();
        
        return data;
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarNotificacion('Error de conexión. Por favor intente nuevamente.', 'danger');
        return { success: false, message: 'Error de conexión' };
    }
}

async function obtenerDatos(url) {
    mostrarLoading('Cargando datos...');
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        ocultarLoading();
        
        return data;
    } catch (error) {
        console.error('Error:', error);
        ocultarLoading();
        mostrarNotificacion('Error al cargar los datos', 'danger');
        return null;
    }
}

// ===== UPLOAD DE ARCHIVOS =====
function handleFileUpload(inputId, callback) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        mostrarLoading('Subiendo archivo...');
        
        setTimeout(() => {
            ocultarLoading();
            if (typeof callback === 'function') {
                callback(file);
            }
            mostrarNotificacion('Archivo subido correctamente', 'success');
        }, 1000);
    });
}

// ===== UTILIDADES =====
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarNotificacion('Copiado al portapapeles', 'success');
    }).catch(err => {
        console.error('Error al copiar:', err);
        mostrarNotificacion('Error al copiar', 'danger');
    });
}

function descargarArchivo(url, nombreArchivo) {
    mostrarLoading('Descargando archivo...');
    
    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = nombreArchivo;
            link.click();
            ocultarLoading();
            mostrarNotificacion('Archivo descargado correctamente', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            ocultarLoading();
            mostrarNotificacion('Error al descargar el archivo', 'danger');
        });
}

// ===== DEBOUNCE =====
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

// ===== SCROLL SUAVE =====
function scrollSuave(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// ===== TOOLTIP SIMPLE =====
function initTooltips() {
    const elementsWithTitle = document.querySelectorAll('[title]');
    elementsWithTitle.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            const tooltip = document.createElement('div');
            tooltip.className = 'simple-tooltip';
            tooltip.textContent = title;
            tooltip.style.cssText = `
                position: absolute;
                background: #1a1d2e;
                color: white;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                z-index: 10000;
                pointer-events: none;
                white-space: nowrap;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, { once: true });
        });
    });
}

document.addEventListener('DOMContentLoaded', initTooltips);