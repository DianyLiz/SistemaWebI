<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <form action="actualizar_contrasena.php" method="POST">
        <h2>Cambiar Contraseña</h2>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
        <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña" required>
        <button type="submit">Actualizar contraseña</button>
    </form>
</body>
</html>