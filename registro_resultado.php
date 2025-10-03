<?php
$mensaje = "Registro completado.";
$detalle = "Tu registro se ha procesado correctamente.";
$es_exitoso = true;

// Personalizar mensaje según el resultado
if (isset($_GET['success'])) {
    $dni = isset($_GET['dni']) ? htmlspecialchars($_GET['dni']) : '';
    $mensaje = "¡Registro exitoso!";
    $detalle = "Te has registrado correctamente con DNI: $dni. Ya puedes iniciar sesión usando tu DNI como usuario y contraseña.";
    $es_exitoso = true;
} elseif (isset($_GET['error'])) {
    $es_exitoso = false;
    switch ($_GET['error']) {
        case 'campos_vacios':
            $mensaje = "Campos incompletos";
            $detalle = "Por favor, completa todos los campos requeridos.";
            break;
        case 'fecha_invalida':
            $mensaje = "Fecha inválida";
            $detalle = "El formato de fecha de nacimiento no es válido.";
            break;
        case 'dni_invalido':
            $mensaje = "DNI inválido";
            $detalle = "El DNI debe contener solo números.";
            break;
        case 'dni_existente':
            $mensaje = "DNI ya registrado";
            $detalle = "Este DNI ya está registrado en el sistema. Si ya tienes cuenta, inicia sesión.";
            break;
        case 'dni_fuera_de_rango':
            $mensaje = "DNI fuera de rango";
            $detalle = "El DNI debe estar entre 1.000.000 y 99.999.999";    
            break;
        case 'error_bd':
            $mensaje = "Error del sistema";
            $detalle = "Ocurrió un error al procesar el registro. Intenta nuevamente.";
            break;
        default:
            $mensaje = "Error de registro";
            $detalle = "Ha ocurrido un error. Intenta nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Registro - Sistema de Calificaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="result-container <?php echo $es_exitoso ? 'success' : 'error'; ?>">
            <h1><?php echo htmlspecialchars($mensaje); ?></h1>
            <p><?php echo htmlspecialchars($detalle); ?></p>
            
            <div class="error-actions">
                <a href="login.html" class="btn-back"><?php echo $es_exitoso ? 'Iniciar Sesión' : 'Volver al Login'; ?></a>
            </div>
            
            <?php if ($es_exitoso): ?>
                <div class="help-section" style="border-left-color: #48bb78; background: rgba(72, 187, 120, 0.1);">
                    <h3>Información importante:</h3>
                    <ul>
                        <li>Tu usuario es tu DNI</li>
                        <li>Tu contraseña inicial es tu DNI</li>
                        <li>Ya puedes iniciar sesión</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="help-section">
                    <h3>¿Necesitas ayuda?</h3>
                    <ul>
                        <li>Verifica que el DNI contenga solo números</li>
                        <li>Asegúrate de completar todos los campos</li>
                        <li>Si el DNI ya existe, intenta iniciar sesión</li>
                        <li>Contacta al administrador si persiste el problema</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>