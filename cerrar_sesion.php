<?php
session_start();

// Registrar la actividad de logout si hay una sesión activa
if (isset($_SESSION['usuario_id']) && isset($_SESSION['login_time'])) {
    // Aquí podrías registrar en una tabla de logs si lo deseas
    $tiempo_sesion = time() - $_SESSION['login_time'];
    // Log: Usuario {$_SESSION['usuario']} cerró sesión después de {$tiempo_sesion} segundos
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Limpiar cualquier cache del navegador
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirigir al login
header("Location: login.html");
exit();
?>