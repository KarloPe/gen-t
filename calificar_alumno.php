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

// Obtener calificaciones existentes del alumno en esta materia
$sql_calificaciones = "SELECT id, cali, bim, fecha_registro, observaciones 
                       FROM cali 
                       WHERE ida = $alumno_id AND idm = $materia_id 
                       ORDER BY bim";
$resultado_calificaciones = mysqli_query($conexion, $sql_calificaciones);

$calificaciones_existentes = [];
while ($cal = mysqli_fetch_assoc($resultado_calificaciones)) {
    $calificaciones_existentes[$cal['bim']] = $cal;
}

// Procesar formulario de calificación
$mensaje = '';
$tipo_mensaje = '';

if ($_POST && isset($_POST['bimestre']) && isset($_POST['calificacion'])) {
    $bimestre = (int)$_POST['bimestre'];
    $calificacion = trim($_POST['calificacion']);
    $observaciones = trim($_POST['observaciones']);
    
    // Validaciones
    if ($bimestre < 1 || $bimestre > 4) {
        $mensaje = "El bimestre debe estar entre 1 y 4.";
        $tipo_mensaje = "error";
    } elseif (empty($calificacion) || !is_numeric($calificacion)) {
        $mensaje = "La calificación es obligatoria y debe ser numérica.";
        $tipo_mensaje = "error";
    } elseif ($calificacion < 1 || $calificacion > 10) {
        $mensaje = "La calificación debe estar entre 1 y 10.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si ya existe una calificación para este bimestre
        if (isset($calificaciones_existentes[$bimestre])) {
            // Actualizar calificación existente
            $id_calificacion = $calificaciones_existentes[$bimestre]['id'];
            $sql_update = "UPDATE cali SET 
                          cali = '$calificacion', 
                          observaciones = '$observaciones',
                          fecha_registro = CURRENT_TIMESTAMP
                          WHERE id = $id_calificacion";
            
            if (mysqli_query($conexion, $sql_update)) {
                $mensaje = "Calificación actualizada correctamente para el {$bimestre}° bimestre.";
                $tipo_mensaje = "success";
                
                // Actualizar array local
                $calificaciones_existentes[$bimestre]['cali'] = $calificacion;
                $calificaciones_existentes[$bimestre]['observaciones'] = $observaciones;
            } else {
                $mensaje = "Error al actualizar la calificación: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        } else {
            // Insertar nueva calificación
            $sql_insert = "INSERT INTO cali (cali, ida, idm, bim, observaciones) 
                          VALUES ('$calificacion', $alumno_id, $materia_id, $bimestre, '$observaciones')";
            
            if (mysqli_query($conexion, $sql_insert)) {
                $mensaje = "Calificación registrada correctamente para el {$bimestre}° bimestre.";
                $tipo_mensaje = "success";
                
                // Agregar al array local
                $calificaciones_existentes[$bimestre] = [
                    'cali' => $calificacion,
                    'observaciones' => $observaciones,
                    'bim' => $bimestre
                ];
            } else {
                $mensaje = "Error al registrar la calificación: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    }
}

// Calcular promedio general
$total_calificaciones = 0;
$suma_calificaciones = 0;
foreach ($calificaciones_existentes as $cal) {
    if (!empty($cal['cali'])) {
        $suma_calificaciones += floatval($cal['cali']);
        $total_calificaciones++;
    }
}
$promedio_general = $total_calificaciones > 0 ? round($suma_calificaciones / $total_calificaciones, 2) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar - <?php echo htmlspecialchars($info['alumno_apellido'] . ', ' . $info['alumno_nombre']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-info">
                <h1>Calificar Alumno</h1>
                <p class="breadcrumb">
                    <a href="bienvenido.php">Mis Materias</a> > 
                    <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>">Alumnos</a> > 
                    Calificar
                </p>
            </div>
            <div class="header-buttons">
                <a href="ver_alumnos.php?materias_id=<?php echo $materia_id; ?>" class="btn-back">Volver a Alumnos</a>
                <a href="cerrar_sesion.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
        
        <div class="content" id="main-content">
            <div class="alumno-calificacion-info">
                <div class="alumno-header">
                    <h2><?php echo htmlspecialchars($info['alumno_apellido'] . ', ' . $info['alumno_nombre']); ?></h2>
                    <div class="materia-curso-info">
                        <p><strong>Materia:</strong> <?php echo htmlspecialchars($info['materia_nombre']); ?></p>
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
                
                <?php if ($promedio_general > 0): ?>
                    <div class="promedio-general">
                        <h3>Promedio General: 
                            <span class="promedio-valor <?php echo $promedio_general >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                <?php echo $promedio_general; ?>
                            </span>
                        </h3>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <div class="calificaciones-container">
                <div class="bimestres-grid">
                    <?php for ($bim = 1; $bim <= 4; $bim++): ?>
                        <div class="bimestre-card">
                            <h3><?php echo $bim; ?>° Bimestre</h3>
                            
                            <?php if (isset($calificaciones_existentes[$bim])): ?>
                                <div class="calificacion-existente">
                                    <div class="nota-actual">
                                        <span class="nota-numero <?php echo $calificaciones_existentes[$bim]['cali'] >= 7 ? 'aprobado' : 'desaprobado'; ?>">
                                            <?php echo htmlspecialchars($calificaciones_existentes[$bim]['cali']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($calificaciones_existentes[$bim]['observaciones'])): ?>
                                        <div class="observaciones-actuales">
                                            <strong>Observaciones:</strong>
                                            <p><?php echo htmlspecialchars($calificaciones_existentes[$bim]['observaciones']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <button class="btn-editar" onclick="editarCalificacion(<?php echo $bim; ?>)">
                                        ✏️ Editar
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="sin-calificacion">
                                    <p>Sin calificación</p>
                                    <button class="btn-agregar" onclick="agregarCalificacion(<?php echo $bim; ?>)">
                                        ➕ Agregar Nota
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Modal para agregar/editar calificación -->
            <div id="modalCalificacion" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 id="modalTitulo">Calificar Bimestre</h3>
                        <button class="modal-close" onclick="cerrarModal()">&times;</button>
                    </div>
                    
                    <form method="POST" id="formCalificacion">
                        <input type="hidden" id="bimestre" name="bimestre" value="">
                        
                        <div class="form-group">
                            <label for="calificacion">Calificación (1-10):</label>
                            <input type="number" id="calificacion" name="calificacion" min="1" max="10" step="0.1" required>
                            <div class="calificacion-indicador">
                                <span class="escala-item">1-6: Desaprobado</span>
                                <span class="escala-item">7-10: Aprobado</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones">Observaciones (opcional):</label>
                            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Comentarios sobre el desempeño del alumno..."></textarea>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                            <button type="submit" class="btn-primary">Guardar Calificación</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="acciones-adicionales">
                <h3>Acciones Adicionales</h3>
                <div class="acciones-buttons">
                    <button class="btn-accion" onclick="verHistorialCompleto()">
                        📊 Ver Historial Completo
                    </button>
                    <button class="btn-accion" onclick="generarReporte()">
                        📄 Generar Reporte
                    </button>
                    <button class="btn-accion" onclick="compararPromedio()">
                        📈 Comparar con Promedio del Curso
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        let bimestreActual = 0;
        
        function agregarCalificacion(bimestre) {
            bimestreActual = bimestre;
            document.getElementById('modalTitulo').textContent = `Calificar ${bimestre}° Bimestre`;
            document.getElementById('bimestre').value = bimestre;
            document.getElementById('calificacion').value = '';
            document.getElementById('observaciones').value = '';
            document.getElementById('modalCalificacion').style.display = 'flex';
        }
        
        function editarCalificacion(bimestre) {
            bimestreActual = bimestre;
            document.getElementById('modalTitulo').textContent = `Editar ${bimestre}° Bimestre`;
            document.getElementById('bimestre').value = bimestre;
            
            // Cargar datos existentes
            <?php foreach ($calificaciones_existentes as $bim => $cal): ?>
                if (bimestre === <?php echo $bim; ?>) {
                    document.getElementById('calificacion').value = '<?php echo $cal['cali']; ?>';
                    document.getElementById('observaciones').value = '<?php echo addslashes($cal['observaciones']); ?>';
                }
            <?php endforeach; ?>
            
            document.getElementById('modalCalificacion').style.display = 'flex';
        }
        
        function cerrarModal() {
            document.getElementById('modalCalificacion').style.display = 'none';
        }
        
        function verHistorialCompleto() {
            showNotification('Función de historial completo estará disponible próximamente', 'info');
        }
        
        function generarReporte() {
            showNotification('Función de reportes estará disponible próximamente', 'info');
        }
        
        function compararPromedio() {
            showNotification('Función de comparación estará disponible próximamente', 'info');
        }
        
        // Actualizar indicador visual de calificación en tiempo real
        document.getElementById('calificacion').addEventListener('input', function() {
            const valor = parseFloat(this.value);
            const indicadores = document.querySelectorAll('.escala-item');
            
            indicadores.forEach(ind => ind.classList.remove('active'));
            
            if (valor >= 7 && valor <= 10) {
                indicadores[1].classList.add('active');
            } else if (valor >= 1 && valor < 7) {
                indicadores[0].classList.add('active');
            }
        });
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalCalificacion').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
        
        // Validación del formulario
        document.getElementById('formCalificacion').addEventListener('submit', function(e) {
            const calificacion = parseFloat(document.getElementById('calificacion').value);
            
            if (isNaN(calificacion) || calificacion < 1 || calificacion > 10) {
                e.preventDefault();
                showNotification('La calificación debe estar entre 1 y 10', 'error');
            }
        });
    </script>
</body>
</html>