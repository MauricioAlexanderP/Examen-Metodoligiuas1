<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
  header("Location: index.php");
  exit();
}

// Configuración de la conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "clinica";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// Consultas para el dashboard
$total_citas = $conn->query("SELECT COUNT(*) AS total FROM citas")->fetch_assoc()['total'];
$citas_canceladas = $conn->query("SELECT COUNT(*) AS total FROM citas WHERE estado='cancelada'")->fetch_assoc()['total'];
$citas_realizadas = $conn->query("SELECT COUNT(*) AS total FROM citas WHERE estado='realizada'")->fetch_assoc()['total'];
$citas_pendientes = $conn->query("SELECT COUNT(*) AS total FROM citas WHERE estado='pendiente'")->fetch_assoc()['total'];
$pacientes_atendidos = $conn->query("SELECT COUNT(DISTINCT usuario_id) AS total FROM citas WHERE estado='realizada'")->fetch_assoc()['total'];
$citas_reprogramadas = $conn->query("SELECT COUNT(*) AS total FROM citas WHERE estado IN ('pendiente','cancelada')")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Reportes de Citas</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
      color: white;
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

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 40px;
    }

    .card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%);
    }

    .card.cancelled::before {
      background: linear-gradient(90deg, #e67e22 0%, #d35400 100%);
    }

    .card.rescheduled::before {
      background: linear-gradient(90deg, #27ae60 0%, #229954 100%);
    }

    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      font-size: 24px;
      color: white;
    }

    .card.total .card-icon {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .card.cancelled .card-icon {
      background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    }

    .card.rescheduled .card-icon {
      background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    }

    .card-title {
      font-size: 1.1rem;
      color: #666;
      margin-bottom: 15px;
      font-weight: 500;
    }

    .card-value {
      font-size: 3rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 10px;
    }

    .card.total .card-value {
      color: #e74c3c;
    }

    .card.cancelled .card-value {
      color: #e67e22;
    }

    .card.rescheduled .card-value {
      color: #27ae60;
    }

    .card-subtitle {
      font-size: 0.9rem;
      color: #999;
    }

    .summary-section {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      margin-top: 30px;
    }

    .summary-title {
      font-size: 1.5rem;
      color: #333;
      margin-bottom: 20px;
      text-align: center;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .summary-item {
      text-align: center;
      padding: 20px;
      border-radius: 15px;
      background: #f8f9fa;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .summary-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .summary-item h3 {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .summary-item .value {
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
    }

    .report-button {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      color: white;
      border: none;
      padding: 15px 30px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 20px auto;
      display: block;
      min-width: 200px;
    }

    .report-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(231, 76, 60, 0.6);
      background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    }

    .report-button:active {
      transform: translateY(0);
    }

    .button-container {
      text-align: center;
      margin: 30px 0;
    }

    @media (max-width: 768px) {
      .header h1 {
        font-size: 2rem;
      }

      .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .card {
        padding: 20px;
      }

      .card-value {
        font-size: 2.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <h1>Dashboard de Reportes</h1>
      <p>Gestión y seguimiento de citas médicas</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-grid">
      <div class="card total">
        <div class="card-icon">📊</div>
        <div class="card-title">Total de Citas</div>
        <div class="card-value"><?php echo $total_citas; ?></div>
        <div class="card-subtitle">Citas registradas este mes</div>
      </div>
      <div class="card cancelled">
        <div class="card-icon">❌</div>
        <div class="card-title">Citas Canceladas</div>
        <div class="card-value"><?php echo $citas_canceladas; ?></div>
        <div class="card-subtitle">Citas no realizadas</div>
      </div>
      <div class="card rescheduled">
        <div class="card-icon">🔄</div>
        <div class="card-title">Citas Reprogramadas</div>
        <div class="card-value"><?php echo $citas_reprogramadas; ?></div>
        <div class="card-subtitle">Citas reagendadas</div>
      </div>
    </div>

    <!-- Botón para Generar Reporte -->
    <div class="button-container">
      <button class="report-button" onclick="generarReporte()">📊 Generar Reporte</button>
    </div>

    <!-- Resumen Adicional -->
    <div class="summary-section">
      <h2 class="summary-title">Resumen del Período</h2>
      <div class="summary-grid">
        <div class="summary-item">
          <h3>Citas Completadas</h3>
          <div class="value"><?php echo $citas_realizadas; ?></div>
        </div>
        <div class="summary-item">
          <h3>Citas Pendientes</h3>
          <div class="value"><?php echo $citas_pendientes; ?></div>
        </div>
        <div class="summary-item">
          <h3>Total Procesadas</h3>
          <div class="value"><?php echo $total_citas - $citas_pendientes; ?></div>
        </div>
        <div class="summary-item">
          <h3>Pacientes Atendidos</h3>
          <div class="value"><?php echo $pacientes_atendidos; ?></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function generarReporte() {
      alert('Función para generar reporte - aquí implementarás tu lógica');
    }
  </script>
</body>

</html>
<?php $conn->close(); ?>