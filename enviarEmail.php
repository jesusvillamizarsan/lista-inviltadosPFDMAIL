<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();

// Verificar que el usuario está autenticado y que el email está en la sesión
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_mail'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado o email no disponible']);
    exit;
}

// Cargar configuración
$config = require 'config_mail.php';

function generarContenidoHTML($invitados, $resumen)
{
    $html = '<h2 style="color: #333;">Lista de Invitados</h2>';

    // Agregar resumen
    $html .= '<div style="margin: 20px 0; padding: 10px; background-color: #f4f4f4;">';
    $html .= "<h3>Resumen</h3>";
    $html .= "<p>Total confirmados: {$resumen['total_asistiran']}</p>";
    $html .= "<p>No podrán asistir: {$resumen['total_no_asistiran']}</p>";
    $html .= "<p>Quizás asistan: {$resumen['total_quizas']}</p>";
    $html .= "<p>Total personas que asistirán: <strong>{$resumen['total_personas']}</strong></p>";
    $html .= '</div>';

    // Crear tabla
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
    $html .= '<tr style="background-color: #f4f4f4;">';
    $html .= '<th style="padding: 10px; border: 1px solid #ddd;">Nombre</th>';
    $html .= '<th style="padding: 10px; border: 1px solid #ddd;">Email</th>';
    $html .= '<th style="padding: 10px; border: 1px solid #ddd;">Asistencia</th>';
    $html .= '<th style="padding: 10px; border: 1px solid #ddd;">Personas</th>';
    $html .= '<th style="padding: 10px; border: 1px solid #ddd;">Fecha</th>';
    $html .= '</tr>';

    foreach ($invitados as $invitado) {
        $bgColor = '';
        switch ($invitado['asistencia']) {
            case 'asistire':
                $bgColor = '#90EE90';
                break;
            case 'no_asistire':
                $bgColor = '#FFB6B6';
                break;
            case 'quizas':
                $bgColor = '#E0E0E0';
                break;
        }

        $html .= "<tr style='background-color: $bgColor;'>";
        $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$invitado['nombre']}</td>";
        $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>{$invitado['email']}</td>";
        $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>" .
            ($invitado['asistencia'] == 'asistire' ? 'Allí estaré' : ($invitado['asistencia'] == 'no_asistire' ? 'No podré' : 'Lo intentaré')) . "</td>";
        $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>" .
            ($invitado['num_personas'] ?: '-') . "</td>";
        $html .= "<td style='padding: 10px; border: 1px solid #ddd;'>" .
            date('d/m/Y', strtotime($invitado['fecha_respuesta'])) . "</td>";
        $html .= '</tr>';
    }

    $html .= '</table>';
    return $html;
}

try {
    // Conectar a la base de datos y obtener datos
    require_once 'conexion.php';
    // Obtener resumen
    $stmt = $conn->query("SELECT 
        SUM(CASE WHEN asistencia = 'asistire' THEN 1 ELSE 0 END) as total_asistiran,
        SUM(CASE WHEN asistencia = 'no_asistire' THEN 1 ELSE 0 END) as total_no_asistiran,
        SUM(CASE WHEN asistencia = 'quizas' THEN 1 ELSE 0 END) as total_quizas,
        SUM(CASE WHEN asistencia = 'asistire' THEN num_personas ELSE 0 END) as total_personas
        FROM confirmaciones");
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener lista de invitados
    $stmt = $conn->query("SELECT * FROM confirmaciones ORDER BY fecha_respuesta DESC");
    $invitados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configurar PHPMailer
    $mail = new PHPMailer(true);

    // Configuración del servidor
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Cambia a DEBUG_SERVER para depuración
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($_SESSION['usuario_mail']); // Cambiado a usuario_mail

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Lista de Invitados - ' . date('d/m/Y');
    $mail->Body = generarContenidoHTML($invitados, $resumen);
    $mail->AltBody = 'Esta es una versión de texto plano del correo.';

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email enviado correctamente a ' . $_SESSION['usuario_mail']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Error al enviar email: {$mail->ErrorInfo}"]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Error de base de datos: {$e->getMessage()}"]);
}
