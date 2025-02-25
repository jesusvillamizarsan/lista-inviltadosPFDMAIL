<?php
// Incluir el archivo de conexión
require_once 'conexion.php';

try {
    // Usar la conexión ($conn) del archivo conexion.php
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsuarios = $resultado['total'];

    // Si necesitas devolver el resultado (por ejemplo, para una API)

} catch (PDOException $e) {
    error_log("Error en check_users.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos'
    ]);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm" action="validar_login.php" method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Ingresar</button>
            <div class="text-center mt-3">
                <a href="recuperar-password.php">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
        <?php
        if ($totalUsuarios == !1) {
            echo '<div class="register-link">';
            echo '<p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>';
            echo '</div>';
        }
        ?>



    </div>
</body>

</html>