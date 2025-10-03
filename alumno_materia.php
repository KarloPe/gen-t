<?php
session_start();
include 'config.php';
include 'verificar_sesion.php';

// Verificar que solo los alumnos puedan acceder
verificarSesion(array('ALUMNO'));

// Verificar que se haya enviado el ID de la materia
if (!isset($_GET['materia_id']) || empty($_GET['materia_id'])) {
    header("Location: alumno_dashboard.php");
    exit();
}

$materia_id = (int)$_GET['materia_id'];
$alumno_id = $_SESSION['usuario_id'];
$curso_id = $_SESSION['curso_id'];

// Verificar que la materia pertenezca al curso del alumno
$sql_verificar = "SELECT m.id, m.nombre as materia_nombre, 
                  CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre -- ,
                  -- p.email as profesor_email
                  FROM materias m 
                  LEFT JOIN profesores p ON m.id_prof = p.id 
                  WHERE m.id = $materia_id AND m.id_cur = '$curso_id'";
$resultado_verificar = mysqli_query($conexion, $sql_verificar);

if (!$resultado_verificar || mysqli_num_rows($resultado_verificar) == 0) {
    header("Location: alumno_dashboard.php");
    exit();
}

$materia_info = mysqli_fetch_assoc($resultado_verificar);

// Obtener calificaciones del alumno para esta materia
$sql_calificaciones = "SELECT * FROM cali 
                       WHERE ida = $alumno_id AND idm = $materia_id 
                       ORDER BY bim";
$resultado_calificaciones = mysqli_query($conexion, $sql_calificaciones);

$calificaciones = array();
if ($resultado_calificaciones) {
    while ($cali = mysqli_fetch_assoc($resultado_calificaciones)) {
        $calificaciones[$cali['bim']] = $cali['cali'];
    }
}

// Calcular promedio
$notas_numericas = array();
for ($bim = 1; $bim <= 4; $bim++) {
    if (isset($calificaciones[$bim]) && is_numeric($calificaciones[$bim])) {
        $notas_numericas[] = floatval($calificaciones[$bim]);
    }
}
$promedio = count($notas_numericas) > 0 ? round(array_sum($notas_numericas) / count($notas_numericas), 2) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($materia_info['materia_nombre']); ?> - Mis Calificaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container alumno">
        <div class="header">
            <div class="header-info">
                <h1><?php echo htmlspecialchars($materia_info['materia_nombre']); ?></h1>
                <p class="breadcrumb">
                    <a href="alumno_dashboard.php">Mis Calificaciones</a> > 
                    <?php echo htmlspecialchars($materia_info['materia_nombre']); ?>
                </p>
            </div>
            <div class="header-buttons">
                <a href="alumno_dashboard.php" class="btn-back">Volver</a>
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <div class="materia-detalle">
                <div class="materia-header">
                    <h2><?php echo htmlspecialchars($materia_info['materia_nombre']); ?></h2>
                    <div class="profesor-info">
                        <p><strong>Profesor:</strong> <?php echo htmlspecialchars($materia_info['profesor_nombre'] ?: 'Sin asignar'); ?></p>
                        <!--
                        <?php if ($materia_info['profesor_email']): ?>
                            <p><strong>Email:</strong> 
                                <a href="mailto:<?php echo htmlspecialchars($materia_info['profesor_email']); ?>">
                                    <?php echo htmlspecialchars($materia_info['profesor_email']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        --> 
                    </div>
                </div>
                
                <div class="calificaciones-detalle">
                    <h3>Calificaciones por Bimestre</h3>
                    <div class="bimestres-grid">
                        <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                            <div class="bimestre-card">
                                <h4><?php echo $bim; ?>° Bimestre</h4>
                                <div class="nota-display">
                                    <?php 
                                    $nota = isset($calificaciones[$bim]) ? $calificaciones[$bim] : null;
                                    if ($nota !== null && is_numeric($nota)) {
                                        $clase_nota = floatval($nota) >= 6 ? 'aprobado' : 'desaprobado';
                                        echo "<span class='nota-grande $clase_nota'>$nota</span>";
                                    } else {
                                        echo "<span class='nota-grande pendiente'>Sin calificar</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="promedio-final">
                        <h3>Promedio de la Materia</h3>
                        <div class="promedio-display">
                            <?php if ($promedio > 0): ?>
                                <span class="promedio-grande <?php echo $promedio >= 6 ? 'aprobado' : 'desaprobado'; ?>">
                                    <?php echo $promedio; ?>
                                </span>
                                <p class="estado-materia">
                                    <?php echo $promedio >= 6 ? '✅ Materia Aprobada' : '❌ Materia Desaprobada'; ?>
                                </p>
                            <?php else: ?>
                                <span class="promedio-grande pendiente">Sin calificaciones</span>
                                <p class="estado-materia">⏳ Esperando calificaciones</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="observaciones">
                    <h3>Información Adicional</h3>
                    <div class="info-cards">
                        <div class="info-card">
                            <h4>📊 Rendimiento</h4>
                            <p>
                                <?php 
                                $calificadas = count($notas_numericas);
                                echo "Calificaciones registradas: $calificadas de 4 bimestres";
                                ?>
                            </p>
                        </div>
                        
                        <div class="info-card">
                            <h4>🎯 Meta</h4>
                            <p>Promedio mínimo para aprobar: 6.0</p>
                        </div>
                        
                        <div class="info-card">
                            <h4>📞 Contacto</h4>
                            <p>Consulta con tu profesor para aclarar dudas sobre las calificaciones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>