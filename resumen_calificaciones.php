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
    header("Location: bienvenido_noseenvio.php");
    exit();
}

$materia_id = (int)$_GET['materias_id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el profesor tenga acceso a esta materia
$sql_verificar = "SELECT m.id, m.nombre as materia_nombre, c.nombre as curso_nombre, c.idc as curso_id, c.especialidad, c.turno, c.ano, c.divi
                  FROM materias m 
                  LEFT JOIN cursos c ON m.id_cur = c.idc 
                  WHERE m.id = ? AND m.id_prof = ?";

$stmt_verificar = mysqli_prepare($conexion, $sql_verificar);
if (!$stmt_verificar) {
    die("Error en preparación de consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt_verificar, "ii", $materia_id, $usuario_id);
mysqli_stmt_execute($stmt_verificar);
$resultado_verificar = mysqli_stmt_get_result($stmt_verificar);

if (!$resultado_verificar || mysqli_num_rows($resultado_verificar) == 0) {
    header("Location: bienvenido_noverificaprofe.php");
    exit();
}

$materia_info = mysqli_fetch_assoc($resultado_verificar);
$curso_id = $materia_info['curso_id'];

// Obtener todos los alumnos del curso con sus calificaciones - Consulta simplificada
$sql_alumnos = "SELECT 
    a.id as alumno_id,
    a.nombre as alumno_nombre,
    a.apellido as alumno_apellido,
    a.email as alumno_email
FROM alumnos a
WHERE a.idc = ?
ORDER BY a.apellido, a.nombre";

$stmt_alumnos = mysqli_prepare($conexion, $sql_alumnos);
mysqli_stmt_bind_param($stmt_alumnos, "s", $curso_id);
mysqli_stmt_execute($stmt_alumnos);
$resultado_alumnos = mysqli_stmt_get_result($stmt_alumnos);

$alumnos_calificaciones = [];
$total_alumnos = 0;
$alumnos_aprobados = 0;
$suma_promedios = 0;
$promedios_calculados = 0;

while ($alumno = mysqli_fetch_assoc($resultado_alumnos)) {
    // Obtener calificaciones de cada alumno por separado
    $sql_notas = "SELECT bim, cali FROM cali WHERE ida = ? AND idm = ? ORDER BY bim";
    $stmt_notas = mysqli_prepare($conexion, $sql_notas);
    mysqli_stmt_bind_param($stmt_notas, "ii", $alumno['alumno_id'], $materia_id);
    mysqli_stmt_execute($stmt_notas);
    $resultado_notas = mysqli_stmt_get_result($stmt_notas);
    
    // Inicializar notas
    $alumno['bim1'] = null;
    $alumno['bim2'] = null;
    $alumno['bim3'] = null;
    $alumno['bim4'] = null;
    
    $suma_alumno = 0;
    $count_alumno = 0;
    
    while ($nota = mysqli_fetch_assoc($resultado_notas)) {
        $alumno['bim' . $nota['bim']] = $nota['cali'];
        if ($nota['cali'] !== null && is_numeric($nota['cali'])) {
            $suma_alumno += floatval($nota['cali']);
            $count_alumno++;
        }
    }
    
    // Calcular promedio del alumno
    $alumno['promedio'] = $count_alumno > 0 ? round($suma_alumno / $count_alumno, 2) : 0;
    
    $alumnos_calificaciones[] = $alumno;
    $total_alumnos++;
    
    if ($alumno['promedio'] > 0) {
        $suma_promedios += $alumno['promedio'];
        $promedios_calculados++;
        
        if ($alumno['promedio'] >= 7) {
            $alumnos_aprobados++;
        }
    }
}

$promedio_curso = $promedios_calculados > 0 ? round($suma_promedios / $promedios_calculados, 2) : 0;
$porcentaje_aprobacion = $total_alumnos > 0 ? round(($alumnos_aprobados / $total_alumnos) * 100, 1) : 0;

// Estadísticas por bimestre - Simplificado
$estadisticas_bimestres = [];
for ($bim = 1; $bim <= 4; $bim++) {
    $sql_stats_bim = "SELECT 
        COUNT(*) as total_calificados,
        AVG(CAST(cali AS DECIMAL(3,2))) as promedio_bimestre,
        SUM(CASE WHEN CAST(cali AS DECIMAL(3,2)) >= 7 THEN 1 ELSE 0 END) as aprobados_bimestre
        FROM cali c
        JOIN alumnos a ON c.ida = a.id
        WHERE c.idm = ? AND c.bim = ? AND a.idc = ?";
    
    $stmt_stats = mysqli_prepare($conexion, $sql_stats_bim);
    mysqli_stmt_bind_param($stmt_stats, "iis", $materia_id, $bim, $curso_id);
    mysqli_stmt_execute($stmt_stats);
    $resultado_stats = mysqli_stmt_get_result($stmt_stats);
    $stats = mysqli_fetch_assoc($resultado_stats);
    
    $estadisticas_bimestres[$bim] = [
        'total_calificados' => $stats['total_calificados'],
        'promedio' => $stats['promedio_bimestre'] ? round($stats['promedio_bimestre'], 2) : 0,
        'aprobados' => $stats['aprobados_bimestre'],
        'porcentaje_aprobacion' => $stats['total_calificados'] > 0 ? round(($stats['aprobados_bimestre'] / $stats['total_calificados']) * 100, 1) : 0
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Calificaciones - <?php echo htmlspecialchars($materia_info['materia_nombre']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-info">
                <h1>Resumen de Calificaciones</h1>
                <p class="breadcrumb">
                    <a href="bienvenido.php">Mis Materias</a> > 
                    <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>">Alumnos</a> > 
                    Resumen
                </p>
            </div>
            <div class="header-buttons">
                <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>" class="btn-back">Volver a Alumnos</a>
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <!-- Información de la materia -->
            <div class="resumen-materia-info">
                <h2><?php echo htmlspecialchars($materia_info['materia_nombre']); ?></h2>
                <div class="materia-detalles">
                    <p><strong>Curso:</strong> <?php echo htmlspecialchars($materia_info['curso_nombre']); ?></p>
                    <?php if ($materia_info['especialidad']): ?>
                        <p><strong>Especialidad:</strong> 
                            <?php echo htmlspecialchars($materia_info['especialidad'] . ' - ' . $materia_info['turno']); ?>
                            <?php if ($materia_info['ano'] && $materia_info['divi']): ?>
                                - <?php echo htmlspecialchars($materia_info['ano'] . '° ' . $materia_info['divi']); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas generales -->
            <div class="estadisticas-generales">
                <h3><br>Estadísticas Generales<br></h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_alumnos; ?></div>
                        <div class="stat-label">Total Alumnos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number <?php echo $promedio_curso >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                            <?php echo $promedio_curso ?: 'S/C'; ?>
                        </div>
                        <div class="stat-label">Promedio General</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number <?php echo $porcentaje_aprobacion >= 70 ? 'aprobado' : 'desaprobado'; ?>">
                            <?php echo $porcentaje_aprobacion; ?>%
                        </div>
                        <div class="stat-label">Aprobación</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number neutral">
                            <?php echo $promedios_calculados; ?>/<?php echo $total_alumnos; ?>
                        </div>
                        <div class="stat-label">Con Calificaciones</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de calificaciones -->
            <div class="tabla-calificaciones">
                <div class="tabla-header">
                    <h3><br>Tabla de Calificaciones<br></h3>
                    <div class="tabla-acciones">
                        <button class="btn-tabla" onclick="ordenarTabla()">🔄 Ordenar</button>
                        <button class="btn-tabla" onclick="filtrarTabla()">🔍 Filtrar</button>
                        <button class="btn-tabla" onclick="exportarTabla()">📥 Exportar</button>
                    </div>
                </div>
                
                <div class="tabla-responsive">
                    <table class="calificaciones-table">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>1° Bim</th>
                                <th>2° Bim</th>
                                <th>3° Bim</th>
                                <th>4° Bim</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos_calificaciones as $alumno): ?>
                                <tr class="fila-alumno">
                                    <td class="alumno-nombre">
                                        <strong><?php echo htmlspecialchars($alumno['alumno_apellido'] . ', ' . $alumno['alumno_nombre']); ?></strong>
                                        <small>DNI: <?php echo htmlspecialchars($alumno['alumno_id']); ?></small>
                                    </td>
                                    
                                    <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                                        <td class="calificacion-cell">
                                            <?php 
                                            $nota = $alumno["bim$bim"];
                                            if ($nota !== null): 
                                            ?>
                                                <span class="nota <?php echo $nota >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                                    <?php echo $nota; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="sin-nota">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    
                                    <td class="promedio-cell">
                                        <?php if ($alumno['promedio'] > 0): ?>
                                            <span class="promedio <?php echo $alumno['promedio'] >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                                <?php echo $alumno['promedio']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="sin-promedio">S/C</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="estado-cell">
                                        <?php if ($alumno['promedio'] > 0): ?>
                                            <span class="estado <?php echo $alumno['promedio'] >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                                <?php echo $alumno['promedio'] >= 7 ? 'Aprobado' : 'Desaprobado'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="estado pendiente">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="acciones-cell">
                                        <div class="acciones-tabla">
                                            <a href="calificar_alumno.php?alumno_id=<?php echo $alumno['alumno_id']; ?>&materia_id=<?php echo $materia_id; ?>" 
                                               class="btn-accion-tabla calificar" title="Calificar">📝</a>
                                            <a href="historial_alumno.php?alumno_id=<?php echo $alumno['alumno_id']; ?>&materia_id=<?php echo $materia_id; ?>" 
                                               class="btn-accion-tabla historial" title="Ver Historial">📊</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Estadísticas por bimestre -->
            <div class="estadisticas-bimestres">
                <h3><br>Rendimiento por Bimestre<br></h3>
                <div class="bimestres-stats-grid">
                    <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                        <div class="bimestre-stat-card">
                            <h4><?php echo $bim; ?>° Bimestre</h4>
                            <div class="bim-stats">
                                <div class="bim-stat">
                                    <span class="bim-numero <?php echo $estadisticas_bimestres[$bim]['promedio'] >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                        <?php echo $estadisticas_bimestres[$bim]['promedio'] ?: 'S/C'; ?>
                                    </span>
                                    <span class="bim-label">Promedio</span>
                                </div>
                                <div class="bim-stat">
                                    <span class="bim-numero neutral">
                                        <?php echo $estadisticas_bimestres[$bim]['total_calificados']; ?>
                                    </span>
                                    <span class="bim-label">Calificados</span>
                                </div>
                                <div class="bim-stat">
                                    <span class="bim-numero <?php echo $estadisticas_bimestres[$bim]['porcentaje_aprobacion'] >= 70 ? 'aprobado' : 'desaprobado'; ?>">
                                        <?php echo $estadisticas_bimestres[$bim]['porcentaje_aprobacion']; ?>%
                                    </span>
                                    <span class="bim-label">Aprobación</span>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Acciones del resumen -->
            <div class="acciones-resumen">
                <h3><br><br>Acciones del Resumen<br></h3>
                
                <div class="acciones-buttons">
                    <button class="btn-accion" onclick="generarReporteCompleto()">
                        📄 Generar Reporte Completo
                    </button>
                    <button class="btn-accion" onclick="enviarResumenEmail()">
                        📧 Enviar por Email
                    </button>
                    <button class="btn-accion" onclick="compararPeriodos()">
                        📈 Comparar Períodos
                    </button>
                    <button class="btn-accion" onclick="estadisticasAvanzadas()">
                        🔬 Estadísticas Avanzadas
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        function ordenarTabla() {
            showNotification('Función de ordenamiento estará disponible próximamente', 'info');
        }
        
        function filtrarTabla() {
            showNotification('Función de filtrado estará disponible próximamente', 'info');
        }
        
        function exportarTabla() {
            showNotification('Función de exportación estará disponible próximamente', 'info');
        }
        
        function generarReporteCompleto() {
            showNotification('Función de reporte completo estará disponible próximamente', 'info');
        }
        
        function enviarResumenEmail() {
            showNotification('Función de envío por email estará disponible próximamente', 'info');
        }
        
        function compararPeriodos() {
            showNotification('Función de comparación estará disponible próximamente', 'info');
        }
        
        function estadisticasAvanzadas() {
            showNotification('Función de estadísticas avanzadas estará disponible próximamente', 'info');
        }
        
        // Sistema básico de notificaciones
        function showNotification(message, type) {
            alert(message);
        }
        
        // Animar estadísticas al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const stats = document.querySelectorAll('.stat-number, .bim-numero');
            stats.forEach((stat, index) => {
                setTimeout(() => {
                    if (stat.style) {
                        stat.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            stat.style.transform = 'scale(1)';
                        }, 200);
                    }
                }, index * 50);
            });
        });
    </script>
</body>
</html>