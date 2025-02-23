<?php
// validar_login.php

session_start();
require_once 'conexion.php';

try {
    // Validar datos de entrada
    $credenciales = validarCredenciales($_POST);

    // Intentar autenticar al usuario
    $usuario = autenticarUsuario($conn, $credenciales);

    if ($usuario) {
        manejarLoginExitoso($conn, $usuario);
    } else {
        manejarLoginFallido($conn, $credenciales['email']);
    }
} catch (Exception $e) {
    error_log("Error en validar_login.php: " . $e->getMessage());
    mostrarError("Ha ocurrido un error durante el inicio de sesión.");
}

/**
 * Valida las credenciales de entrada
 * @param array $post
 * @return array
 * @throws Exception
 */
function validarCredenciales($post)
{
    if (empty($post['email']) || empty($post['password'])) {
        throw new Exception("Todos los campos son requeridos");
    }

    return [
        'email' => filter_var($post['email'], FILTER_SANITIZE_EMAIL),
        'password' => $post['password']
    ];
}

/**
 * Autentica al usuario
 * @param PDO $conn
 * @param array $credenciales
 * @return array|false
 */
function autenticarUsuario($conn, $credenciales)
{
    $stmt = $conn->prepare("
        SELECT * 
        FROM usuarios 
        WHERE email = :email
    ");

    $stmt->execute([':email' => $credenciales['email']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($credenciales['password'], $usuario['password'])) {
        return $usuario;
    }

    return false;
}

/**
 * Maneja el proceso de login exitoso
 * @param PDO $conn
 * @param array $usuario
 */
function manejarLoginExitoso($conn, $usuario)
{
    // Verificar si la cuenta está activa
    if (!$usuario['activo']) {
        mostrarError("Esta cuenta está desactivada. Por favor contacte al administrador.");
    }

    // Resetear intentos fallidos y actualizar último acceso
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET intentos_fallidos = 0, 
            ultimo_acceso = CURRENT_TIMESTAMP 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $usuario['id']]);

    // Iniciar sesión
    iniciarSesion($usuario);

    // Redireccionar
    header("Location: listainvitados.php");
    exit();
}

/**
 * Maneja el proceso de login fallido
 * @param PDO $conn
 * @param string $email
 */
function manejarLoginFallido($conn, $email)
{
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $intentos = $usuario['intentos_fallidos'] + 1;

        // Actualizar intentos fallidos
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET intentos_fallidos = :intentos 
            WHERE id = :id
        ");
        $stmt->execute([
            ':intentos' => $intentos,
            ':id' => $usuario['id']
        ]);

        // Bloquear cuenta si hay demasiados intentos
        if ($intentos >= 5) {
            bloquearCuenta($conn, $usuario['id']);
            mostrarError("Cuenta bloqueada por demasiados intentos fallidos. Contacte al administrador.");
        }
    }

    mostrarError("Email o contraseña incorrectos");
}

/**
 * Bloquea una cuenta de usuario
 * @param PDO $conn
 * @param int $userId
 */
function bloquearCuenta($conn, $userId)
{
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET activo = 0 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $userId]);
}

/**
 * Inicia la sesión del usuario
 * @param array $usuario
 */
function iniciarSesion($usuario)
{
    $_SESSION['usuario_mail'] = $usuario['email'];
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol'] = $usuario['rol'];

    // Agregar timestamp de inicio de sesión
    $_SESSION['login_time'] = time();
}

/**
 * Muestra un mensaje de error y termina la ejecución
 * @param string $mensaje
 */
function mostrarError($mensaje)
{
    require_once 'templates/error_template.php';
    exit();
}
