<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "confirmarevento";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4"); // Para asegurar la correcta codificación de caracteres
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Error de conexión: " . $e->getMessage()]);
    exit;
}
