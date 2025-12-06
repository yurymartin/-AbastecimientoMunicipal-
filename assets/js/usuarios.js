let modoEdicion = false;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('tablaUsuarios')) {
        inicializarModuloUsuarios();
        console.log('✅ Módulo de usuarios inicializado');
    }
});

function inicializarModuloUsuarios() {
    const buscarInput = document.getElementById('buscarUsuario');
    if (buscarInput) {
        buscarInput.addEventListener('keyup', buscarUsuarios);
    }
    
    const filtroRol = document.getElementById('filtroRol');
    if (filtroRol) {
        filtroRol.addEventListener('change', filtrarPorRol);
    }
    
    inicializarValidaciones();
}


function buscarUsuarios() {
    const filtro = this.value.toUpperCase();
    const tabla = document.getElementById('tablaUsuarios');
    const tbody = tabla.querySelector('tbody');
    const filas = tbody.getElementsByTagName('tr');
    let resultados = 0;
    
    for (let i = 0; i < filas.length; i++) {
        if (filas[i].cells.length === 1) continue;
        
        let mostrar = false;
        const celdas = filas[i].getElementsByTagName('td');
        
        for (let j = 0; j < celdas.length - 1; j++) {
            const celda = celdas[j];
            if (celda && celda.textContent.toUpperCase().indexOf(filtro) > -1) {
                mostrar = true;
                resultados++;
                break;
            }
        }
        
        filas[i].style.display = mostrar ? '' : 'none';
    }

    mostrarMensajeBusqueda(tabla, resultados, filtro);
}

function filtrarPorRol() {
    const filtroRol = this.value;
    const tabla = document.getElementById('tablaUsuarios');
    const tbody = tabla.querySelector('tbody');
    const filas = tbody.getElementsByTagName('tr');
    
    for (let i = 0; i < filas.length; i++) {
        if (filas[i].cells.length === 1) continue;
        
        if (filtroRol === '') {
            filas[i].style.display = '';
        } else {
            const rolFila = filas[i].getAttribute('data-rol');
            filas[i].style.display = rolFila === filtroRol ? '' : 'none';
        }
    }
}

function mostrarMensajeBusqueda(tabla, resultados, filtro) {
    const mensajeAnterior = tabla.parentElement.querySelector('.mensaje-busqueda');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }
    
    if (resultados === 0 && filtro.length > 0) {
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje-busqueda';
        mensaje.innerHTML = `
            <i class="fas fa-search"></i>
            <p>No se encontraron resultados para "${filtro}"</p>
        `;
        tabla.parentElement.appendChild(mensaje);
    }
}

function abrirModalNuevo() {
    modoEdicion = false;
    
    document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
    
    const form = document.getElementById('formUsuario');
    if (form) form.reset();
    document.getElementById('idUsuario').value = '';
    
    const passwordInput = document.getElementById('password');
    passwordInput.required = true;
    
    document.getElementById('labelPassword').innerHTML = 'Contraseña *';
    const helpPassword = document.getElementById('helpPassword');
    if (helpPassword) {
        helpPassword.textContent = 'Mínimo 6 caracteres';
    }
    
    if (typeof mostrarModal === 'function') {
        mostrarModal('modalUsuario');
    } else {
        abrirModalLocal('modalUsuario');
    }
}

function cerrarModalUsuario() {
    cerrarModalLocal('modalUsuario');
}

function cerrarModalConfirmar() {
    cerrarModalLocal('modalConfirmar');
}

function abrirModalLocal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.offsetHeight;
        modal.style.opacity = '1';
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalLocal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            const form = modal.querySelector('form');
            if (form) form.reset();
        }, 300);
    }
}

window.cerrarModal = function(modalId) {
    cerrarModalLocal(modalId);
};

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        cerrarModalLocal(e.target.id);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modales = ['modalUsuario', 'modalConfirmar'];
        modales.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && modal.style.display === 'flex') {
                cerrarModalLocal(modalId);
            }
        });
    }
});


function editarUsuario(id) {
    modoEdicion = true;
    
    if (typeof mostrarLoading === 'function') {
        mostrarLoading('Cargando datos del usuario...');
    }
    
    fetch(`controller/cUsuario.php?action=obtener&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('Respuesta recibida:', text);
                throw new Error('La respuesta no es JSON válido');
            }
        })
        .then(data => {
            if (typeof ocultarLoading === 'function') {
                ocultarLoading();
            }
            
            if (data.success) {
                const usuario = data.data;
                
                document.getElementById('modalTitulo').textContent = 'Editar Usuario';
                
                document.getElementById('idUsuario').value = usuario.idUsuario;
                document.getElementById('username').value = usuario.username;
                document.getElementById('nombres').value = usuario.nombres;
                document.getElementById('apellidos').value = usuario.apellidos;
                document.getElementById('dni').value = usuario.dni;
                document.getElementById('email').value = usuario.email;
                document.getElementById('rolId').value = usuario.rolId;
                document.getElementById('estado').value = usuario.estado;
                
                const passwordInput = document.getElementById('password');
                passwordInput.required = false;
                passwordInput.value = '';
                
                document.getElementById('labelPassword').innerHTML = 
                    'Contraseña <small class="text-muted">(dejar vacío para mantener)</small>';
                const helpPassword = document.getElementById('helpPassword');
                if (helpPassword) {
                    helpPassword.textContent = 'Solo completar si desea cambiarla';
                }
                
                if (typeof mostrarModal === 'function') {
                    mostrarModal('modalUsuario');
                } else {
                    abrirModalLocal('modalUsuario');
                }
            } else {
                mostrarAlerta(data.message || 'Error al cargar usuario', 'danger');
            }
        })
        .catch(error => {
            if (typeof ocultarLoading === 'function') {
                ocultarLoading();
            }
            console.error('Error:', error);
            mostrarAlerta('Error al cargar los datos del usuario', 'danger');
        });
}

function guardarUsuario(event) {
    event.preventDefault();
    
    const form = document.getElementById('formUsuario');
    const formData = new FormData(form);
    formData.append('action', 'guardar');
    
    const dni = formData.get('dni');
    if (!/^\d{8}$/.test(dni)) {
        mostrarAlerta('El DNI debe tener exactamente 8 dígitos', 'warning');
        document.getElementById('dni').focus();
        return;
    }
    
    const password = formData.get('password');
    const idUsuario = formData.get('idUsuario');
    if (!idUsuario && password.length < 6) {
        mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'warning');
        document.getElementById('password').focus();
        return;
    }
    
    const btnGuardar = document.getElementById('btnGuardar');
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    if (typeof mostrarLoading === 'function') {
        mostrarLoading('Guardando usuario...');
    }
    
    fetch('controller/cUsuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('Respuesta recibida:', text);
            throw new Error('La respuesta no es JSON válido');
        }
    })
    .then(data => {
        if (typeof ocultarLoading === 'function') {
            ocultarLoading();
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            cerrarModalLocal('modalUsuario');
            
            setTimeout(() => {
                window.location.href = '?page=usuarios';
            }, 1500);
        } else {
            mostrarAlerta(data.message || 'Error al guardar usuario', 'danger');
        }
    })
    .catch(error => {
        if (typeof ocultarLoading === 'function') {
            ocultarLoading();
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
        console.error('Error:', error);
        mostrarAlerta('Error al guardar el usuario', 'danger');
    });
}

function confirmarEliminar(id, username) {
    document.getElementById('usuarioEliminar').textContent = username;
    document.getElementById('idUsuarioEliminar').value = id;
    
    if (typeof mostrarModal === 'function') {
        mostrarModal('modalConfirmar');
    } else {
        abrirModalLocal('modalConfirmar');
    }
}

function eliminarUsuario() {
    const id = document.getElementById('idUsuarioEliminar').value;
    
    cerrarModalLocal('modalConfirmar');
    
    if (typeof mostrarLoading === 'function') {
        mostrarLoading('Desactivando usuario...');
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);
    
    fetch('controller/cUsuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('Respuesta recibida:', text);
            throw new Error('La respuesta no es JSON válido');
        }
    })
    .then(data => {
        if (typeof ocultarLoading === 'function') {
            ocultarLoading();
        }
        
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            
            setTimeout(() => {
                window.location.href = '?page=usuarios';
            }, 1500);
        } else {
            mostrarAlerta(data.message || 'Error al eliminar usuario', 'danger');
        }
    })
    .catch(error => {
        if (typeof ocultarLoading === 'function') {
            ocultarLoading();
        }
        console.error('Error:', error);
        mostrarAlerta('Error al eliminar el usuario', 'danger');
    });
}


function inicializarValidaciones() {
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
        });
        
        dniInput.addEventListener('blur', function() {
            if (this.value && this.value.length !== 8) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\s/g, '');
        });
    }
    
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                this.style.borderColor = '#f59e0b';
            } else {
                this.style.borderColor = '';
            }
        });
    }
}


function mostrarAlerta(mensaje, tipo) {
    if (typeof mostrarNotificacion === 'function') {
        mostrarNotificacion(mensaje, tipo);
        return;
    }
    
    const alerta = document.getElementById('mensajeAlerta');
    const mensajeTexto = document.getElementById('mensajeTexto');
    
    if (!alerta || !mensajeTexto) {
        console.error('Elementos de alerta no encontrados');
        return;
    }
    
    alerta.className = 'alert';
    
    const iconos = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    alerta.classList.add(`alert-${tipo}`);
    const icono = alerta.querySelector('i');
    if (icono) {
        icono.className = `fas ${iconos[tipo] || iconos.info}`;
    }
    
    mensajeTexto.textContent = mensaje;
    alerta.style.display = 'flex';
    alerta.style.opacity = '1';
    
    if (!alerta.querySelector('.btn-close')) {
        const btnClose = document.createElement('button');
        btnClose.className = 'btn-close';
        btnClose.innerHTML = '<i class="fas fa-times"></i>';
        btnClose.onclick = function() {
            alerta.style.opacity = '0';
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 300);
        };
        alerta.appendChild(btnClose);
    }
    
    setTimeout(() => {
        alerta.style.opacity = '0';
        setTimeout(() => {
            alerta.style.display = 'none';
        }, 300);
    }, 5000);
}


function limpiarFiltros() {
    document.getElementById('buscarUsuario').value = '';
    document.getElementById('filtroRol').value = '';
    
    const tabla = document.getElementById('tablaUsuarios');
    const tbody = tabla.querySelector('tbody');
    const filas = tbody.getElementsByTagName('tr');
    
    for (let i = 0; i < filas.length; i++) {
        filas[i].style.display = '';
    }
    
    const mensaje = tabla.parentElement.querySelector('.mensaje-busqueda');
    if (mensaje) {
        mensaje.remove();
    }
}

console.log('Módulo de usuarios cargado correctamente');


function cambiarPagina(pagina) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', 'usuarios');
    urlParams.set('pagina', pagina);
    
    window.location.href = '?' + urlParams.toString();
}

console.log('Funciones de paginación cargadas');