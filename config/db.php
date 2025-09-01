<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';      // Host de la base de datos (generalmente localhost en XAMPP)
$dbname = 'clinica';      // Nombre de la base de datos
$username = 'root';       // Usuario de la base de datos (por defecto 'root' en XAMPP)
$password = '';           // Contraseña (por defecto vacía en XAMPP)

// Crear conexión usando PDO
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  // Configurar el modo de error PDO para que lance excepciones
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // Configurar para que los resultados sean arrays asociativos por defecto
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  // Desactivar emulación de prepared statements
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Si llega a este punto, la conexión fue exitosa
  // echo "Conexión establecida correctamente";
} catch (PDOException $e) {
  // En caso de error en la conexión
  die("ERROR: No se pudo conectar a la base de datos. " . $e->getMessage());
}
