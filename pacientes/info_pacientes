<?php
$fecha = $_GET['fecha'];
$especialidad = $_GET['especialidad'];
$medico = $_GET['idMedico'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Información del Paciente</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <main class="contenido">
        <div class="form-container">
            <h2>Información del Paciente</h2>
            <form action="guardar_paciente.php" method="POST">
                <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                <input type="hidden" name="especialidad" value="<?php echo $especialidad; ?>">
                <input type="hidden" name="medico" value="<?php echo $medico; ?>">

                <div class="form-group">
                    <label for="dpi">DPI / Pasaporte / ID Asegurado:</label>
                    <input type="text" id="dpi" name="dpi" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" required>
                </div>
                <div class="form-group">
                    <label for="motivo_consulta">Motivo de Consulta:</label>
                    <textarea id="motivo_consulta" name="motivo_consulta" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <button type="button" class="btn-regresar" onclick="window.history.back();">Regresar</button>
                    <button type="submit" class="btn-guardar">Guardar</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>