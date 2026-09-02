<?php
/**
 * Ligação à base de dados - Centro Médico Santa Victória
 * Ajusta as credenciais consoante o teu ambiente (local ou hospedagem).
 */

$DB_HOST = 'localhost';
$DB_NAME = 'santa_victoria';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Em produção não mostres o erro completo ao utilizador.
    error_log('Erro de ligação à BD: ' . $e->getMessage());
    $pdo = null;
}
