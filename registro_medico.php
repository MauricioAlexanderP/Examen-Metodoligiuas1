<?php
// Incluir el archivo de configuración de la base de datos
require_once 'config/database.php';

// Inicializar variables
$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Recoger los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $especialidad = $_POST['especialidad'] ?? '';
    $horarioInicio = $_POST['horarioInicio'] ?? '';
    $horarioFin = $_POST['horarioFin'] ?? '';

    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($especialidad) || empty($horarioInicio) || empty($horarioFin)) {
      throw new Exception("Todos los campos son obligatorios.");
    }

    // Preparar la consulta SQL para insertar los datos
    $sql = "INSERT INTO medicos (nombre, apellido, especialidad, horario_inicio, horario_fin) 
                VALUES (:nombre, :apellido, :especialidad, :horario_inicio, :horario_fin)";

    // Preparar la sentencia
    $stmt = $pdo->prepare($sql);

    // Vincular parámetros
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
    $stmt->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
    $stmt->bindParam(':horario_inicio', $horarioInicio, PDO::PARAM_STR);
    $stmt->bindParam(':horario_fin', $horarioFin, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    $mensaje = "Médico registrado exitosamente.";
    $tipoMensaje = "success";
  } catch (PDOException $e) {
    $mensaje = "Error de base de datos: " . $e->getMessage();
    $tipoMensaje = "danger";
  } catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
    $tipoMensaje = "warning";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Médico</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .bg-custom {
      background-color: #f8f9fa;
    }

    .card {
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body class="bg-custom">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Sistema Médico</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" href="registro_medico.php">Registro Médico</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Cerrar Sesión</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card p-4">
          <h2 class="text-center mb-4">Registro de Médico</h2>

          <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
              <?php echo $mensaje; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre" required>
              </div>
              <div class="col-md-6">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingrese el apellido" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="especialidad" class="form-label">Especialidad</label>
              <select class="form-select" id="especialidad" name="especialidad" required>
                <option value="" selected disabled>Seleccione una especialidad</option>
                <option value="cardiologia">Cardiología</option>
                <option value="dermatologia">Dermatología</option>
                <option value="neurologia">Neurología</option>
                <option value="pediatria">Pediatría</option>
                <option value="traumatologia">Traumatología</option>
                <option value="oftalmologia">Oftalmología</option>
                <option value="otra">Otra</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="horarioAtencion" class="form-label">Horario de Atención</label>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <label for="horarioInicio" class="form-label">Hora de inicio</label>
                  <input type="time" class="form-control" id="horarioInicio" name="horarioInicio" required>
                </div>
                <div class="col-md-6 mb-2">
                  <label for="horarioFin" class="form-label">Hora de finalización</label>
                  <input type="time" class="form-control" id="horarioFin" name="horarioFin" required>
                </div>
              </div>
            </div>

            <div class="d-grid gap-2 mt-4">
              <button type="submit" class="btn btn-primary">Guardar Registro</button>
              <a href="index.php" class="btn btn-secondary">Volver</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>