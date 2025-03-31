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

        // Obtener día de la semana
        $diasSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        
        $nombreDiaIngles = $fechaObj->format('l');
        $diaSemana = $diasSemana[$nombreDiaIngles] ?? '';

        if (empty($diaSemana)) {
            throw new Exception("No se pudo determinar el día de la semana");
        }

        // Consulta SQL modificada para SQL Server
        $sql = "SELECT 
                h.idHorario,
                CONVERT(VARCHAR(5), h.horaInicio, 108) AS horaInicio,  -- Formato HH:mm
                CONCAT(u.nombre, ' ', u.apellido) AS nombreMedico,
                m.idMedico,
                COALESCE(DATEDIFF(MINUTE, h.horaInicio, h.horaFin), 60) AS duracion
            FROM HorariosMedicos h
            INNER JOIN Medicos m ON h.idMedico = m.idMedico
            INNER JOIN Usuarios u ON m.idUsuario = u.idUsuario
            INNER JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE h.diaSemana = :diaSemana 
            AND e.nombreEspecialidad = :especialidad
            AND NOT EXISTS (
                SELECT 1 FROM Citas c 
                WHERE c.idHorario = h.idHorario 
                AND CONVERT(TIME, c.hora) = CONVERT(TIME, h.horaInicio)
                AND c.estado NOT IN ('Cancelada', 'Rechazada')
            )
            ORDER BY h.horaInicio";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':diaSemana', $diaSemana, PDO::PARAM_STR);
        $stmt->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
        }

        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($horarios) > 0) {
            foreach ($horarios as $horario) {
                // Depuración: Ver los datos recibidos
                error_log(print_r($horario, true));
                
                $duracion = $horario['duracion'] > 0 ? $horario['duracion'] : 60;
                $horaInicio = !empty($horario['horaInicio']) ? $horario['horaInicio'] : 'Hora no disponible';
                
                echo "<tr>
                        <td>".htmlspecialchars($horaInicio, ENT_QUOTES, 'UTF-8')."</td>
                        <td>".htmlspecialchars($horario['nombreMedico'], ENT_QUOTES, 'UTF-8')."</td>
                        <td>".htmlspecialchars($duracion, ENT_QUOTES, 'UTF-8')." min</td>
                        <td>
                            <button class='btn-reservar' 
                                    data-horario='".htmlspecialchars($horario['idHorario'], ENT_QUOTES, 'UTF-8')."' 
                                    data-medico='".htmlspecialchars($horario['idMedico'], ENT_QUOTES, 'UTF-8')."'
                                    data-hora-inicio='".htmlspecialchars($horaInicio, ENT_QUOTES, 'UTF-8')."'
                                    data-duracion='".htmlspecialchars($duracion, ENT_QUOTES, 'UTF-8')."' >
                                Reservar
                            </button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios disponibles para ".htmlspecialchars($diaSemana, ENT_QUOTES, 'UTF-8')." (".htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8').")</td></tr>";
        }
        
    } catch (Exception $e) {
        error_log("ERROR obtenerHorarios: ".$e->getMessage());
        echo "<tr><td colspan='4' class='error'>Error: ".htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')."</td></tr>";
    }
} else {
    echo "<tr><td colspan='4' class='error'>Error: Faltan parámetros requeridos (fecha o especialidad)</td></tr>";
}

// Cerrar conexión
if (isset($conn)) {
    $conn = null;
}
?>