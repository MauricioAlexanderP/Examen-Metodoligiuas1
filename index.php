<?php
// Iniciar sesión
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['usuario_id'])) {
  if ($_SESSION['usuario_rol'] === 'paciente') {
    header("Location: vistaPaciente.php");
  } elseif ($_SESSION['usuario_rol'] === 'medico') {
    header("Location: vistaDoctor.php");
  } else {
    header("Location: dashboard.php");
  }
  exit;
}

// Incluir el archivo de configuración de la base de datos
require_once 'config/database.php';

// Inicializar variables
$error = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Recoger los datos del formulario
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Validación básica
    if (empty($usuario) || empty($clave)) {
      throw new Exception("Por favor ingrese usuario y contraseña.");
    }

    // Preparar la consulta para buscar el usuario
    $sql = "SELECT id, usuario, password, nombre, rol FROM usuarios WHERE usuario = :usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();

    // Verificar si el usuario existe
    if ($stmt->rowCount() === 1) {
      $user = $stmt->fetch();

      // Verificar la contraseña (sin encriptado)
      if ($clave === $user['password']) {
        // Iniciar sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];

        // Redirigir según el rol del usuario
        if ($user['rol'] === 'paciente') {
          header("Location: vistaPaciente.php");
        } elseif ($user['rol'] === 'medico') {
          header("Location: vistaDoctor.php");
        } else {
          header("Location: dashboard.php");
        }
        exit;
      } else {
        $error = "Contraseña incorrecta.";
      }
    } else {
      $error = "Usuario no encontrado.";
    }
  } catch (PDOException $e) {
    $error = "Error de base de datos: " . $e->getMessage();
  } catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistema Clínica</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg p-4 rounded-4" style="width: 22rem;">
    <h3 class="text-center mb-4">Iniciar Sesión</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅ Sesión cerrada exitosamente.</strong> ¡Hasta pronto!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
      </div>
      <div class="mb-3">
        <label for="contraseña" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="contraseña" name="clave" placeholder="Ingresa tu contraseña" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>