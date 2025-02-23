<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_mail'])) {
    header("Location: login.php");
    exit();
}

// Incluir archivo de conexión
require_once 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Invitados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .asistire {
            background-color: #90EE90 !important;
        }

        .no-asistire {
            background-color: #FFB6B6 !important;
        }

        .quizas {
            background-color: #E0E0E0 !important;
        }

        .btn-borrar {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-borrar:hover {
            background-color: #cc0000;
        }

        .resumen {
            margin-top: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 5px;
        }

        .btn-accion {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn-accion:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <h2>Lista de Invitados</h2>

    <?php
    try {
        // Manejar la solicitud de borrado
        if (isset($_POST['borrar_id'])) {
            $stmt = $conn->prepare("DELETE FROM confirmaciones WHERE id = ?");
            $stmt->execute([$_POST['borrar_id']]);
            echo "<p style='color: green;'>Registro eliminado correctamente.</p>";
        }

        // Obtener resumen de confirmaciones
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
    ?>

        <div class="resumen">
            <h3>Resumen</h3>
            <p>No podrán asistir: <?php echo $resumen['total_no_asistiran']; ?></p>
            <p>Quizás asistan: <?php echo $resumen['total_quizas']; ?></p>
            <p>Total confirmados: <?php echo $resumen['total_asistiran']; ?></p>
            <p>Total personas que asistirán: <strong><?php echo $resumen['total_personas']; ?></strong></p>
            <div style="margin: 20px 0;">
                <button onclick="window.location.href='generarPDF.php'" class="btn-accion">
                    Descargar PDF
                </button>
                <button onclick="enviarEmail()" class="btn-accion">
                    Enviar por Email a <?php echo htmlspecialchars($_SESSION['usuario_mail']); ?>
                </button>
            </div>
        </div>

        <div style="text-align: right; padding: 10px;">
            <span>Hola!, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            <a href="logout.php" style="margin-left: 20px;">Cerrar Sesión</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Asistencia</th>
                    <th>Número de Personas</th>
                    <th>Fecha de Respuesta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invitados as $invitado):
                    $clase = match ($invitado['asistencia']) {
                        'asistire' => 'asistire',
                        'no_asistire' => 'no-asistire',
                        default => 'quizas'
                    };

                    $asistencia_texto = match ($invitado['asistencia']) {
                        'asistire' => 'Allí estaré',
                        'no_asistire' => 'No podré acudir',
                        default => 'Lo intentaré'
                    };
                ?>
                    <tr class="<?php echo $clase; ?>">
                        <td><?php echo htmlspecialchars($invitado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($invitado['email']); ?></td>
                        <td><?php echo $asistencia_texto; ?></td>
                        <td><?php echo $invitado['num_personas'] ?: '-'; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($invitado['fecha_respuesta'])); ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este registro?');">
                                <input type="hidden" name="borrar_id" value="<?php echo $invitado['id']; ?>">
                                <button type="submit" class="btn-borrar">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php
    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        echo "<div style='color: red;'>Ha ocurrido un error. Por favor, contacte al administrador.</div>";
    }
    ?>

    <script>
        function enviarEmail() {
            if (confirm('¿Deseas enviar la lista de invitados por email?')) {
                fetch('enviarEmail.php')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al enviar el email');
                    });
            }
        }
    </script>
</body>

</html>