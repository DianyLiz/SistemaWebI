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

        // Validar fecha
        if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
            throw new Exception("Formato de fecha inválido");
        }

        // Obtener día de la semana en español
        $fechaObj = new DateTime($fecha);
        $nombreDiaIngles = $fechaObj->format('l');
        $diasSemana = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        $diaSemana = $diasSemana[$nombreDiaIngles] ?? '';

        if (empty($diaSemana)) {
            throw new Exception("No se pudo determinar el día de la semana");
        }

        // Consulta SQL mejorada con debugging
        $sql = "SELECT 
                h.idHorario,
                CONVERT(VARCHAR(5), h.horaInicio, 108) AS horaInicio,
                CONCAT(u.nombre, ' ', u.apellido) AS nombreMedico,
                m.idMedico
            FROM HorariosMedicos h
            INNER JOIN Medicos m ON h.idMedico = m.idMedico
            INNER JOIN Usuarios u ON m.idUsuario = u.idUsuario
            INNER JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
            WHERE h.diaSemana = :diaSemana 
            AND e.nombreEspecialidad = :especialidad
            AND NOT EXISTS (
                SELECT 1 FROM Citas c 
                WHERE c.idHorario = h.idHorario
                AND CAST(c.hora AS DATE) = CAST(:fecha AS DATE)
                AND FORMAT(c.hora, 'HH:mm') = FORMAT(h.horaInicio, 'HH:mm')
                AND c.estado NOT IN ('Cancelada', 'Rechazada')
            )
            ORDER BY h.horaInicio";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':diaSemana', $diaSemana, PDO::PARAM_STR);
        $stmt->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new Exception("Error en consulta: " . implode(" ", $stmt->errorInfo()));
        }

        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($horarios) > 0) {
            foreach ($horarios as $horario) {
                echo "<tr>
                        <td>".htmlspecialchars($horario['horaInicio'], ENT_QUOTES, 'UTF-8')."</td>
                        <td>".htmlspecialchars($horario['nombreMedico'], ENT_QUOTES, 'UTF-8')."</td>
                        <td>60 min</td>
                        <td>
                            <button class='btn-reservar' 
                                    data-horario='".htmlspecialchars($horario['idHorario'], ENT_QUOTES, 'UTF-8')."' 
                                    data-medico='".htmlspecialchars($horario['idMedico'], ENT_QUOTES, 'UTF-8')."'
                                    data-hora-inicio='".htmlspecialchars($horario['horaInicio'], ENT_QUOTES, 'UTF-8')."'>
                                Reservar
                            </button>
                        </td>
                    </tr>";
            }
        } else {
            // Mensaje de depuración
            error_log("No se encontraron horarios para: Dia=$diaSemana, Especialidad=$especialidad, Fecha=$fecha");
            echo "<tr><td colspan='4' class='no-horarios'>No hay horarios disponibles para esta fecha. Intente con otra fecha.</td></tr>";
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