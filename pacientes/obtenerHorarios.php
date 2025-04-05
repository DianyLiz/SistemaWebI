<?php
include '../conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!$conn) {
    echo "<tr><td colspan='4' class='error'>Error de conexión a la base de datos</td></tr>";
    exit;
}

if (isset($_POST['fecha']) && isset($_POST['especialidad'])) {
    try {
        $fecha = $_POST['fecha'];
        $especialidad = $_POST['especialidad'];

        // Registrar datos recibidos
        error_log("Fecha recibida: " . $fecha);
        error_log("Especialidad recibida: " . $especialidad);

        // Validar fecha
        if (empty($fecha)) {
            throw new Exception("El campo 'fecha' está vacío");
        }

        if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
            throw new Exception("Formato de fecha inválido (YYYY-MM-DD)");
        }

        // Obtener día de la semana
        $diaSemanaNumero = date('N', strtotime($fecha));
        $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $diaSemanaNombre = $diasSemana[$diaSemanaNumero];

        // Consultar horarios
        $sqlHorarios = "SELECT 
                h.idHorario,
                h.idMedico,
                h.horaInicio,
                h.horaFin,
                h.diaSemana,
                h.fecha,
                h.cupos,
                CONCAT(u.nombre, ' ', u.apellido) AS medico
            FROM HorariosMedicos h
            JOIN Medicos m ON h.idMedico = m.idMedico
            JOIN Usuarios u ON m.idUsuario = u.idUsuario
            JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE (h.fecha = ? OR (h.fecha IS NULL AND h.diaSemana = ?))
            AND e.nombreEspecialidad = ?
            ORDER BY h.horaInicio";

        $stmtHorarios = $conn->prepare($sqlHorarios);
        $stmtHorarios->execute([$fecha, $diaSemanaNombre, $especialidad]);
        $bloquesHorarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        if (empty($bloquesHorarios)) {
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios disponibles para esta fecha</td></tr>";
            exit;
        }

        // Consultar citas existentes
        $sqlCitas = "SELECT 
                idHorario,
                CONVERT(VARCHAR(8), hora, 108) AS hora_cita,
                COUNT(*) AS citas_activas
            FROM Citas
            WHERE CONVERT(DATE, hora) = ?
            AND estado = 'activo'
            GROUP BY idHorario, CONVERT(VARCHAR(8), hora, 108)";

        $stmtCitas = $conn->prepare($sqlCitas);
        $stmtCitas->execute([$fecha]);
        $citasOcupadas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

        $citasPorHorario = [];
        foreach ($citasOcupadas as $cita) {
            $citasPorHorario[$cita['idHorario']][$cita['hora_cita']] = $cita['citas_activas'];
        }

        // Mostrar horarios
        foreach ($bloquesHorarios as $bloque) {
            $horaInicio = DateTime::createFromFormat('H:i:s', substr($bloque['horaInicio'], 0, 8));
            $horaFin = DateTime::createFromFormat('H:i:s', substr($bloque['horaFin'], 0, 8));

            if (!$horaInicio || !$horaFin) {
                error_log("Formato de hora inválido en el bloque: " . json_encode($bloque));
                continue;
            }

            echo "<tr><td colspan='4'><strong>Horario de " . 
                 $horaInicio->format('H:i') . " a " . 
                 $horaFin->format('H:i') . " - " . 
                 htmlspecialchars($bloque['medico']) . 
                 ($bloque['cupos'] ? " (Cupos: {$bloque['cupos']})" : "") . 
                 "</strong></td></tr>";

            $horaActual = clone $horaInicio;
            $intervalo = new DateInterval('PT60M');

            while ($horaActual < $horaFin) {
                $horaFormato = $horaActual->format('H:i');
                $ocupado = false;

                if (isset($citasPorHorario[$bloque['idHorario']][$horaFormato . ':00'])) {
                    $ocupados = $citasPorHorario[$bloque['idHorario']][$horaFormato . ':00'];
                    $ocupado = ($bloque['cupos'] && $ocupados >= $bloque['cupos']);
                }

                echo "<tr>
                        <td>{$horaFormato}</td>
                        <td>" . htmlspecialchars($bloque['medico']) . "</td>
                        <td>60 min</td>
                        <td>";

                echo $ocupado 
                    ? "<span class='btn-ocupado'>No disponible</span>"
                    : "<button class='btn-reservar' 
                            data-horario='{$bloque['idHorario']}' 
                            data-medico='{$bloque['idMedico']}'
                            data-hora-inicio='{$horaFormato}'
                            data-cupos='" . ($bloque['cupos'] ?? '') . "'>
                          Reservar
                        </button>";

                echo "</td></tr>";
                $horaActual->add($intervalo);
            }
        }

    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        echo "<tr><td colspan='4' class='error'>Error en la base de datos</td></tr>";
    } catch (Exception $e) {
        error_log("Error general: " . $e->getMessage());
        echo "<tr><td colspan='4' class='error'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
} else {
    echo "<tr><td colspan='4' class='error'>Faltan parámetros requeridos</td></tr>";
}

$conn = null;
?>