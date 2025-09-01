<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'clinica';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
  $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch (PDOException $e) {
  die('Error de conexión: ' . $e->getMessage());
}
