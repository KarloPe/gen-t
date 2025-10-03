<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: acceso_errado.php?error=sesion_expirada");
    exit();
}

// Verificar que se haya enviado el ID de la materia
if (!isset($_GET['materias_id']) || empty($_GET['materias_id'])) {
    header("Location: bienvenido.php");
    exit();
}

// $materia_id = (int)$_GET['materia_id'];
$materia_id = (int)$_GET['materias_id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el profesor tenga acceso a esta materia
$sql_verificar = "SELECT m.id, m.nombre as materia_nombre, c.nombre as curso_nombre, c.idc as curso_id, c.especialidad, c.turno, c.ano, c.divi
                  FROM materias m 
                  LEFT JOIN cursos c ON m.id_cur = c.idc 
                  WHERE m.id = $materia_id AND m.id_prof = $usuario_id";
$resultado_verificar = mysqli_query($conexion, $sql_verificar);

if (!$resultado_verificar || mysqli_num_rows($resultado_verificar) == 0) {
    header("Location: bienvenido.php");
    exit();
}

$materia_info = mysqli_fetch_assoc($resultado_verificar);
// Obtener alumnos del curso de esta materia
$curso_id = $materia_info['curso_id'];
//
$sql_alumnos = "SELECT id, nombre, apellido, email, telefono, fecha_nacimiento 
                FROM alumnos 
                WHERE idc = '$curso_id' 
                ORDER BY apellido, nombre";
$resultado_alumnos = mysqli_query($conexion, $sql_alumnos);

// Contar alumnos
$total_alumnos = mysqli_num_rows($resultado_alumnos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos - <?php echo htmlspecialchars($materia_info['materia_nombre']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-info">
                <h1>Alumnos de <?php echo htmlspecialchars($materia_info['materia_nombre']); ?></h1>
                <p class="breadcrumb">
                    <a href="bienvenido.php">Mis Materias</a> > 
                    <?php echo htmlspecialchars($materia_info['curso_nombre']); ?> > 
                    <?php echo htmlspecialchars($materia_info['materia_nombre']); ?>
                </p>
            </div>
            <div class="header-buttons">
                <a href="bienvenido.php" class="btn-back">Volver a Materias</a>
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <div class="materia-info">
                <h2><?php echo htmlspecialchars($materia_info['materia_nombre']); ?></h2>
                <div class="curso-info">
                    <p><strong><?php echo htmlspecialchars($materia_info['curso_nombre']); ?></strong></p>
                    <?php if ($materia_info['especialidad']): ?>
                        <p class="curso-details">
                            <?php echo htmlspecialchars($materia_info['especialidad']); ?> - 
                            <?php echo htmlspecialchars($materia_info['turno']); ?>
                            <?php if ($materia_info['ano'] && $materia_info['divi']): ?>
                                - <?php echo htmlspecialchars($materia_info['ano']); ?>° <?php echo htmlspecialchars($materia_info['divi']); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p class="total-alumnos">Total de alumnos: <strong><?php echo $total_alumnos; ?></strong></p>
                </div>
            </div>
            
            
                <div class="acciones-masivas">
                    <h3>Acciones para toda la clasee</h3>
                    <div class="acciones-buttons">
<!--                        <a href="resumen_calificaciones.php?materia_id=<?php echo $materia_id; ?>" class="btn-accion-masiva">
-->
                        <a href="resumen_calificaciones.php?materias_id=<?php echo $materia_id; ?>" class="btn-accion-masiva">
                            📊 Ver Resumen dde Calificaciones
                        </a>
                        <button class="btn-accion-masiva" disabled>📥 Exportar Lista</button>
                        <button class="btn-accion-masiva" disabled>📋 Generar Reporte</button>
                        <button class="btn-accion-masiva" disabled>📧 Enviar Comunicado</button>
                    </div>
                </div>
                

            <?php if ($total_alumnos > 0): ?>
                <div class="alumnos-container">
                    <div class="alumnos-grid">
                        <?php while ($alumno = mysqli_fetch_assoc($resultado_alumnos)): ?>
                            <div class="alumno-card">
                                <div class="alumno-info">
                                    <h3><?php echo htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']); ?></h3>
                                    <p class="alumno-id"><strong>DNI:</strong> <?php echo htmlspecialchars($alumno['id']); ?></p>
                                    
                                    <?php if ($alumno['email']): ?>
                                        <p class="alumno-email">
                                            <strong>Email:</strong> 
                                            <a href="mailto:<?php echo htmlspecialchars($alumno['email']); ?>">
                                                <?php echo htmlspecialchars($alumno['email']); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($alumno['telefono']): ?>
                                        <p class="alumno-telefono">
                                            <strong>Teléfono:</strong> 
                                            <a href="tel:<?php echo htmlspecialchars($alumno['telefono']); ?>">
                                                <?php echo htmlspecialchars($alumno['telefono']); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($alumno['fecha_nacimiento']): ?>
                                        <p class="alumno-fecha">
                                            <strong>F. Nacimiento:</strong> 
                                            <?php echo date('d/m/Y', strtotime($alumno['fecha_nacimiento'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="alumno-acciones">
                                    <a href="calificar_alumno.php?alumno_id=<?php echo $alumno['id']; ?>&materia_id=<?php echo $materia_id; ?>" class="btn-calificar">
                                        📝 Calificar
                                    </a>
                                    <a href="historial_alumno.php?alumno_id=<?php echo $alumno['id']; ?>&materia_id=<?php echo $materia_id; ?>" class="btn-historial">
                                        📊 Ver Historial
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="no-alumnos">
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3>No hay alumnos registrados</h3>
                        <p>No hay alumnos registrados en este curso actualmente.</p>
                        <p class="info-adicional">Contacta al administrador para revisar la asignación de alumnos al curso.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        // Funciones específicas para esta página
        function verHistorial(alumnoId, materiaId) {
            window.location.href = `historial_alumno.php?alumno_id=${alumnoId}&materia_id=${materiaId}`;
        }
    </script>
</body>
</html>