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

        // Obtener el día de la semana (1=Lunes, 7=Domingo)
        $diaSemanaNumero = date('N', strtotime($fecha));
        $diasSemana = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        $diaSemanaNombre = $diasSemana[$diaSemanaNumero];

        // Consulta SQL para obtener horarios disponibles
        $sqlHorarios = "SELECT 
                h.idHorario,
                h.idMedico,
                h.horaInicio,
                h.horaFin,
                h.diaSemana,
                h.fecha,
                h.cupos,
                CONCAT(u.nombre, ' ', u.apellido) AS medico,
                e.nombreEspecialidad
            FROM HorariosMedicos h
            INNER JOIN Medicos m ON h.idMedico = m.idMedico
            INNER JOIN Usuarios u ON m.idUsuario = u.idUsuario
            INNER JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE (h.fecha = :fecha OR (h.fecha IS NULL AND h.diaSemana = :diaSemana))
            AND e.nombreEspecialidad = :especialidad
            ORDER BY h.horaInicio";

        $stmtHorarios = $conn->prepare($sqlHorarios);
        $stmtHorarios->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':diaSemana', $diaSemanaNombre, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
        
        if (!$stmtHorarios->execute()) {
            throw new Exception("Error al obtener horarios: " . implode(" ", $stmtHorarios->errorInfo()));
        }

        $bloquesHorarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        if (count($bloquesHorarios) == 0) {
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios programados para esta fecha</td></tr>";
            exit;
        }

        // Consulta para obtener citas ya reservadas en esos horarios
        $sqlCitas = "SELECT 
                c.idHorario,
                CAST(c.hora AS TIME) AS hora_cita,
                COUNT(*) AS cupos_ocupados
            FROM Citas c
            WHERE CAST(c.hora AS DATE) = CAST(:fecha AS DATE)
            AND c.estado NOT IN ('Cancelada', 'Rechazada')
            GROUP BY c.idHorario, CAST(c.hora AS TIME)";

        $stmtCitas = $conn->prepare($sqlCitas);
        $stmtCitas->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        
        if (!$stmtCitas->execute()) {
            throw new Exception("Error al obtener citas: " . implode(" ", $stmtCitas->errorInfo()));
        }

        $citasOcupadas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
        $citasOcupadasPorHorario = [];
        foreach ($citasOcupadas as $cita) {
            $citasOcupadasPorHorario[$cita['idHorario']][$cita['hora_cita']] = $cita['cupos_ocupados'];
        }

        // Procesar cada bloque de horario
        foreach ($bloquesHorarios as $bloque) {
            // Recortar el formato de hora
            $horaInicioFormateada = substr($bloque['horaInicio'], 0, 8);
            $horaFinFormateada = substr($bloque['horaFin'], 0, 8);

            $horaInicio = DateTime::createFromFormat('H:i:s', $horaInicioFormateada);
            $horaFin = DateTime::createFromFormat('H:i:s', $horaFinFormateada);

            // Validación de formato de hora
            if (!$horaInicio || !$horaFin) {
                throw new Exception("Formato de hora inválido en horaInicio: " . $bloque['horaInicio'] . " o horaFin: " . $bloque['horaFin']);
            }

            $tipoHorario = $bloque['fecha'] ? "Horario específico" : "Horario regular (" . $bloque['diaSemana'] . ")";
            
            echo "<tr><td colspan='4' class='bloque-horario'>$tipoHorario de "
                .$horaInicio->format('H:i')." a "
                .$horaFin->format('H:i')." - "
                .htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')
                ." (Cupos: ".($bloque['cupos'] ?: 'Sin límite').")</td></tr>";

            $horaActual = clone $horaInicio;
            $idHorario = $bloque['idHorario'];

            // Duración fija de 60 minutos
            $duracion = new DateInterval('PT60M');

            while ($horaActual < $horaFin) {
                $horaFormato = $horaActual->format('H:i');
                
                // Verificar si el horario está ocupado
                $ocupado = false;
                $cuposDisponibles = $bloque['cupos'] ?? null;
                $ocupados = 0;

                if (isset($citasOcupadasPorHorario[$idHorario][$horaFormato])) {
                    $ocupados = $citasOcupadasPorHorario[$idHorario][$horaFormato];
                }

                if ($cuposDisponibles === NULL || $cuposDisponibles == 0) {
                    $ocupado = false; // Sin límite de cupos, siempre disponible
                } else if ($ocupados >= $cuposDisponibles) {
                    $ocupado = true; // Verificar si los ocupados exceden los disponibles
                } else {
                    $ocupado = false; // No está lleno
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
                            data-disponible='true'>
                          Reservar
                        </button>";
                }
                
                echo "</td></tr>";

                $horaActual->add($duracion);
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