<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado y es paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'paciente') {
    header("Location: index.php");
    exit;
}

// Incluir el archivo de configuración de la base de datos
require_once 'config/database.php';

// Obtener información del paciente
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Obtener las citas del paciente con información del doctor y especialidad (limitado a las últimas 3)
$sql = "SELECT c.*, c.fecha, c.hora_inicio, c.hora_fin, c.estado,
               CONCAT(d.nombre, ' ', d.apellidos) as doctor_nombre,
               e.nombre as especialidad
        FROM citas c
        JOIN doctores d ON c.doctor_id = d.id
        JOIN especialidades e ON d.especialidad_id = e.id
        WHERE c.usuario_id = :usuario_id
        ORDER BY c.fecha DESC, c.hora_inicio DESC
        LIMIT 3";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$citas = $stmt->fetchAll();

// Separar citas por estado
$citas_pendientes = [];
$citas_realizadas = [];
$citas_canceladas = [];

foreach ($citas as $cita) {
    switch ($cita['estado']) {
        case 'pendiente':
            $citas_pendientes[] = $cita;
            break;
        case 'realizada':
            $citas_realizadas[] = $cita;
            break;
        case 'cancelada':
            $citas_canceladas[] = $cita;
            break;
    }
}

// Procesar acciones de cancelar y editar
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        $cita_id = $_POST['cita_id'] ?? 0;

        try {
            if ($accion === 'cancelar') {
                // Cancelar cita
                $sql_cancelar = "UPDATE citas SET estado = 'cancelada' WHERE id = :cita_id AND usuario_id = :usuario_id AND estado = 'pendiente'";
                $stmt_cancelar = $pdo->prepare($sql_cancelar);
                $stmt_cancelar->bindParam(':cita_id', $cita_id, PDO::PARAM_INT);
                $stmt_cancelar->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

                if ($stmt_cancelar->execute() && $stmt_cancelar->rowCount() > 0) {
                    $mensaje = "Cita cancelada exitosamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "No se pudo cancelar la cita. Verifique que la cita exista y esté pendiente.";
                    $tipo_mensaje = "error";
                }
            } elseif ($accion === 'editar') {
                // Editar cita
                $nueva_fecha = $_POST['nueva_fecha'] ?? '';
                $nueva_hora = $_POST['nueva_hora'] ?? '';

                if (!empty($nueva_fecha) && !empty($nueva_hora)) {
                    $nueva_fecha_hora = $nueva_fecha . ' ' . $nueva_hora . ':00';

                    $sql_editar = "UPDATE citas SET fecha = :nueva_fecha, hora_inicio = :nueva_fecha_hora 
                                   WHERE id = :cita_id AND usuario_id = :usuario_id AND estado = 'pendiente'";
                    $stmt_editar = $pdo->prepare($sql_editar);
                    $stmt_editar->bindParam(':nueva_fecha', $nueva_fecha);
                    $stmt_editar->bindParam(':nueva_fecha_hora', $nueva_fecha_hora);
                    $stmt_editar->bindParam(':cita_id', $cita_id, PDO::PARAM_INT);
                    $stmt_editar->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

                    if ($stmt_editar->execute() && $stmt_editar->rowCount() > 0) {
                        $mensaje = "Cita reagendada exitosamente.";
                        $tipo_mensaje = "success";
                    } else {
                        $mensaje = "No se pudo reagendar la cita.";
                        $tipo_mensaje = "error";
                    }
                } else {
                    $mensaje = "Por favor complete todos los campos para reagendar.";
                    $tipo_mensaje = "error";
                }
            }

            // Recargar las citas después de la acción
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $citas = $stmt->fetchAll();

            // Volver a separar por estado
            $citas_pendientes = [];
            $citas_realizadas = [];
            $citas_canceladas = [];

            foreach ($citas as $cita) {
                switch ($cita['estado']) {
                    case 'pendiente':
                        $citas_pendientes[] = $cita;
                        break;
                    case 'realizada':
                        $citas_realizadas[] = $cita;
                        break;
                    case 'cancelada':
                        $citas_canceladas[] = $cita;
                        break;
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error de base de datos: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas Médicas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .logout-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .patient-info {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .patient-card {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .patient-details h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .patient-details p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .appointments-section {
            padding: 30px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .appointment-card.upcoming {
            border-left-color: #28a745;
        }

        .appointment-card.today {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fff9c4, #ffffff);
        }

        .appointment-card.completed {
            border-left-color: #6c757d;
            opacity: 0.8;
        }

        .appointment-card.cancelled {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #ffeaa7, #ffffff);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .appointment-date {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .appointment-time {
            font-size: 1rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-upcoming {
            background: #d4edda;
            color: #155724;
        }

        .status-today {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .appointment-details {
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .detail-item strong {
            color: #495057;
            margin-right: 8px;
            min-width: 80px;
        }

        .appointment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
            text-decoration: none;
        }

        .appointment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            align-items: center;
        }

        .appointment-actions form {
            margin: 0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            grid-column: 1 / -1;
        }

        .notification {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .logout-btn {
                position: static;
                margin-top: 15px;
                display: inline-block;
            }

            .appointments-grid {
                grid-template-columns: 1fr;
            }

            .patient-card {
                flex-direction: column;
                text-align: center;
            }

            .appointment-header {
                flex-direction: column;
                gap: 10px;
            }

            .appointment-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <a href="logout.php" class="logout-btn">🚪 Cerrar Sesión</a>
            <h1>🏥 Mis Citas Médicas</h1>
            <p>Portal del Paciente - Consulta tu horario de atención</p>
        </div>

        <div class="patient-info">
            <div class="patient-card">
                <div class="avatar"><?php echo strtoupper(substr($usuario_nombre, 0, 2)); ?></div>
                <div class="patient-details">
                    <h3><?php echo htmlspecialchars($usuario_nombre); ?></h3>
                    <p>ID: <?php echo $usuario_id; ?> | Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                </div>
            </div>
        </div>

        <div class="appointments-section">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'danger'; ?>" style="background: <?php echo $tipo_mensaje === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensaje === 'success' ? '#155724' : '#721c24'; ?>; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid <?php echo $tipo_mensaje === 'success' ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="notification">
                <strong>📅 Recordatorio:</strong> Se recomienda llegar 15 minutos antes de su cita programada.
            </div>

            <h2 class="section-title">
                📋 Mis Últimas 3 Citas
            </h2>

            <div class="appointments-grid">
                <?php if (empty($citas)): ?>
                    <div class="empty-state">
                        <p>🗓️ No tienes citas registradas en este momento.</p>
                    </div>
                <?php elseif (empty($citas_pendientes) && empty($citas_canceladas) && empty($citas_realizadas)): ?>
                    <div class="empty-state">
                        <p>🗓️ No tienes citas en tus últimos registros.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($citas_pendientes as $cita): ?>
                        <?php
                        $fecha = new DateTime($cita['fecha']);
                        $hora_inicio = new DateTime($cita['hora_inicio']);
                        $fecha_formateada = $fecha->format('j \d\e F Y');
                        $hora_formateada = $hora_inicio->format('g:i A');

                        // Determinar si es hoy
                        $es_hoy = $fecha->format('Y-m-d') === date('Y-m-d');
                        $clase_card = $es_hoy ? 'today' : 'upcoming';
                        $estado_badge = $es_hoy ? 'status-today' : 'status-upcoming';
                        ?>
                        <div class="appointment-card <?php echo $clase_card; ?>">
                            <div class="appointment-header">
                                <div>
                                    <div class="appointment-date">📅 <?php echo $fecha_formateada; ?></div>
                                    <div class="appointment-time">⏰ <?php echo $hora_formateada; ?></div>
                                </div>
                                <span class="status-badge <?php echo $estado_badge; ?>">Pendiente</span>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <strong>👨‍⚕️ Médico:</strong> Dr. <?php echo htmlspecialchars($cita['doctor_nombre']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>🏥 Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>🕐 Hora:</strong> <?php echo $hora_formateada; ?>
                                </div>
                                <div class="detail-item">
                                    <strong>📋 Estado:</strong> <span style="color: <?php echo $es_hoy ? '#856404' : '#155724'; ?>; font-weight: bold;">Pendiente</span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <button type="button" class="btn btn-primary" onclick="mostrarModalEditar(<?php echo $cita['id']; ?>, '<?php echo $cita['fecha']; ?>', '<?php echo date('H:i', strtotime($cita['hora_inicio'])); ?>')">
                                    ✏️ Editar
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="accion" value="cancelar">
                                    <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('¿Está seguro de que desea cancelar esta cita?')">
                                        ❌ Cancelar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php foreach ($citas_canceladas as $cita): ?>
                    <?php
                    $fecha = new DateTime($cita['fecha']);
                    $hora_inicio = new DateTime($cita['hora_inicio']);
                    $fecha_formateada = $fecha->format('j \d\e F Y');
                    $hora_formateada = $hora_inicio->format('g:i A');
                    ?>
                    <div class="appointment-card cancelled">
                        <div class="appointment-header">
                            <div>
                                <div class="appointment-date">📅 <?php echo $fecha_formateada; ?></div>
                                <div class="appointment-time">⏰ <?php echo $hora_formateada; ?></div>
                            </div>
                            <span class="status-badge status-cancelled">Cancelada</span>
                        </div>
                        <div class="appointment-details">
                            <div class="detail-item">
                                <strong>👨‍⚕️ Médico:</strong> Dr. <?php echo htmlspecialchars($cita['doctor_nombre']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>🏥 Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>🕐 Hora:</strong> <?php echo $hora_formateada; ?>
                            </div>
                            <div class="detail-item">
                                <strong>📋 Estado:</strong> <span style="color: #721c24; font-weight: bold;">Cancelada</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($citas_realizadas)): ?>
                <h2 class="section-title" style="margin-top: 40px;">
                    ✅ Historial de Citas
                </h2>

                <div class="appointments-grid">
                    <?php foreach ($citas_realizadas as $cita): ?>
                        <?php
                        $fecha = new DateTime($cita['fecha']);
                        $hora_inicio = new DateTime($cita['hora_inicio']);
                        $fecha_formateada = $fecha->format('j \d\e F Y');
                        $hora_formateada = $hora_inicio->format('g:i A');
                        ?>
                        <div class="appointment-card completed">
                            <div class="appointment-header">
                                <div>
                                    <div class="appointment-date">📅 <?php echo $fecha_formateada; ?></div>
                                    <div class="appointment-time">⏰ <?php echo $hora_formateada; ?></div>
                                </div>
                                <span class="status-badge status-completed">Realizada</span>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <strong>👨‍⚕️ Médico:</strong> Dr. <?php echo htmlspecialchars($cita['doctor_nombre']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>🏥 Especialidad:</strong> <?php echo htmlspecialchars($cita['especialidad']); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>🕐 Hora:</strong> <?php echo $hora_formateada; ?>
                                </div>
                                <div class="detail-item">
                                    <strong>📋 Estado:</strong> <span style="color: #383d41; font-weight: bold;">Realizada</span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <a href="#" class="btn btn-secondary">📋 Ver detalles</a>
                                <a href="#" class="btn btn-primary">📄 Informe médico</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para editar cita -->
    <div id="modalEditar" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 15px; width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">✏️ Editar Cita</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="cita_id" id="editarCitaId">

                <div style="margin-bottom: 15px;">
                    <label for="nueva_fecha" style="display: block; margin-bottom: 5px; font-weight: bold;">📅 Nueva Fecha:</label>
                    <input type="date" id="nueva_fecha" name="nueva_fecha" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="nueva_hora" style="display: block; margin-bottom: 5px; font-weight: bold;">🕐 Nueva Hora:</label>
                    <input type="time" id="nueva_hora" name="nueva_hora" required style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModalEditar()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        ❌ Cancelar
                    </button>
                    <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        ✅ Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mostrarModalEditar(citaId, fechaActual, horaActual) {
            document.getElementById('editarCitaId').value = citaId;
            document.getElementById('nueva_fecha').value = fechaActual;
            document.getElementById('nueva_hora').value = horaActual;
            document.getElementById('modalEditar').style.display = 'block';
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                cerrarModalEditar();
            }
        }

        // Prevenir fechas pasadas
        document.addEventListener('DOMContentLoaded', function() {
            var hoy = new Date().toISOString().split('T')[0];
            document.getElementById('nueva_fecha').setAttribute('min', hoy);
        });
    </script>
</body>

</html>