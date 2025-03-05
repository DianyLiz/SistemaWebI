<?php
require '../php/vendor/autoload.php';
include '../conexion.php';
$paginaActual = 'horarios';

// Consulta para obtener los días disponibles en el mes actual
$sqlDias = "SELECT DISTINCT diaSemana FROM HorariosMedicos";
try {
    $queryDias = $conn->prepare($sqlDias);
    $queryDias->execute();
    $diasDisponibles = $queryDias->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

// Consulta para obtener los horarios del día seleccionado
if (isset($_GET['dia'])) {
    $dia = $_GET['dia'];
    $mesActual = date('m');
    $anioActual = date('Y');
    
    // Creamos un objeto DateTime para manejar la fecha
    $fecha = new DateTime("$anioActual-$mesActual-$dia");
    $fechaFormateada = $fecha->format('Y-m-d');
    
    $sqlHorarios = "SELECT 
                        H.hora, 
                        M.nombre as medico, 
                        E.nombre as especialidad, 
                        H.cupo 
                    FROM HorariosMedicos H
                    INNER JOIN Medicos M ON H.idMedico = M.id
                    INNER JOIN Especialidades E ON M.idEspecialidad = E.id
                    WHERE H.fecha = :fecha";
    
    try {
        $queryHorarios = $conn->prepare($sqlHorarios);
        $queryHorarios->bindParam(':fecha', $fechaFormateada);
        $queryHorarios->execute();
        $horarios = $queryHorarios->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al ejecutar la consulta: " . $e->getMessage());
    }
}

$mapDiasSemana = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
    5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
];

$mesActual = date('m');
$anioActual = date('Y');
$diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Médicos</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <style>
        .filter-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
        }

        .filter-container form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-container select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            flex: 1;
            min-width: 150px;
        }

        .filter-container select:focus {
            border-color: #0b5471;
            outline: none;
        }

        .filter-container button {
            background-color: #0b5471;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .filter-container button:hover {
            background-color: #0b5471;
        }

        .table-container {
            text-align: center;
            margin: 20px auto;
            max-width: 1200px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #0b5471;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .calendar-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            max-width: 700px;
            margin: 20px auto;
        }

        .day {
            padding: 15px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .available {
            background-color: #0b5471;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .unavailable {
            background-color: #f1f1f1;
        }

        .schedule-container {
            margin-top: 20px;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <div class="filter-container">
                <form method="GET" action="">
                    <label for="diaSemana">Día de la Semana:</label>
                    <select name="diaSemana">
                        <option value="">Todos</option>
                        <?php foreach ($mapDiasSemana as $dia => $nombre) : ?>
                            <option value="<?php echo $dia; ?>" <?= isset($_GET['diaSemana']) && $_GET['diaSemana'] == $dia ? 'selected' : '' ?>><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <h2>HORARIOS MÉDICOS</h2>
                <div class="calendar-container">
                    <?php
                    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                        $fecha = new DateTime("$anioActual-$mesActual-$dia");
                        $numDiaSemana = $fecha->format('N');
                        
                        if (in_array($numDiaSemana, $diasDisponibles)) {
                            echo "<div class='day available' onclick='verHorarios($dia)'>$dia</div>";
                        } else {
                            echo "<div class='day unavailable'>$dia</div>";
                        }
                    }
                    ?>
                </div>

                <?php if (isset($_GET['dia'])) : ?>
                <div class="schedule-container">
                    <h3>Horarios del día <?php echo $_GET['dia']; ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Médico</th>
                                <th>Especialidad</th>
                                <th>Cupo Disponible</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horarios as $horario) : ?>
                            <tr>
                                <td><?php echo $horario['hora']; ?></td>
                                <td><?php echo $horario['medico']; ?></td>
                                <td><?php echo $horario['especialidad']; ?></td>
                                <td><?php echo $horario['cupo']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function verHorarios(dia) {
            window.location.href = "horarios.php?dia=" + dia;
        }
    </script>
</body>
</html>
