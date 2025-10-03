<?php
// verificar_sesion.php
// Archivo para verificar sesiones y roles de usuario

function verificarSesion($roles_permitidos = array()) {
    // Verificar si existe sesión
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
        header("Location: acceso_errado.php?error=sesion_expirada");
        exit();
    }
    
    // Si se especifican roles, verificar que el usuario tenga uno de ellos
    if (!empty($roles_permitidos)) {
        if (!in_array($_SESSION['rol'], $roles_permitidos)) {
            header("Location: acceso_errado.php?error=acceso_denegado");
            exit();
        }
    }
    
    return true;
}

function redirigirSegunRol() {
    if (!isset($_SESSION['rol'])) {
        header("Location: login.html");
        exit();
    }
    
    switch ($_SESSION['rol']) {
        case 'DOCENTE':
            header("Location: bienvenido.php");
            break;
        case 'ALUMNO':
            header("Location: alumno_dashboard.php");
            break;
        default:
            header("Location: acceso_errado.php?error=rol_desconocido");
            break;
    }
    exit();
}

function esDocente() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'DOCENTE';
}

function esAlumno() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ALUMNO';
}

function obtenerNombreCompleto() {
    return isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 'Usuario';
}

function obtenerRolAmigable() {
    if (!isset($_SESSION['rol'])) return 'Invitado';
    
    switch ($_SESSION['rol']) {
        case 'DOCENTE':
            return 'Profesor';
        case 'ALUMNO':
            return 'Alumno';
        default:
            return 'Usuario';
    }
}
?>