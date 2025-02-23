<?php
function mostrarMensaje($tipo, $titulo, $mensaje)
{
?>
    <div class="container">
        <div class="mensaje <?php echo $tipo; ?>">
            <h2><?php echo htmlspecialchars($titulo); ?></h2>
            <p><?php echo htmlspecialchars($mensaje); ?></p>
        </div>
        <a href="login.php" class="boton">Ir al Login</a>
    </div>
<?php
}
?>