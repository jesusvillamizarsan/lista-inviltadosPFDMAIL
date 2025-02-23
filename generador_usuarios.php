<?PHP
$password = "kreatika2024";

// Hashear la contraseña
$hash = password_hash($password, PASSWORD_DEFAULT);

// Mostrar el hash generado
echo $hash;
