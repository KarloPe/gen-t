<?php
$error_message = "Usuario o contraseña incorrectos.";
$error_detail = "Por favor, verifica tus credenciales e intenta nuevamente.";

// Personalizar mensaje según el tipo de error
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'campos_vacios':
            $error_message = "Campos incompletos";
            $error_detail = "Por favor, completa todos los campos requeridos.";
            break;
        case 'clave_incorrecta':
            $error_message = "Contraseña incorrecta";
            $error_detail = "La contraseña ingresada no es válida.";
            break;
        case 'usuario_no_encontrado':
            $error_message = "Usuario no encontrado";
            $error_detail = "El ID de usuario ingresado no existe en el sistema.";
            break;
        case 'sesion_expirada':
            $error_message = "Sesión expirada";
            $error_detail = "Tu sesión ha expirado. Por favor, inicia sesión nuevamente.";
            break;
        default:
            $error_message = "Error de acceso";
            $error_detail = "Ha ocurrido un error. Intenta nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Sistema de Calificaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="result-container error">
            <h1><?php echo htmlspecialchars($error_message); ?></h1>
            <p><?php echo htmlspecialchars($error_detail); ?></p>
            
            <div class="error-actions">
                <a href="login.html" class="btn-back">Volver al Login</a>
            </div>
            
            <div class="help-section">
                <h3>¿Necesitas ayuda?</h3>
                <ul>
                    <li>Verifica que tu ID de usuario sea correcto</li>
                    <li>Asegúrate de escribir la contraseña correctamente</li>
                    <li>Si eres administrador, usa 'admin' como usuario</li>
                    <li>Contacta al administrador del sistema si persiste el problema</li>
                </ul>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>