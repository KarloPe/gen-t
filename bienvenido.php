<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: acceso_errado.php?error=sesion_expirada");
    exit();
}

/*
// Si es admin, redirigir al panel de administración
if ($_SESSION['usuario'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}
*/

// Obtener MATERIAS asignadas al ID usuario (profesor) con información del curso
// $sql_materias = "SELECT m.id, m.nombre as materia_nombre, c.nombre as curso_nombre, c.idc as curso_id, c.especialidad, c.turno, c.ano, c.divi
// $sql_materias = "SELECT m.*, c.nombre as curso_nombre, c.idc as curso_id, c.especialidad, c.turno, c.ano, c.divi
$usuario_id = $_SESSION['usuario_id'];
$sql_materias = "SELECT m.id, m.nombre, m.id_prof, m.id_cur, c.nombre as curso_nombre, c.idc as curso_id, c.especialidad, c.turno, c.ano, c.divi
                 FROM materias m 
                 LEFT JOIN cursos c ON m.id_cur = c.idc 
                 WHERE m.id_prof = $usuario_id 
                 ORDER BY c.nombre, m.nombre";
$resultado_materias = mysqli_query($conexion, $sql_materias);

// Agrupar materias por curso
$materias_por_curso = array();
$total_materias = 0;

if (mysqli_num_rows($resultado_materias) > 0) {
    while ($materia = mysqli_fetch_assoc($resultado_materias)) {
        $curso_key = $materia['curso_nombre'] ? $materia['curso_nombre'] : 'Sin Curso Asignado';
        $materias_por_curso[$curso_key][] = $materia;
        $total_materias++;
    }
}

// Obtener estadísticas del profesor
$sql_stats = "SELECT 
    COUNT(DISTINCT m.id) as total_materias,
    COUNT(DISTINCT c.idc) as total_cursos,
    COUNT(DISTINCT a.id) as total_alumnos
    FROM materias m 
    LEFT JOIN cursos c ON m.id_cur = c.idc 
    LEFT JOIN alumnos a ON a.idc = c.idc
    WHERE m.id_prof = $usuario_id";
$resultado_stats = mysqli_query($conexion, $sql_stats);
$stats = mysqli_fetch_assoc($resultado_stats);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Materias - Sistema de Calificaciones</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-info">
                <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
                <p class="user-role">Profesor - <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></p>
            </div>
            <div class="header-buttons">
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
          

            <h2>Mis Materias</h2>
            
            <?php if (!empty($materias_por_curso)): ?>
                <div class="cursos-columns-container">
                    <?php foreach ($materias_por_curso as $curso_nombre => $materias): ?>
                        <div class="curso-column">
                            <div class="curso-header">
                                <h3><?php echo htmlspecialchars($curso_nombre); ?></h3>
                                <?php if ($materias[0]['especialidad']): ?>
                                    <p class="curso-details">
                                        <?php echo htmlspecialchars($materias[0]['especialidad']); ?> - 
                                        <?php echo htmlspecialchars($materias[0]['turno']); ?>
                                        <?php if ($materias[0]['ano'] && $materias[0]['divi']): ?>
                                            - <?php echo htmlspecialchars($materias[0]['ano']); ?>° <?php echo htmlspecialchars($materias[0]['divi']); ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="materias-list">
                                <?php foreach ($materias as $materia): ?>
                                    <div class="materia-item">
                                        <h4><?php echo htmlspecialchars($materia['nombre']); ?></h4>
                                        <div class="materia-actions">
                                            <a href="ver_alumnos.php?materias_id=<?php echo $materia['id']; ?>" class="btn-ver-alumnos">
                                                Ver Alumnos
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-cursos">
                    <h3>No tienes materias asignadas</h3>
                    <p>No tienes materias asignadas actualmente.</p>
                    <p class="info-adicional">Contacta al administrador para que te asigne materias y cursos.</p>
                </div>
            <?php endif; ?>

              <!-- Estadísticas del profesor -->
            <h1> </h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_materias']; ?></div>
                    <div class="stat-label">Materias</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_cursos']; ?></div>
                    <div class="stat-label">Cursos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_alumnos']; ?></div>
                    <div class="stat-label">Alumnos</div>
                </div>
            </div>



        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>