<?php
session_start();
require 'config/database.php'; // Usar conexión PDO desde config/database.php

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado. Por favor, inicie sesión.");
}

$doctor_id = $_SESSION['usuario_id'];

// Consulta: solo citas del doctor de hoy, ordenadas por hora_inicio
$hoy = date('Y-m-d');
$sql = "SELECT c.hora_inicio, c.hora_fin, u.nombre AS paciente, c.motivo
        FROM citas c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.doctor_id = :doctor_id AND c.fecha = :fecha
        ORDER BY c.hora_inicio ASC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->bindParam(':fecha', $hoy, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas de Hoy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Mis citas programadas para hoy (<?php echo $hoy; ?>)</h2>
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Paciente</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($result) > 0): ?>
                <?php foreach($result as $fila): ?>
                    <tr>
                        <td><?php echo date('H:i', strtotime($fila['hora_inicio'])); ?></td>
                        <td><?php echo date('H:i', strtotime($fila['hora_fin'])); ?></td>
                        <td><?php echo htmlspecialchars($fila['paciente']); ?></td>
                        <td><?php echo htmlspecialchars($fila['motivo']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No tienes citas programadas para hoy.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
