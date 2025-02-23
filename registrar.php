<?php
// registro.php

// Incluir archivos necesarios
require_once 'conexion.php';
require_once 'templates/header.php';
require_once 'templates/messages.php';
require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    // Verificar si ya existe algún usuario
    if (existeUsuario($conn)) {
        mostrarMensaje('error', 'Ya existe un usuario registrado', 'Solo se permite un usuario administrador en el sistema.');
        exit();
    }

    // Validar y procesar el registro
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        procesarRegistro($conn);
    }
} catch (Exception $e) {
    error_log("Error en registro.php: " . $e->getMessage());
    mostrarMensaje('error', 'Error', 'Ha ocurrido un error durante el registro.');
}

function existeUsuario($conn)
{
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
}

function procesarRegistro($conn)
{
    // Validar datos de entrada
    $datos = validarDatosRegistro($_POST);

    // Verificar si el email ya existe
    if (emailExiste($conn, $datos['email'])) {
        throw new Exception("Este correo electrónico ya está registrado");
    }

    // Generar token de activación
    $datos['token_activacion'] = bin2hex(random_bytes(32));

    // Insertar nuevo usuario
    $usuario_id = insertarUsuario($conn, $datos);

    // Enviar correo de activación
    if (enviarCorreoActivacion($datos['email'], $datos['token_activacion'])) {
        mostrarMensaje(
            'success',
            '¡Registro Exitoso!',
            'Se ha enviado un correo de activación a tu dirección de email. 
             Por favor, revisa tu bandeja de entrada para activar tu cuenta.'
        );
    } else {
        throw new Exception("Error al enviar el correo de activación");
    }
}

function validarDatosRegistro($post)
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

    // Validar contraseña
    if (empty($post['password']) || strlen($post['password']) < 6) {
        throw new Exception("La contraseña debe tener al menos 6 caracteres");
    }
    $datos['password'] = password_hash($post['password'], PASSWORD_DEFAULT);

    return $datos;
}

function emailExiste($conn, $email)
{
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->rowCount() > 0;
}

function insertarUsuario($conn, $datos)
{
    $stmt = $conn->prepare("
        INSERT INTO usuarios (
            nombre, 
            email, 
            password, 
            rol, 
            fecha_creacion, 
            activo, 
            token_activacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $rol = 'admin'; // O el rol que corresponda
    $fecha_creacion = date('Y-m-d H:i:s');
    $activo = 0; // Usuario inactivo hasta que se valide

    $stmt->execute([
        $datos['nombre'],
        $datos['email'],
        $datos['password'],
        $rol,
        $fecha_creacion,
        $activo,
        $datos['token_activacion']
    ]);

    return $conn->lastInsertId();
}

function enviarCorreoActivacion($email, $token)
{
    $mail = new PHPMailer(true);

    try {
        // Cargar configuración del correo
        $config = require 'config_mail.php';

        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // Configuración del correo
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        // URL base de tu sitio
        $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        $enlace_activacion = $base_url . "/activar.php?token=" . $token;

        // Contenido del correo
        $mail->Subject = 'Activa tu cuenta';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { padding: 20px; }
                    .button {
                        background-color: #4CAF50;
                        border: none;
                        color: white;
                        padding: 15px 32px;
                        text-align: center;
                        text-decoration: none;
                        display: inline-block;
                        font-size: 16px;
                        margin: 4px 2px;
                        cursor: pointer;
                        border-radius: 4px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>¡Gracias por registrarte!</h2>
                    <p>Para completar tu registro, por favor haz clic en el siguiente enlace:</p>
                    <p><a href='{$enlace_activacion}' class='button'>Activar mi cuenta</a></p>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p>{$enlace_activacion}</p>
                    <p>Si no has creado esta cuenta, puedes ignorar este mensaje.</p>
                    <br>
                    <p>Saludos,<br>{$config['from_name']}</p>
                </div>
            </body>
            </html>
        ";

        // Versión alternativa en texto plano
        $mail->AltBody = "
            Gracias por registrarte!
            Para completar tu registro, por favor visita el siguiente enlace:
            {$enlace_activacion}
            
            Si no has creado esta cuenta, puedes ignorar este mensaje.
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        throw new Exception("Error al enviar el correo de activación: " . $mail->ErrorInfo);
    }
}
