<?php
require_once 'conexion.php';
require_once 'templates/header.php';
require_once 'templates/messages.php';

$token = $_GET['token'] ?? '';
$tokenValido = false;

if (empty($token)) {
    mostrarMensaje('error', 'Error', 'Token no proporcionado');
    exit();
}

// Verificar token
$stmt = $conn->prepare("
    SELECT id 
    FROM usuarios 
    WHERE token_recuperacion = ? 
    AND token_expiracion > NOW()
");
$stmt->execute([$token]);

if ($stmt->rowCount() === 0) {
    mostrarMensaje('error', 'Error', 'El enlace ha expirado o no es válido');
    exit();
}

$tokenValido = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValido) {
    try {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            throw new Exception("Las contraseñas no coinciden");
        }

        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }

        // Actualizar contraseña y limpiar token
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET password = ?, 
                token_recuperacion = NULL, 
                token_expiracion = NULL 
            WHERE token_recuperacion = ?
        ");
        $stmt->execute([$hash, $token]);

        mostrarMensaje(
            'success',
            '¡Contraseña actualizada!',
            'Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.'
        );

        // Redirigir al login después de 3 segundos
        header("refresh:3;url=login.php");
        exit();
    } catch (Exception $e) {
        mostrarMensaje('error', 'Error', $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./CSS/cambiopass.css">
    <title>CambioPass</title>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card animated">
                    <div class="card-header">
                        <h3 class="text-center">Crear Nueva Contraseña</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($tokenValido): ?>
                            <form method="POST" action="" id="passwordForm">
                                <div class="form-group">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <div class="position-relative">
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="password"
                                            name="password"
                                            required>
                                        <span class="password-toggle">
                                            <i class="far fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="password-strength">
                                        <div class="password-strength-bar"></div>
                                    </div>
                                    <div class="password-feedback text-muted"></div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                    <div class="position-relative">
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="confirm_password"
                                            name="confirm_password"
                                            required>
                                        <span class="password-toggle">
                                            <i class="far fa-eye"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-submit">
                                        <i class="fas fa-lock me-2"></i>Actualizar Contraseña
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>

<!-- Agregar este script al final del documento -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para medir la fortaleza de la contraseña
        function measurePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            return strength;
        }

        // Actualizar indicador de fortaleza
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.password-strength-bar');
        const feedback = document.querySelector('.password-feedback');

        passwordInput.addEventListener('input', function() {
            const strength = measurePasswordStrength(this.value);
            strengthBar.style.width = strength + '%';

            if (strength <= 25) {
                strengthBar.style.backgroundColor = '#FF6B6B';
                feedback.textContent = 'Contraseña débil';
            } else if (strength <= 50) {
                strengthBar.style.backgroundColor = '#FFD93D';
                feedback.textContent = 'Contraseña moderada';
            } else if (strength <= 75) {
                strengthBar.style.backgroundColor = '#6BCB77';
                feedback.textContent = 'Contraseña fuerte';
            } else {
                strengthBar.style.backgroundColor = '#4ECDC4';
                feedback.textContent = 'Contraseña muy fuerte';
            }
        });

        // Toggle para mostrar/ocultar contraseña
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>