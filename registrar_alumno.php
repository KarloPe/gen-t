<?php
include 'config.php';

// Función para validar datos del registro
function validarDatosRegistro($dni, $apellido, $fecha_nacimiento, $conexion) {
    // Verificar campos obligatorios
    if (empty($dni) || empty($apellido)) {
        return "campos_vacios";
    }
    
    // Validar formato DNI (solo números)
    if (!preg_match('/^[0-9]+$/', $dni)) {
        return "dni_invalido";
    }
    
    // Verificar que el DNI estÃ© dentro del rango permitido
    if ((int)$dni < 1000000 || (int)$dni > 99999999) {
        return "dni_fuera_de_rango";
    }
    
    // Verificar que el DNI no exista
    $sql_verificar = "SELECT id FROM alumnos WHERE id = '$dni'";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);
    
    if ($resultado_verificar && mysqli_num_rows($resultado_verificar) > 0) {
        return "dni_existente";
    }
    
    // Validar formato de fecha de nacimiento
    if (!empty($fecha_nacimiento)) {
        $fecha_validada = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
        if (!$fecha_validada || $fecha_validada->format('Y-m-d') !== $fecha_nacimiento) {
            return "fecha_invalida";
        }
    }
    
    // Si llegamos aquí, todo está bien
    return "ok";
}

// Verificar que se enviaron los datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar datos del formulario
    $dni = mysqli_real_escape_string($conexion, trim($_POST['dni']));
    $apellido = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $telefono = mysqli_real_escape_string($conexion, trim($_POST['telefono']));
    $fecha_nacimiento = mysqli_real_escape_string($conexion, trim($_POST['fecha_nacimiento']));
    
    // Validar datos usando la función
    $resultado_validacion = validarDatosRegistro($dni, $apellido, $fecha_nacimiento, $conexion);
    
    if ($resultado_validacion !== "ok") {
        header("Location: registro_resultado.php?error=" . $resultado_validacion);
        exit();
    }
    
    // Si llegamos aquí, las validaciones pasaron
    // Procesar campos opcionales
    if (empty($nombre)) $nombre = '00';
    if (empty($email)) $email = '00';
    if (empty($telefono)) $telefono = '00';
    if (empty($fecha_nacimiento)) $fecha_nacimiento = '1900-01-01';
    
    // Insertar nuevo alumno
    $sql_insertar = "INSERT INTO alumnos (id, nombre, apellido, email, telefono, fecha_nacimiento, idc, turno_id, rol_id) 
                     VALUES ('$dni', '$nombre', '$apellido', '$email', '$telefono', '$fecha_nacimiento', '00', '00', '4')";
   
    if (mysqli_query($conexion, $sql_insertar)) {
        // Registro exitoso
        header("Location: registro_resultado.php?success=registro_exitoso&dni=$dni");
        exit();
    } else {
        // Error en la inserción
        header("Location: registro_resultado.php?error=error_bd");
        exit();
    }
    
} else {
    // Acceso directo sin POST
    header("Location: login.html");
    exit();
}

mysqli_close($conexion);
?>