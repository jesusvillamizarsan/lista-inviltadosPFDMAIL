<?php
// templates/error_template.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de inicio de sesión</title>
    <link rel="stylesheet" href="./CSS/error.css">
</head>

<body>
    <div class="error-container">
        <h2>Error de inicio de sesión</h2>
        <p class="error-message"><?php echo htmlspecialchars($mensaje); ?></p>
        <a href="login.php" class="volver-btn">Volver al login</a>
    </div>
</body>

</html>