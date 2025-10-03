<?php
// Configuración de la base de datos
$servidor = "sql104.infinityfree.com";
$usuario_db = "if0_39417527";
$clave_db = "b3b3inf1";
$base_datos = "if0_39417527_prueba1";

// Crear  conexión
$conexion = new mysqli($servidor, $usuario_db, $clave_db, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer charset para caracteres especiales
$conexion->set_charset("utf8");
?>