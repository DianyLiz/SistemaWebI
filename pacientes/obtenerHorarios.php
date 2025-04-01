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
        if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
            throw new Exception("Formato de fecha inválido");
        }

        // Obtener día de la semana en español
        $fechaObj = new DateTime($fecha);
        $diasSemana = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        $diaSemana = $diasSemana[$fechaObj->format('l')] ?? '';
        
        if (empty($diaSemana)) {
            throw new Exception("No se pudo determinar el día de la semana");
        }

        // 1. Obtener todos los horarios base para ese día y especialidad
        $sqlHorarios = "SELECT 
                h.idHorario,
                CONVERT(VARCHAR(8), h.horaInicio, 108) AS horaInicio,
                CONVERT(VARCHAR(8), h.horaFin, 108) AS horaFin,
                CONCAT(u.nombre, ' ', u.apellido) AS medico,
                m.idMedico
            FROM HorariosMedicos h
            INNER JOIN Medicos m ON h.idMedico = m.idMedico
            INNER JOIN Usuarios u ON m.idUsuario = u.idUsuario
            INNER JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE h.diaSemana = :diaSemana
            AND e.nombreEspecialidad = :especialidad
            ORDER BY h.horaInicio";

        $stmtHorarios = $conn->prepare($sqlHorarios);
        $stmtHorarios->bindParam(':diaSemana', $diaSemana, PDO::PARAM_STR);
        $stmtHorarios->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
        $stmtHorarios->execute();
        $bloquesHorarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

        if (count($bloquesHorarios) == 0) {
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios programados para ".htmlspecialchars($diaSemana, ENT_QUOTES, 'UTF-8')."</td></tr>";
            exit;
        }

        // 2. Obtener todas las citas existentes para la fecha seleccionada
        $sqlCitas = "SELECT 
                FORMAT(hora, 'HH:mm') AS hora_cita,
                idHorario
            FROM Citas
            WHERE CAST(hora AS DATE) = CAST(:fecha AS DATE)
            AND estado NOT IN ('Cancelada', 'Rechazada')";

        $stmtCitas = $conn->prepare($sqlCitas);
        $stmtCitas->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtCitas->execute();
        $citasOcupadas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

        // 3. Procesar cada bloque horario
        foreach ($bloquesHorarios as $bloque) {
            $horaInicio = DateTime::createFromFormat('H:i:s', $bloque['horaInicio']);
            $horaFin = DateTime::createFromFormat('H:i:s', $bloque['horaFin']);
            
            // Mostrar encabezado del bloque
            echo "<tr><td colspan='4' class='bloque-horario'>Horario de "
                .$horaInicio->format('H:i')." a "
                .$horaFin->format('H:i')." - "
                .htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')
                ."</td></tr>";

            // Generar intervalos exactos para este bloque
            $horaActual = clone $horaInicio;
            
            while ($horaActual < $horaFin) {
                $horaFormato = $horaActual->format('H:i');
                
                // Verificar si este horario está ocupado
                $ocupado = false;
                foreach ($citasOcupadas as $cita) {
                    if ($cita['hora_cita'] == $horaFormato && $cita['idHorario'] == $bloque['idHorario']) {
                        $ocupado = true;
                        break;
                    }
                }

                // Mostrar fila según disponibilidad
                if ($ocupado) {
                    echo "<tr>
                            <td>".$horaFormato."</td>
                            <td>".htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')."</td>
                            <td>60 min</td>
                            <td><span class='btn-ocupado'>Cupo ya reservado</span></td>
                        </tr>";
                } else {
                    echo "<tr>
                            <td>".$horaFormato."</td>
                            <td>".htmlspecialchars($bloque['medico'], ENT_QUOTES, 'UTF-8')."</td>
                            <td>60 min</td>
                            <td>
                                <button class='btn-reservar' 
                                        data-horario='".htmlspecialchars($bloque['idHorario'], ENT_QUOTES, 'UTF-8')."' 
                                        data-medico='".htmlspecialchars($bloque['idMedico'], ENT_QUOTES, 'UTF-8')."'
                                        data-hora-inicio='".$horaFormato."'>
                                    Reservar
                                </button>
                            </td>
                        </tr>";
                }

                // Avanzar exactamente 60 minutos
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