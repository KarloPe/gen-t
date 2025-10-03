<!-- 9 ok -->
<?php
session_start();
include 'config.php';

// Verificar que se enviaron los datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar datos del formulario
    $usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $clave = mysqli_real_escape_string($conexion, trim($_POST['clave']));
    
    // Verificar que los campos no estén vacíos
    if (empty($usuario) || empty($clave)) {
        header("Location: acceso_errado.php?error=campos_vacios");
        exit();
    }
    
    // Primero intentar como profesor
    $sql_profesor = "SELECT id, usuario, clave, nombre, apellido FROM profesores WHERE id = '$usuario'";
    $resultado_profesor = mysqli_query($conexion, $sql_profesor);
    
    if ($resultado_profesor && mysqli_num_rows($resultado_profesor) == 1) {
        $fila = mysqli_fetch_assoc($resultado_profesor);
        
        // Verificar la contraseña
        if ($clave === $fila['clave']) {
            // Credenciales válidas - PROFESOR
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['nombre_completo'] = $fila['nombre'] . ' ' . $fila['apellido'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['apellido'] = $fila['apellido'];
            $_SESSION['es_admin'] = false;
            $_SESSION['rol'] = 'DOCENTE';
            $_SESSION['rol_id'] = $fila['rol_id'];
            $_SESSION['login_time'] = time();
            
            /*/ Registrar último acceso
            $update_sql = "UPDATE profesores SET fecha_creacion = CURRENT_TIMESTAMP WHERE id = '{$fila['id']}'";
            mysqli_query($conexion, $update_sql);
            */
            
            header("Location: bienvenido.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: acceso_errado.php?error=clave_incorrecta");
            exit();
        }
    }
    
    // Si no es profesor, intentar como alumno
    $sql_alumno = "SELECT a.id, a.nombre, a.apellido, a.email, a.idc, c.nombre as curso_nombre 
                   FROM alumnos a 
                   LEFT JOIN cursos c ON a.idc = c.idc 
                   WHERE a.id = '$usuario'";
    $resultado_alumno = mysqli_query($conexion, $sql_alumno);
    
    if ($resultado_alumno && mysqli_num_rows($resultado_alumno) == 1) {
        $fila = mysqli_fetch_assoc($resultado_alumno);
        
        // Para alumnos, usamos el ID como contraseña (simplificado para esta versión)
        if ($clave === $fila['id']) {
            // Credenciales válidas - ALUMNO
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['id'];
            $_SESSION['nombre_completo'] = $fila['nombre'] . ' ' . $fila['apellido'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['apellido'] = $fila['apellido'];
            $_SESSION['email'] = $fila['email'];
            $_SESSION['curso_id'] = $fila['idc'];
            $_SESSION['curso_nombre'] = $fila['curso_nombre'];
            $_SESSION['es_admin'] = false;
            $_SESSION['rol'] = 'ALUMNO';
            $_SESSION['rol_id'] = 4;
            $_SESSION['login_time'] = time();
            
            header("Location: alumno_dashboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: acceso_errado.php?error=clave_incorrecta");
            exit();
        }
    }
    
    // Usuario no encontrado en ninguna tabla
    header("Location: acceso_errado.php?error=usuario_no_encontrado");
    exit();
    
} else {
    // Acceso directo sin POST
    header("Location: login.html");
    exit();
}

mysqli_close($conexion);
?>