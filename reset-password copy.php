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

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Nueva Contraseña</h3>
                </div>
                <div class="card-body">
                    <?php if ($tokenValido): ?>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="password">Nueva Contraseña:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="confirm_password">Confirmar Contraseña:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>