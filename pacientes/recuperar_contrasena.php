<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <form action="enviar_recuperacion.php" method="POST">
        <h2>Recuperar Contraseña</h2>
        <input type="email" name="email" placeholder="Ingresa tu correo electrónico" required>
        <button type="submit">Enviar enlace</button>
    </form>
</body>
</html>
