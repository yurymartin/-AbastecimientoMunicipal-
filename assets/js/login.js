// INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    initializeLogin();
    createParticles();
    loadRememberMe();
    initAutoCloseAlerts();
});

// FUNCIÓN PRINCIPAL
function initializeLogin() {
    const form = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const usernameInput = document.getElementById('username');
    const rememberMeCheckbox = document.getElementById('rememberMe');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(passwordInput, this);
        });
    }
    
    if (usernameInput) {
        usernameInput.addEventListener('blur', function() {
            validateField(this, 'username');
        });
        
        usernameInput.addEventListener('input', function() {
            clearFieldError(this, 'username');
        });
        
        usernameInput.addEventListener('keypress', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            validateField(this, 'password');
        });
        
        passwordInput.addEventListener('input', function() {
            clearFieldError(this, 'password');
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                shakeForm();
            } else {
                handleFormSubmit();
                
                if (rememberMeCheckbox && rememberMeCheckbox.checked) {
                    saveRememberMe(usernameInput.value);
                } else {
                    clearRememberMe();
                }
            }
        });
    }
    
    const btnLogin = document.querySelector('.btn-login');
    if (btnLogin) {
        btnLogin.addEventListener('click', createRipple);
    }
    
    initKeyboardNavigation();
}

// TOGGLE CONTRASEÑA
function togglePasswordVisibility(input, button) {
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Ocultar contraseña');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Mostrar contraseña');
    }
    
    icon.style.transform = 'scale(0.8)';
    setTimeout(() => {
        icon.style.transform = 'scale(1)';
    }, 150);
}

// VALIDACIÓN
function validateForm() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    let isValid = true;
    
    if (!validateField(username, 'username')) {
        isValid = false;
    }
    
    if (!validateField(password, 'password')) {
        isValid = false;
    }
    
    return isValid;
}

function validateField(field, fieldName) {
    const value = field.value.trim();
    const errorElement = document.getElementById(fieldName + 'Error');
    let errorMessage = '';
    
    if (!value) {
        errorMessage = 'Este campo es obligatorio';
    } else if (fieldName === 'username') {
        if (value.length < 3) {
            errorMessage = 'El usuario debe tener al menos 3 caracteres';
        } else if (!/^[a-zA-Z0-9._-]+$/.test(value)) {
            errorMessage = 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos';
        }
    } else if (fieldName === 'password' && value.length < 4) {
        errorMessage = 'La contraseña debe tener al menos 4 caracteres';
    }
    
    if (errorMessage) {
        showFieldError(field, errorElement, errorMessage);
        return false;
    } else {
        clearFieldError(field, fieldName);
        return true;
    }
}

function showFieldError(field, errorElement, message) {
    field.classList.add('error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearFieldError(field, fieldName) {
    const errorElement = document.getElementById(fieldName + 'Error');
    field.classList.remove('error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
}

// ANIMACIONES
function shakeForm() {
    const card = document.querySelector('.login-card');
    if (card) {
        card.classList.add('shake');
        setTimeout(() => {
            card.classList.remove('shake');
        }, 600);
    }
}

function handleFormSubmit() {
    const button = document.getElementById('btnLogin');
    if (!button) return;
    
    button.disabled = true;
    button.classList.add('loading');
}

// EFECTO RIPPLE
function createRipple(e) {
    const button = this;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple-effect');
    
    button.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// PARTÍCULAS
function createParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    
    const particleCount = 30;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        const size = Math.random() * 40 + 10;
        const left = Math.random() * 100;
        const delay = Math.random() * 20;
        const duration = Math.random() * 10 + 15;
        const opacity = Math.random() * 0.3 + 0.1;
        
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = left + '%';
        particle.style.bottom = '-50px';
        particle.style.animationDelay = delay + 's';
        particle.style.animationDuration = duration + 's';
        particle.style.opacity = opacity;
        
        container.appendChild(particle);
    }
}

// ALERTAS
function closeAlert() {
    const alert = document.getElementById('alert');
    if (!alert) return;
    
    alert.style.animation = 'slideUp 0.3s ease forwards';
    setTimeout(() => {
        alert.remove();
    }, 300);
}

function initAutoCloseAlerts() {
    const alert = document.getElementById('alert');
    if (alert) {
        setTimeout(() => {
            closeAlert();
        }, 5000);
        
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeAlert);
        }
    }
}

// RECORDAR USUARIO
function saveRememberMe(username) {
    try {
        localStorage.setItem('rememberedUser', username);
    } catch (e) {
        console.error('Error al guardar en localStorage:', e);
    }
}

function loadRememberMe() {
    try {
        const rememberedUser = localStorage.getItem('rememberedUser');
        const usernameInput = document.getElementById('username');
        const rememberMeCheckbox = document.getElementById('rememberMe');
        
        if (rememberedUser && usernameInput) {
            usernameInput.value = rememberedUser;
            if (rememberMeCheckbox) {
                rememberMeCheckbox.checked = true;
            }
        }
    } catch (e) {
        console.error('Error al cargar de localStorage:', e);
    }
}

function clearRememberMe() {
    try {
        localStorage.removeItem('rememberedUser');
    } catch (e) {
        console.error('Error al limpiar localStorage:', e);
    }
}

// NAVEGACIÓN CON TECLADO
function initKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        if (e.key === 'Enter' && document.activeElement === usernameInput) {
            e.preventDefault();
            if (passwordInput) {
                passwordInput.focus();
            }
        }
        
        if (e.key === 'Escape') {
            closeAlert();
        }
    });
}

// PREVENIR DOBLE SUBMIT
let isSubmitting = false;
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        if (validateForm()) {
            isSubmitting = true;
        }
    });
}

// DETECCIÓN DE MODO OSCURO
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    console.log('Modo oscuro detectado en el sistema');
}

// FOCUS VISUAL EN INPUTS
document.querySelectorAll('.form-group input').forEach(input => {
    input.addEventListener('focus', function() {
        const wrapper = this.closest('.input-wrapper');
        if (wrapper) {
            wrapper.classList.add('focused');
        }
    });
    
    input.addEventListener('blur', function() {
        const wrapper = this.closest('.input-wrapper');
        if (wrapper) {
            wrapper.classList.remove('focused');
        }
    });
});

// UTILIDADES
function showCustomError(message, type = 'error') {
    const alertContainer = document.querySelector('.login-form');
    if (!alertContainer) return;
    
    const existingAlerts = alertContainer.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.id = 'alert';
    
    const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
    
    alert.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
        <button type="button" class="alert-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    setTimeout(() => {
        closeAlert();
    }, 3000);
    
    alert.querySelector('.alert-close').addEventListener('click', closeAlert);
}

window.closeAlert = closeAlert;