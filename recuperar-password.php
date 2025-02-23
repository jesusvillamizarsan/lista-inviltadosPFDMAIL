<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

require_once 'conexion.php';
require_once 'templates/header.php';
require_once 'templates/messages.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Verificar si el email existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // Generar token y establecer expiración (2 horas)
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+2 hours'));

            // Actualizar usuario con el token
            $stmt = $conn->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE email = ?");
            $stmt->execute([$token, $expiracion, $email]);

            function enviarCorreoRecuperacion($email, $token)
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
                    $enlace_recuperacion = $base_url . "/reset-password.php?token=" . $token;

                    // Contenido del correo
                    $mail->Subject = 'Recuperación de Contraseña';
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
                    <h2>Recuperación de Contraseña</h2>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                    <p><a href='{$enlace_recuperacion}' class='button'>Restablecer Contraseña</a></p>
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p>{$enlace_recuperacion}</p>
                    <p>Este enlace expirará en 2 horas.</p>
                    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                    <br>
                    <p>Saludos,<br>{$config['from_name']}</p>
                </div>
            </body>
            </html>
        ";

                    $mail->AltBody = "
            Recuperación de Contraseña
            Has solicitado restablecer tu contraseña. Visita el siguiente enlace para crear una nueva contraseña:
            {$enlace_recuperacion}
            
            Este enlace expirará en 2 horas.
            Si no solicitaste este cambio, puedes ignorar este mensaje.
        ";

                    $mail->send();
                    return true;
                } catch (Exception $e) {
                    error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);
                    throw new Exception("Error al enviar el correo de recuperación");
                }
            }

            // Enviar correo
            enviarCorreoRecuperacion($email, $token);

            mostrarMensaje(
                'success',
                '¡Correo enviado!',
                'Se ha enviado un enlace de recuperación a tu correo electrónico.'
            );
        } else {
            mostrarMensaje(
                'error',
                'Email no encontrado',
                'No existe una cuenta con ese correo electrónico.'
            );
        }
    } catch (Exception $e) {
        error_log("Error en recuperación: " . $e->getMessage());
        mostrarMensaje('error', 'Error', 'Ha ocurrido un error. Por favor, intenta más tarde.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./CSS/recovery.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card animated">
                    <div class="card-header text-center">
                        <h3>Recuperar Contraseña</h3>
                        <p>Ingresa tu correo electrónico para recibir el enlace de recuperación</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group mb-4">
                                <label for="email">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input
                                        type="email"
                                        class="form-control border-start-0"
                                        id="email"
                                        name="email"
                                        placeholder="ejemplo@correo.com"
                                        required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Enviar enlace de recuperación
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>