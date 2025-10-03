<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado y es alumno
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'ALUMNO') {
    header("Location: acceso_errado.php?error=sesion_expirada");
    exit();
}

$alumno_id = $_SESSION['usuario_id'];
$curso_id = $_SESSION['curso_id'];

// Obtener materias del curso del alumno
$sql_materias = "SELECT m.id, m.nombre as materia_nombre, 
                 CONCAT(p.nombre, ' ', p.apellido) as profesor_nombre
                 FROM materias m 
                 LEFT JOIN profesores p ON m.id_prof = p.id 
                 WHERE m.id_cur = '$curso_id' 
                 ORDER BY m.nombre";
$resultado_materias = mysqli_query($conexion, $sql_materias);

// Obtener calificaciones del alumno por bimestre
$sql_calificaciones = "SELECT c.*, m.nombre as materia_nombre 
                       FROM cali c 
                       LEFT JOIN materias m ON c.idm = m.id 
                       WHERE c.ida = $alumno_id AND m.id_cur = '$curso_id'
                       ORDER BY m.nombre, c.bim";
$resultado_calificaciones = mysqli_query($conexion, $sql_calificaciones);

// Organizar calificaciones por materia y bimestre
$calificaciones = array();
if ($resultado_calificaciones) {
    while ($cali = mysqli_fetch_assoc($resultado_calificaciones)) {
        $calificaciones[$cali['idm']][$cali['bim']] = $cali['cali'];
    }
}

// Contar materias
$total_materias = mysqli_num_rows($resultado_materias);

// Calcular estadísticas
$total_calificaciones = 0;
$suma_calificaciones = 0;
$calificaciones_count = 0;

foreach ($calificaciones as $materia_calis) {
    foreach ($materia_calis as $bim => $cali) {
        if ($cali !== null && is_numeric($cali)) {
            $total_calificaciones++;
            $suma_calificaciones += floatval($cali);
            $calificaciones_count++;
        }
    }
}

$promedio_general = $calificaciones_count > 0 ? round($suma_calificaciones / $calificaciones_count, 2) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Calificaciones - Sistema de Calificaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container alumno">
        <div class="header">
            <div class="header-info">
                <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
                <p class="user-role">Alumno - <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></p>
                <p class="breadcrumb">Curso: <?php echo htmlspecialchars($_SESSION['curso_nombre']); ?></p>
            </div>
            <div class="header-buttons">
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <!-- Estadísticas del alumno -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_materias; ?></div>
                    <div class="stat-label">Materias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_calificaciones; ?></div>
                    <div class="stat-label">Calificaciones</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $promedio_general; ?></div>
                    <div class="stat-label">Promedio General</div>
                </div>
            </div>

            <h2>Mis Calificaciones</h2>
            
            <?php if ($total_materias > 0): ?>
                <div class="calificaciones-container">
                    <div class="calificaciones-tabla">
                        <table class="tabla-calificaciones">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>Profesor</th>
                                    <th>1° Bim.</th>
                                    <th>2° Bim.</th>
                                    <th>3° Bim.</th>
                                    <th>4° Bim.</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($resultado_materias, 0);
                                while ($materia = mysqli_fetch_assoc($resultado_materias)): 
                                    $materia_id = $materia['id'];
                                    
                                    // Calcular promedio de la materia
                                    $notas_materia = array();
                                    for ($bim = 1; $bim <= 4; $bim++) {
                                        if (isset($calificaciones[$materia_id][$bim]) && 
                                            is_numeric($calificaciones[$materia_id][$bim])) {
                                            $notas_materia[] = floatval($calificaciones[$materia_id][$bim]);
                                        }
                                    }
                                    $promedio_materia = count($notas_materia) > 0 ? 
                                                       round(array_sum($notas_materia) / count($notas_materia), 2) : 0;
                                ?>
                                    <tr class="materia-row" onclick="verDetalleMateria(<?php echo $materia_id; ?>)">
                                        <td class="materia-nombre">
                                            <a href="alumno_materia.php?materia_id=<?php echo $materia_id; ?>" class="materia-link">
                                                <?php echo htmlspecialchars($materia['materia_nombre']); ?>
                                            </a>
                                        </td>
                                        <td class="profesor-nombre">
                                            <?php echo htmlspecialchars($materia['profesor_nombre'] ?: 'Sin asignar'); ?>
                                        </td>
                                        <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                                            <td class="calificacion">
                                                <?php 
                                                $nota = isset($calificaciones[$materia_id][$bim]) ? 
                                                       $calificaciones[$materia_id][$bim] : '-';
                                                
                                                if ($nota !== '-' && is_numeric($nota)) {
                                                    $clase_nota = floatval($nota) >= 6 ? 'aprobado' : 'desaprobado';
                                                    echo "<span class='nota $clase_nota'>$nota</span>";
                                                } else {
                                                    echo "<span class='nota pendiente'>-</span>";
                                                }
                                                ?>
                                            </td>
                                        <?php endfor; ?>
                                        <td class="promedio-materia">
                                            <?php if ($promedio_materia > 0): ?>
                                                <span class="promedio <?php echo $promedio_materia >= 6 ? 'aprobado' : 'desaprobado'; ?>">
                                                    <?php echo $promedio_materia; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="promedio pendiente">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="resumen-academico">
                    <h3>Resumen Académico</h3>
                    <div class="resumen-cards">
                        <div class="resumen-card">
                            <div class="resumen-icon">📊</div>
                            <h4>Materias Aprobadas</h4>
                            <p class="resumen-valor">
                                <?php
                                $materias_aprobadas = 0;
                                mysqli_data_seek($resultado_materias, 0);
                                while ($materia = mysqli_fetch_assoc($resultado_materias)) {
                                    $materia_id = $materia['id'];
                                    $notas_materia = array();
                                    for ($bim = 1; $bim <= 4; $bim++) {
                                        if (isset($calificaciones[$materia_id][$bim]) && 
                                            is_numeric($calificaciones[$materia_id][$bim])) {
                                            $notas_materia[] = floatval($calificaciones[$materia_id][$bim]);
                                        }
                                    }
                                    if (count($notas_materia) > 0) {
                                        $promedio = array_sum($notas_materia) / count($notas_materia);
                                        if ($promedio >= 6) $materias_aprobadas++;
                                    }
                                }
                                echo "$materias_aprobadas de $total_materias";
                                ?>
                            </p>
                        </div>
                        
                        <div class="resumen-card">
                            <div class="resumen-icon">🎯</div>
                            <h4>Promedio General</h4>
                            <p class="resumen-valor <?php echo $promedio_general >= 6 ? 'aprobado' : 'desaprobado'; ?>">
                                <?php echo $promedio_general; ?>
                            </p>
                        </div>
                        
                        <div class="resumen-card">
                            <div class="resumen-icon">📝</div>
                            <h4>Calificaciones Pendientes</h4>
                            <p class="resumen-valor">
                                <?php 
                                $total_posibles = $total_materias * 4;
                                $pendientes = $total_posibles - $total_calificaciones;
                                echo $pendientes;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="no-materias">
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <h3>No hay materias asignadas</h3>
                        <p>No hay materias asignadas a tu curso actualmente.</p>
                        <p class="info-adicional">Contacta al coordinador académico para más información.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        function verDetalleMateria(materiaId) {
            window.location.href = `alumno_materia.php?materia_id=${materiaId}`;
        }
        
        // Hacer que las filas de la tabla sean clickeables
        document.addEventListener('DOMContentLoaded', function() {
            const filas = document.querySelectorAll('.materia-row');
            filas.forEach(function(fila) {
                fila.style.cursor = 'pointer';
            });
        });
    </script>
</body>
</html>