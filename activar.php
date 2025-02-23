<?php
require_once 'conexion.php';
require_once 'templates/header.php';
require_once 'templates/messages.php';

try {
    if (isset($_GET['token'])) {
        $token = $_GET['token'];

        // Buscar usuario con el token
        $stmt = $conn->prepare("
            SELECT id 
            FROM usuarios 
            WHERE token_activacion = ? AND activo = 0
        ");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            // Activar usuario
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET activo = 1, 
                    token_activacion = NULL 
                WHERE token_activacion = ?
            ");
            $stmt->execute([$token]);

            mostrarMensaje(
                'success',
                '¡Cuenta Activada!',
                'Tu cuenta ha sido activada correctamente. Ya puedes iniciar sesión.'
            );
        } else {
            mostrarMensaje(
                'error',
                'Token Inválido',
                'El enlace de activación no es válido o ya ha sido usado.'
            );
        }
    } else {
        mostrarMensaje(
            'error',
            'Token No Proporcionado',
            'No se proporcionó un token de activación.'
        );
    }
} catch (Exception $e) {
    error_log("Error en activar.php: " . $e->getMessage());
    mostrarMensaje(
        'error',
        'Error',
        'Ha ocurrido un error al activar la cuenta.'
    );
}
