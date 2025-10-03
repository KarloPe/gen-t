// 9 ok
// Script.js - Sistema de Calificaciones (Versión Optimizada)

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de calificaciones cargado');
    
    // Inicializar componentes básicos
    initializeAnimations();
    initializeFormValidation();
    initializeMobileSupport();
});

// Animaciones de entrada
function initializeAnimations() {
    const cards = document.querySelectorAll('.curso-column, .alumno-card, .materia-item, .stat-card');
    
    cards.forEach((card, index) => {
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        }
    });
}

// Validación de formularios
function initializeFormValidation() {
    const loginForm = document.querySelector('form[action="validar.php"]');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario');
            const clave = document.getElementById('clave');
            
            if (!usuario || !clave) return;
            
            const usuarioVal = usuario.value.trim();
            const claveVal = clave.value.trim();
            
            if (!usuarioVal || !claveVal) {
                e.preventDefault();
                showNotification('Complete todos los campos', 'error');
                return false;
            }
            
            if (usuarioVal !== 'admin' && !/^\d+$/.test(usuarioVal)) {
                e.preventDefault();
                showNotification('El ID debe contener solo números', 'error');
                return false;
            }
            
            showLoader();
        });
    }
}

// Soporte para móviles
function initializeMobileSupport() {
    // Detectar dispositivos móviles
    if (window.innerWidth <= 768) {
        document.body.classList.add('mobile-device');
    }
    
    // Ajustar en redimensión
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-device');
        } else {
            document.body.classList.remove('mobile-device');
        }
    });
}



// Sistema de notificaciones
function showNotification(message, type = 'info') {
    // Remover notificación existente
    hideNotification();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="hideNotification()">×</button>
    `;

    document.body.appendChild(notification); // Asegúrate de añadirla al DOM
}


        
// Ocultar notificación
function hideNotification() {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
}
        
        
// Script.js - Funciones JavaScript para el sistema de calificaciones

document.addEventListener('DOMContentLoaded', function() {
    // Añadir efectos de hover y animaciones
    initializeAnimations();
    
    // Validación de formularios
    initializeFormValidation();
    
    // Funciones para futuras implementaciones
    initializeFutureFeatures();
});














// Inicializar animaciones y efectos visuales
function initializeAnimations() {
    // Efecto de aparición gradual para las tarjetas
    const cards = document.querySelectorAll('.curso-column, .alumno-card, .materia-item');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Efecto de pulso en botones importantes
    const importantButtons = document.querySelectorAll('.btn-login, .btn-ver-alumnos');
    importantButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.animation = 'pulse 0.5s ease-in-out';
        });
        
        button.addEventListener('animationend', function() {
            this.style.animation = '';
        });
    });
}

// Validación de formularios
function initializeFormValidation() {
    const loginForm = document.querySelector('form[action="validar.php"]');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const clave = document.getElementById('clave').value.trim();
            
            if (!usuario || !clave) {
                e.preventDefault();
                showNotification('Por favor, complete todos los campos', 'error');
                return false;
            }
            
            // Validar formato de ID usuario (números)
            if (usuario !== 'admin' && !/^\d+$/.test(usuario)) {
                e.preventDefault();
                showNotification('El ID de usuario debe contener solo números', 'error');
                return false;
            }
            
            // Mostrar loader
            showLoader();
        });
        
        // Limpiar mensajes de error al escribir
        const inputs = loginForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#e2e8f0';
                hideNotification();
            });
        });
    }
}

// Funciones para futuras implementaciones
function initializeFutureFeatures() {
    // Preparar botones deshabilitados para futuras funciones
    const disabledButtons = document.querySelectorAll('button:disabled');
    disabledButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Esta función estará disponible en la próxima versión', 'info');
        });
    });
    
    // Preparar funcionalidad de búsqueda (para implementar más adelante)
    initializeSearchFunctionality();
}

// Funcionalidad de búsqueda (placeholder para futuro)
function initializeSearchFunctionality() {
    // Crear campo de búsqueda dinámico si hay muchos elementos
    const alumnosGrid = document.querySelector('.alumnos-grid');
    const materiasContainer = document.querySelector('.cursos-columns-container');
    
    if (alumnosGrid && alumnosGrid.children.length > 6) {
        addSearchBox(alumnosGrid, 'alumnos');
    }
    
    if (materiasContainer && materiasContainer.children.length > 4) {
        addSearchBox(materiasContainer, 'materias');
    }
}

// Añadir caja de búsqueda
function addSearchBox(container, type) {
    const searchBox = document.createElement('div');
    searchBox.className = 'search-box';
    searchBox.innerHTML = `
        <input type="text" placeholder="Buscar ${type}..." class="search-input">
        <button type="button" class="search-clear">×</button>
    `;
    
    container.parentNode.insertBefore(searchBox, container);
    
    const searchInput = searchBox.querySelector('.search-input');
    const clearButton = searchBox.querySelector('.search-clear');
    
    searchInput.addEventListener('input', function() {
        filterElements(container, this.value, type);
    });
    
    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        filterElements(container, '', type);
    });
}

// Filtrar elementos
function filterElements(container, searchTerm, type) {
    const elements = container.children;
    const term = searchTerm.toLowerCase();
    
    Array.from(elements).forEach(element => {
        let text = '';
        
        if (type === 'alumnos') {
            text = element.querySelector('h3').textContent.toLowerCase();
        } else if (type === 'materias') {
            text = element.textContent.toLowerCase();
        }
        
        if (text.includes(term)) {
            element.style.display = '';
            element.style.animation = 'fadeIn 0.3s ease';
        } else {
            element.style.display = 'none';
        }
    });
}

// Sistema de notificaciones
function showNotification(message, type = 'info') {
    // Remover notificación existente
    hideNotification();
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Cerrar notificación
    notification.querySelector('.notification-close').addEventListener('click', hideNotification);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(hideNotification, 5000);
}

function hideNotification() {
    const notification = document.querySelector('.notification');
    if (notification) {
        notification.remove();
    }
}

// Mostrar/ocultar loader
function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.innerHTML = `
        <div class="loader-spinner"></div>
        <p>Verificando credenciales...</p>
    `;
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.remove();
    }
}

// Funciones de utilidad
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatPhone(phone) {
    // Formatear número de teléfono argentino
    return phone.replace(/(\d{2})(\d{4})(\d{4})/, '$1-$2-$3');
}

// Detectar si es dispositivo móvil
function isMobile() {
    return window.innerWidth <= 768;
}

// Ajustar interfaz para móviles
function adjustForMobile() {
    if (isMobile()) {
        document.body.classList.add('mobile-device');
        
        // Ajustar tamaño de fuente en campos de entrada para evitar zoom en iOS
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            if (input.style.fontSize < '16px') {
                input.style.fontSize = '16px';
            }
        });
    }
}

// Llamar al ajuste móvil cuando cambie el tamaño de ventana
window.addEventListener('resize', adjustForMobile);

// Funciones para el manejo de sesiones
function checkSessionTimeout() {
    // Implementar verificación de timeout de sesión
    setInterval(() => {
        fetch('check_session.php')
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    showNotification('Su sesión ha expirado. Será redirigido al login.', 'warning');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 3000);
                }
            })
            .catch(error => {
                console.log('Error checking session:', error);
            });
    }, 300000); // Verificar cada 5 minutos
}

// Funciones para confirmación de acciones
function confirmLogout() {
    if (confirm('¿Está seguro que desea cerrar sesión?')) {
        window.location.href = 'cerrar_sesion.php';
    }
}

// Agregar confirmación a botones de logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutButtons = document.querySelectorAll('.btn-logout');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            confirmLogout();
        });
    });
});

// Funciones para mejorar la experiencia de usuario
function addLoadingStates() {
    const buttons = document.querySelectorAll('button, .btn-ver-alumnos');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.disabled) {
                this.style.opacity = '0.7';
                this.style.cursor = 'wait';
                
                // Restaurar estado después de 2 segundos
                setTimeout(() => {
                    this.style.opacity = '';
                    this.style.cursor = '';
                }, 2000);
            }
        });
    });
}

// Funciones de accesibilidad
function enhanceAccessibility() {
    // Agregar navegación por teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Agregar skip links
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.className = 'skip-link';
    skipLink.textContent = 'Saltar al contenido principal';
    document.body.insertBefore(skipLink, document.body.firstChild);
}

// Sistema de temas (preparado para futuro)
function initializeThemeSystem() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);
}

function applyTheme(theme) {
    document.body.setAttribute('data-theme', theme);
}

function toggleTheme() {
    const currentTheme = document.body.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
}

// Manejo de errores de red
function handleNetworkErrors() {
    window.addEventListener('online', function() {
        showNotification('Conexión restablecida', 'success');
    });
    
    window.addEventListener('offline', function() {
        showNotification('Sin conexión a internet', 'warning');
    });
}

// Inicialización completa
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeFormValidation();
    initializeFutureFeatures();
    adjustForMobile();
    addLoadingStates();
    enhanceAccessibility();
    handleNetworkErrors();
    
    // Solo verificar sesión si no estamos en la página de login
    if (!window.location.href.includes('login.html')) {
        checkSessionTimeout();
    }
});