<?php
// templates/sorry_template.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta ya registrada</title>
    <link rel="stylesheet" href="./CSS/sorry.css">
</head>

<body>
    <div class="mensaje-container">
        <?php if ($error): ?>
            <h2>Error</h2>
            <p class="mensaje"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($confirmacion): ?>
            <h2>Respuesta ya registrada</h2>
            <p class="mensaje">
                Lo sentimos <?php echo htmlspecialchars($confirmacion['nombre']); ?>,
                ya has respondido anteriormente a esta invitación.
            </p>
            <p class="fecha">
                Fecha de respuesta:
                <?php echo date('d/m/Y H:i:s', strtotime($confirmacion['fecha_respuesta'])); ?>
            </p>
        <?php else: ?>
            <h2>Error en la consulta</h2>
            <p class="mensaje">
                No se ha podido encontrar la información de la respuesta.
            </p>
        <?php endif; ?>

        <a href="index.php" class="volver-btn">Volver al inicio</a>
    </div>
</body>

</html>