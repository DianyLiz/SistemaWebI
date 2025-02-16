<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>
<nav>
    <div class="logo">
        MediCitas
    </div>
    <input type="checkbox" id="click">
    <label for="click" class="menu-btn">
        <i class="fas fa-bars"></i>
    </label>
    <ul class="menu">
        <li><a class="active" href="../medicos/header.php">Salir</a></li>
    </ul>
</nav>

<main>
    <div class="filter-container">
        <form method="GET" action="">
            <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>">
            <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>">
            <input type="date" name="fecha" value="<?= $fecha_filter ?>">
            <input type="time" name="hora" value="<?= $hora_filter ?>">
            <select name="estado">
                <option value="">Estado</option>
                <option value="Confirmada" <?= $estado_filter == 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                <option value="Pendiente" <?= $estado_filter == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Cancelada" <?= $estado_filter == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Tabla de Citas Médicas</h2>
        <div class="export-buttons">
            <a href="?export_pdf=true" class="btn-pdf">Exportar a PDF</a>
            <a href="?export_excel=true" class="btn-excel">Exportar a Excel</a>
            <a href="?export_word=true" class="btn-word">Exportar a Word</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mapeo de estados a clases CSS
                $estadoClases = [
                    'Confirmada' => 'confirmed',
                    'Pendiente' => 'pending',
                    'Cancelada' => 'cancelled',
                ];

                if (count($citas) > 0) {
                    foreach ($citas as $fila) {
                        $hora_formateada = date("H:i", strtotime($fila['hora']));
                        $claseEstado = $estadoClases[$fila['estado']] ?? '';

                        echo "<tr>
                                <td>{$fila['paciente']}</td>
                                <td>{$fila['medico']}</td>
                                <td>{$fila['fecha']}</td>
                                <td>{$hora_formateada}</td>
                                <td><span class='status $claseEstado'>" . ucfirst($fila['estado']) . "</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay citas registradas</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<?php
// Cerrar conexión
$conn = null;
?>

</body>
</html>