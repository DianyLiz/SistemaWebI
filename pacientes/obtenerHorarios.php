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

        // Validación de fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj) {
            throw new Exception("Formato de fecha inválido");
        }

        // Consulta SQL para obtener horarios
        $sqlHorarios = "SELECT 
                h.idHorario,
                h.idMedico,
                CONVERT(VARCHAR(8), h.horaInicio, 108) AS horaInicio,
                CONVERT(VARCHAR(8), h.horaFin, 108) AS horaFin,
                h.diaSemana,
                h.fecha,
                h.cupos,
                CONCAT(u.nombre, ' ', u.apellido) AS medico,
                e.nombreEspecialidad
            FROM HorariosMedicos h
            INNER JOIN Medicos m ON h.idMedico = m.idMedico
            INNER JOIN Usuarios u ON m.idUsuario = u.idUsuario
            INNER JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE (h.fecha = :fecha OR (h.fecha IS NULL AND h.diaSemana = (
                    SELECT CASE DATEPART(WEEKDAY, :fechaParam)
                        WHEN 1 THEN 'Domingo'
                        WHEN 2 THEN 'Lunes'
                        WHEN 3 THEN 'Martes'
                        WHEN 4 THEN 'Miércoles'
                        WHEN 5 THEN 'Jueves'
                        WHEN 6 THEN 'Viernes'
                        WHEN 7 THEN 'Sábado'
                    END
                )))
            AND e.nombreEspecialidad = :especialidad
            ORDER BY h.horaInicio";

        $stmtHorarios = $conn->prepare($sqlHorarios);
        $stmtHorarios->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':fechaParam', $fecha, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
        
        if (!$stmtHorarios->execute()) {
            throw new Exception("Error al obtener horarios: " . implode(" ", $stmtHorarios->errorInfo()));
        }

        $bloquesHorarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        if (count($bloquesHorarios) == 0) {
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios programados para esta fecha</td></tr>";
            exit;
        }

        // Consulta para obtener citas existentes
        $sqlCitas = "SELECT 
                c.idHorario,
                FORMAT(c.hora, 'HH:mm') AS hora_cita,
                COUNT(*) AS cupos_ocupados
            FROM Citas c
            WHERE CAST(c.hora AS DATE) = CAST(:fecha AS DATE)
            AND c.estado NOT IN ('Cancelada', 'Rechazada')
            GROUP BY c.idHorario, FORMAT(c.hora, 'HH:mm')";

        $stmtCitas = $conn->prepare($sqlCitas);
        $stmtCitas->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        
        if (!$stmtCitas->execute()) {
            throw new Exception("Error al obtener citas: " . implode(" ", $stmtCitas->errorInfo()));
        }

        $citasOcupadas = $stmtCitas->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);

        // Procesar cada bloque horario
        foreach ($bloquesHorarios as $bloque) {
            $horaInicio = DateTime::createFromFormat('H:i:s', $bloque['horaInicio']);
            $horaFin = DateTime::createFromFormat('H:i:s', $bloque['horaFin']);
            
            $tipoHorario = $bloque['fecha'] ? "Horario específico" : "Horario regular (" . $bloque['diaSemana'] . ")";
            
            echo "<tr><td colspan='4' class='bloque-horario'>$tipoHorario de "
                .$horaInicio->format('H:i')." a "
                .$horaFin->format('H:i')." - "
                .htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')
                ." (Cupos: ".($bloque['cupos'] ?: 'Sin límite').")</td></tr>";

            $horaActual = clone $horaInicio;
            $idHorario = $bloque['idHorario'];
            
            while ($horaActual < $horaFin) {
                $horaFormato = $horaActual->format('H:i');
                
                $ocupado = false;
                $cuposDisponibles = $bloque['cupos'];
                
                if (isset($citasOcupadas[$idHorario][$horaFormato])) {
                    if ($cuposDisponibles && $citasOcupadas[$idHorario][$horaFormato]['cupos_ocupados'] >= $cuposDisponibles) {
                        $ocupado = true;
                    }
                }

                echo "<tr>
                        <td>".$horaFormato."</td>
                        <td>".htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')."</td>
                        <td>60 min</td>
                        <td>";
                
                if ($ocupado) {
                    echo "<span class='btn-ocupado'>Cupo lleno</span>";
                } else {
                    echo "<button class='btn-reservar' 
                            data-horario='".htmlspecialchars($idHorario, ENT_QUOTES, 'UTF-8')."' 
                            data-medico='".htmlspecialchars($bloque['idMedico'], ENT_QUOTES, 'UTF-8')."'
                            data-hora-inicio='".$horaFormato."'
                            data-cupos='".htmlspecialchars($bloque['cupos'], ENT_QUOTES, 'UTF-8')."'
                            data-disponible='".($ocupado ? 'false' : 'true')."'
                            ".($ocupado ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '').">
                          Reservar
                        </button>";
                }
                
                echo "</td></tr>";

                $horaActual->add(new DateInterval('PT60M'));
            }
        }
        
    } catch (Exception $e) {
        error_log("ERROR obtenerHorarios: ".$e->getMessage());
        echo "<tr><td colspan='4' class='error'>Error: ".htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')."</td></tr>";
    }
} else {
    echo "<tr><td colspan='4' class='error'>Error: Faltan parámetros requeridos</td></tr>";
}

$conn = null;
?>