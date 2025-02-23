<?php
include 'session-control.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
</head>

<body>
    <header class="barra-superior">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="logo">MediCitas</div>
        <div class="usuario">
            <span><?php echo "(Médico) " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido']; ?></span>
            <a href="../cerrar-sesion.php" class="btn-salir">Salir</a>
        </div>
    </header>
</body>

</html>