<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: acceso_errado.php?error=sesion_expirada");
    exit();
}

// Verificar que se hayan enviado los parámetros necesarios
if (!isset($_GET['alumno_id']) || !isset($_GET['materia_id']) || empty($_GET['alumno_id']) || empty($_GET['materia_id'])) {
    header("Location: bienvenido.php");
    exit();
}

$alumno_id = (int)$_GET['alumno_id'];
$materia_id = (int)$_GET['materia_id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el profesor tenga acceso a esta materia y alumno
$sql_verificar = "SELECT 
    m.id as materia_id, 
    m.nombre as materia_nombre,
    a.id as alumno_id,
    a.nombre as alumno_nombre,
    a.apellido as alumno_apellido,
    a.email as alumno_email,
    a.telefono as alumno_telefono,
    a.fecha_nacimiento,
    c.nombre as curso_nombre,
    c.idc as curso_id,
    c.especialidad,
    c.turno,
    c.ano,
    c.divi
FROM materias m 
LEFT JOIN cursos c ON m.id_cur = c.idc 
LEFT JOIN alumnos a ON a.idc = c.idc
WHERE m.id = $materia_id 
AND m.id_prof = $usuario_id 
AND a.id = $alumno_id";

$resultado_verificar = mysqli_query($conexion, $sql_verificar);

if (!$resultado_verificar || mysqli_num_rows($resultado_verificar) == 0) {
    header("Location: bienvenido.php");
    exit();
}

$info = mysqli_fetch_assoc($resultado_verificar);

// Obtener todas las calificaciones del alumno en esta materia
$sql_calificaciones = "SELECT id, cali, bim, fecha_registro, observaciones 
                       FROM cali 
                       WHERE ida = $alumno_id AND idm = $materia_id 
                       ORDER BY bim ASC";
$resultado_calificaciones = mysqli_query($conexion, $sql_calificaciones);

$calificaciones = [];
$suma_total = 0;
$total_calificaciones = 0;

while ($cal = mysqli_fetch_assoc($resultado_calificaciones)) {
    $calificaciones[] = $cal;
    if (!empty($cal['cali'])) {
        $suma_total += floatval($cal['cali']);
        $total_calificaciones++;
    }
}

$promedio_general = $total_calificaciones > 0 ? round($suma_total / $total_calificaciones, 2) : 0;

// Obtener estadísticas del curso en esta materia
$sql_estadisticas_curso = "SELECT 
    COUNT(*) as total_alumnos,
    AVG(CAST(c.cali AS DECIMAL(3,2))) as promedio_curso,
    MAX(CAST(c.cali AS DECIMAL(3,2))) as nota_maxima,
    MIN(CAST(c.cali AS DECIMAL(3,2))) as nota_minima
FROM alumnos a
LEFT JOIN cali c ON a.id = c.ida AND c.idm = $materia_id
WHERE a.idc = '{$info['curso_id']}'";
$resultado_stats = mysqli_query($conexion, $sql_estadisticas_curso);
$stats_curso = mysqli_fetch_assoc($resultado_stats);

// Calcular posición del alumno en el curso
$sql_posicion = "SELECT 
    alumno_id,
    promedio,
    ROW_NUMBER() OVER (ORDER BY promedio DESC) as posicion
FROM (
    SELECT 
        a.id as alumno_id,
        AVG(CAST(c.cali AS DECIMAL(3,2))) as promedio
    FROM alumnos a
    LEFT JOIN cali c ON a.id = c.ida AND c.idm = $materia_id
    WHERE a.idc = '{$info['curso_id']}' AND c.cali IS NOT NULL
    GROUP BY a.id
) as promedios";
$resultado_posicion = mysqli_query($conexion, $sql_posicion);

$posicion_alumno = 0;
while ($pos = mysqli_fetch_assoc($resultado_posicion)) {
    if ($pos['alumno_id'] == $alumno_id) {
        $posicion_alumno = $pos['posicion'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - <?php echo htmlspecialchars($info['alumno_apellido'] . ', ' . $info['alumno_nombre']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-info">
                <h1>Historial de Calificaciones</h1>
                <p class="breadcrumb">
                    <a href="bienvenido.php">Mis Materias</a> > 
                    <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>">Alumnos</a> > 
                    Historial
                </p>
            </div>
            <div class="header-buttons">
                <a href="calificar_alumno.php?alumno_id=<?php echo $alumno_id; ?>&materia_id=<?php echo $materia_id; ?>" class="btn-admin">📝 Calificar</a>
                <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>" class="btn-back">Volver a Alumnos</a>
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <!-- Información del alumno -->
            <div class="historial-alumno-info">
                <div class="alumno-datos">
                    <h2><?php echo htmlspecialchars($info['alumno_apellido'] . ', ' . $info['alumno_nombre']); ?></h2>
                    <div class="datos-basicos">
                        <p><strong>DNI:</strong> <?php echo htmlspecialchars($info['alumno_id']); ?></p>
                        <?php if ($info['alumno_email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($info['alumno_email']); ?></p>
                        <?php endif; ?>
                        <?php if ($info['alumno_telefono']): ?>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($info['alumno_telefono']); ?></p>
                        <?php endif; ?>
                        <?php if ($info['fecha_nacimiento']): ?>
                            <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($info['fecha_nacimiento'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="materia-datos">
                    <h3><?php echo htmlspecialchars($info['materia_nombre']); ?></h3>
                    <p><strong>Curso:</strong> <?php echo htmlspecialchars($info['curso_nombre']); ?></p>
                    <?php if ($info['especialidad']): ?>
                        <p><strong>Especialidad:</strong> 
                            <?php echo htmlspecialchars($info['especialidad'] . ' - ' . $info['turno']); ?>
                            <?php if ($info['ano'] && $info['divi']): ?>
                                - <?php echo htmlspecialchars($info['ano'] . '° ' . $info['divi']); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Estadísticas generales -->
            <div class="estadisticas-container">
                <div class="estadistica-card">
                    <h3>Promedio General</h3>
                    <div class="estadistica-valor <?php echo $promedio_general >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                        <?php echo $promedio_general ?: 'S/C'; ?>
                    </div>
                    <p class="estadistica-detalle">
                        <?php echo $promedio_general >= 7 ? 'Aprobado' : ($promedio_general > 0 ? 'Desaprobado' : 'Sin calificaciones'); ?>
                    </p>
                </div>
                
                <div class="estadistica-card">
                    <h3>Posición en el Curso</h3>
                    <div class="estadistica-valor neutral">
                        <?php echo $posicion_alumno ?: 'S/D'; ?>
                    </div>
                    <p class="estadistica-detalle">
                        de <?php echo $stats_curso['total_alumnos']; ?> alumnos
                    </p>
                </div>
                
                <div class="estadistica-card">
                    <h3>Promedio del Curso</h3>
                    <div class="estadistica-valor neutral">
                        <?php echo $stats_curso['promedio_curso'] ? round($stats_curso['promedio_curso'], 2) : 'S/C'; ?>
                    </div>
                    <p class="estadistica-detalle">
                        <?php if ($promedio_general && $stats_curso['promedio_curso']): ?>
                            <?php if ($promedio_general > $stats_curso['promedio_curso']): ?>
                                ↗️ Arriba del promedio
                            <?php elseif ($promedio_general < $stats_curso['promedio_curso']): ?>
                                ↘️ Debajo del promedio
                            <?php else: ?>
                                ➡️ En el promedio
                            <?php endif; ?>
                        <?php else: ?>
                            Comparación no disponible
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="estadistica-card">
                    <h3>Calificaciones</h3>
                    <div class="estadistica-valor neutral">
                        <?php echo count($calificaciones); ?>/4
                    </div>
                    <p class="estadistica-detalle">
                        bimestres calificados
                    </p>
                </div>
            </div>
            
            <!-- Historial de calificaciones -->
            <div class="historial-calificaciones">
                <h3>Historial por Bimestre</h3>
                
                <?php if (count($calificaciones) > 0): ?>
                    <div class="calificaciones-timeline">
                        <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                            <?php 
                            $calificacion_bim = null;
                            foreach ($calificaciones as $cal) {
                                if ($cal['bim'] == $bim) {
                                    $calificacion_bim = $cal;
                                    break;
                                }
                            }
                            ?>
                            <div class="timeline-item <?php echo $calificacion_bim ? 'calificado' : 'sin-calificar'; ?>">
                                <div class="timeline-marker">
                                    <span class="bimestre-numero"><?php echo $bim; ?>°</span>
                                </div>
                                
                                <div class="timeline-content">
                                    <h4><?php echo $bim; ?>° Bimestre</h4>
                                    
                                    <?php if ($calificacion_bim): ?>
                                        <div class="calificacion-detalle">
                                            <div class="nota-principal <?php echo $calificacion_bim['cali'] >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                                <?php echo htmlspecialchars($calificacion_bim['cali']); ?>
                                            </div>
                                            
                                            <div class="calificacion-info">
                                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($calificacion_bim['fecha_registro'])); ?></p>
                                                
                                                <?php if (!empty($calificacion_bim['observaciones'])): ?>
                                                    <div class="observaciones">
                                                        <strong>Observaciones:</strong>
                                                        <p><?php echo htmlspecialchars($calificacion_bim['observaciones']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="sin-calificacion-timeline">
                                            <p>Sin calificación</p>
                                            <a href="calificar_alumno.php?alumno_id=<?php echo $alumno_id; ?>&materia_id=<?php echo $materia_id; ?>" 
                                               class="btn-calificar-small">Calificar</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Gráfico de evolución (simulado con CSS) -->
                    <div class="evolucion-grafico">
                        <h4>Evolución de Calificaciones</h4>
                        <div class="grafico-container">
                            <div class="grafico-linea">
                                <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                                    <?php 
                                    $nota_bim = 0;
                                    foreach ($calificaciones as $cal) {
                                        if ($cal['bim'] == $bim) {
                                            $nota_bim = floatval($cal['cali']);
                                            break;
                                        }
                                    }
                                    $altura = $nota_bim > 0 ? ($nota_bim / 10) * 100 : 0;
                                    ?>
                                    <div class="grafico-punto" style="height: <?php echo $altura; ?>%">
                                        <div class="punto-valor <?php echo $nota_bim >= 7 ? 'aprobado' : ($nota_bim > 0 ? 'desaprobado' : 'sin-nota'); ?>">
                                            <?php echo $nota_bim > 0 ? $nota_bim : '-'; ?>
                                        </div>
                                        <div class="punto-etiqueta"><?php echo $bim; ?>° Bim</div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="grafico-escala">
                                <?php for ($i = 10; $i >= 1; $i--): ?>
                                    <div class="escala-marca <?php echo $i == 7 ? 'aprobacion' : ''; ?>"><?php echo $i; ?></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="sin-historial">
                        <div class="empty-state">
                            <div class="empty-icon">📊</div>
                            <h3>Sin calificaciones registradas</h3>
                            <p>Este alumno aún no tiene calificaciones en esta materia.</p>
                            <a href="calificar_alumno.php?alumno_id=<?php echo $alumno_id; ?>&materia_id=<?php echo $materia_id; ?>" 
                               class="btn-agregar">
                                ➕ Agregar Primera Calificación
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Acciones adicionales -->
            <div class="acciones-historial">
                <h3>Acciones</h3>
                <div class="acciones-buttons">
                    <a href="calificar_alumno.php?alumno_id=<?php echo $alumno_id; ?>&materia_id=<?php echo $materia_id; ?>" 
                       class="btn-accion">
                        📝 Modificar Calificaciones
                    </a>
                    <button class="btn-accion" onclick="generarReporteIndividual()">
                        📄 Generar Reporte Individual
                    </button>
                    <button class="btn-accion" onclick="compararConCurso()">
                        📈 Comparar con el Curso
                    </button>
                    <button class="btn-accion" onclick="enviarNotificacion()">
                        📧 Notificar al Alumno
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        function generarReporteIndividual() {
            showNotification('Función de reporte individual estará disponible próximamente', 'info');
        }
        
        function compararConCurso() {
            showNotification('Función de comparación con curso estará disponible próximamente', 'info');
        }
        
        function enviarNotificacion() {
            showNotification('Función de notificaciones estará disponible próximamente', 'info');
        }
        
        // Animar las estadísticas al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const valores = document.querySelectorAll('.estadistica-valor');
            valores.forEach((valor, index) => {
                setTimeout(() => {
                    valor.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        valor.style.transform = 'scale(1)';
                    }, 200);
                }, index * 100);
            });
        });
    </script>
</body>
</html>