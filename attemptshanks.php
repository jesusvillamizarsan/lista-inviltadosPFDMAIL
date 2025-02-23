<?php
session_start();

// Incluir el archivo de conexión
require_once 'conexion.php';

try {
    // Obtener la última confirmación
    $stmt = $conn->query("SELECT nombre, asistencia FROM confirmaciones ORDER BY fecha_respuesta DESC LIMIT 1");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nombre = htmlspecialchars($resultado['nombre']);
        $asistencia = $resultado['asistencia'];

        echo '<div style="text-align: center; padding: 50px;">';

        if ($asistencia === 'asistire') {
            echo "<h2>¡Gracias {$nombre} por confirmar tu asistencia!</h2>";
        } elseif ($asistencia === 'no_asistire') {
            echo "<h2>Gracias {$nombre} por responder a la invitación.</h2>";
        } elseif ($asistencia === 'lo_intentare') {
            echo "<h2>Gracias {$nombre} por responder a la invitación.</h2>";
        }

        echo '</div>';
    } else {
        echo "<h2>No se encontró ninguna confirmación.</h2>";
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
}

// No es necesario cerrar la conexión explícitamente en PHP, pero si quieres hacerlo:
$conn = null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu respuesta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(./assets/FONDO\ FORMULARIO\ DESKTOP.png);
        }

        div {
            background-color: rgba(5, 0, 0, 0.5);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 10% auto 0 auto;
            max-width: 600px;
        }

        h2 {
            color: #FFF;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
</body>

</html>