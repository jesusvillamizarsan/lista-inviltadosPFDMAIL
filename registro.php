<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
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
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <form id="registroForm" action="registrar.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" id="nombre" name="nombre" required>
                <div id="errorNombre" class="error">Por favor ingrese un nombre válido</div>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" required>
                <div id="errorEmail" class="error">Por favor ingrese un email válido</div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
                <div id="errorPassword" class="error">La contraseña debe tener al menos 8 caracteres</div>
            </div>

            <div class="form-group">
                <label for="confirmar_password">Confirmar contraseña:</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required>
                <div id="errorConfirmar" class="error">Las contraseñas no coinciden</div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>

    <script>
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            let isValid = true;
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmarPassword = document.getElementById('confirmar_password').value;

            // Validar nombre
            if (nombre.length < 3) {
                document.getElementById('errorNombre').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('errorNombre').style.display = 'none';
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('errorEmail').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('errorEmail').style.display = 'none';
            }

            // Validar contraseña
            if (password.length < 8) {
                document.getElementById('errorPassword').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('errorPassword').style.display = 'none';
            }

            // Validar confirmación de contraseña
            if (password !== confirmarPassword) {
                document.getElementById('errorConfirmar').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('errorConfirmar').style.display = 'none';
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>