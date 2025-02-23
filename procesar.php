<?php
// procesar_confirmacion.php

// Iniciar sesión si es necesario para mensajes flash
session_start();

// Incluir el archivo de conexión
require_once 'conexion.php';

try {
    // Validar y sanitizar los datos de entrada
    $datos = validarDatosEntrada($_POST);

    // Verificar si el email ya existe
    if (emailExiste($conn, $datos['email'])) {
        // Redirigir a la página de error
        header("Location: sorry.php?email=" . urlencode($datos['email']));
        exit();
    }

    // Insertar nueva confirmación
    insertarConfirmacion($conn, $datos);

    // Redirigir a la página de éxito
    header("Location: attemptshanks.php");
    exit();
} catch (Exception $e) {
    error_log("Error en procesar_confirmacion.php: " . $e->getMessage());
    $_SESSION['error'] = "Ha ocurrido un error al procesar su confirmación. Por favor, inténtelo de nuevo.";
    header("Location: error.php");
    exit();
}

/**
 * Valida y sanitiza los datos de entrada
 * @param array $post Datos del formulario
 * @return array Datos validados
 * @throws Exception si los datos son inválidos
 */
function validarDatosEntrada($post)
{
    $datos = [];

    // Validar nombre
    if (empty($post['nombre'])) {
        throw new Exception("El nombre es requerido");
    }
    $datos['nombre'] = filter_var(trim($post['nombre']), FILTER_SANITIZE_STRING);

    // Validar email
    if (empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    $datos['email'] = filter_var(trim($post['email']), FILTER_SANITIZE_EMAIL);

    // Validar teléfono
    if (empty($post['telefono'])) {
        throw new Exception("El teléfono es requerido");
    }
    $datos['telefono'] = filter_var(trim($post['telefono']), FILTER_SANITIZE_STRING);

    // Validar asistencia
    if (!in_array($post['asistencia'], ['asistire', 'no_asistire', 'quizas'])) {
        throw new Exception("Opción de asistencia inválida");
    }
    $datos['asistencia'] = $post['asistencia'];

    // Validar número de personas
    $datos['num_personas'] = null;
    if ($datos['asistencia'] === 'asistire') {
        if (!empty($post['personas']) && is_numeric($post['personas'])) {
            $datos['num_personas'] = (int)$post['personas'];
        } else {
            throw new Exception("Número de personas inválido");
        }
    }

    return $datos;
}

/**
 * Verifica si un email ya existe en la base de datos
 * @param PDO $conn Conexión a la base de datos
 * @param string $email Email a verificar
 * @return bool
 */
function emailExiste($conn, $email)
{
    $stmt = $conn->prepare("SELECT 1 FROM confirmaciones WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetchColumn() !== false;
}

/**
 * Inserta una nueva confirmación en la base de datos
 * @param PDO $conn Conexión a la base de datos
 * @param array $datos Datos validados
 * @throws PDOException si hay un error en la base de datos
 */
function insertarConfirmacion($conn, $datos)
{
    $stmt = $conn->prepare("
        INSERT INTO confirmaciones 
            (nombre, email, telefono, asistencia, num_personas) 
        VALUES 
            (:nombre, :email, :telefono, :asistencia, :num_personas)
    ");

    $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':email' => $datos['email'],
        ':telefono' => $datos['telefono'],
        ':asistencia' => $datos['asistencia'],
        ':num_personas' => $datos['num_personas']
    ]);
}
