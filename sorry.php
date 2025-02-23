<?php
// sorry.php

session_start();
require_once 'conexion.php';

// Obtener los datos de la confirmación
$confirmacion = null;
$error = null;

try {
    $email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
    if ($email) {
        $confirmacion = obtenerConfirmacion($conn, $email);
    }
} catch (Exception $e) {
    error_log("Error en sorry.php: " . $e->getMessage());
    $error = "Ha ocurrido un error al procesar la solicitud.";
}

/**
 * Obtiene los datos de una confirmación por email
 * @param PDO $conn
 * @param string $email
 * @return array|null
 */
function obtenerConfirmacion($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT nombre, fecha_respuesta 
        FROM confirmaciones 
        WHERE email = :email
    ");
    $stmt->execute([':email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Separar la lógica de la presentación
require_once 'templates/sorry_template.php';
