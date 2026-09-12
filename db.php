<?php
// Configuración de conexión a la base de datos.
// Ajusta estos valores según tu hosting/servidor.

$host    = 'localhost';
$db      = 'apuestaconyusme';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En producción: loguear el error en vez de mostrarlo.
    http_response_code(500);
    die('Erro de conexão com o banco de dados.');
}
