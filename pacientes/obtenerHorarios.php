<?php
include '../conexion.php';

if (isset($_POST['fecha']) && isset($_POST['especialidad'])) {
    $fecha = $_POST['fecha'];
    $diaSemana = date('l', strtotime($fecha));

    $dias_traduccion = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];

    $diaSemana = $dias_traduccion[$diaSemana] ?? '';
    $especialidad = $_POST['especialidad'];

    $sql = "
    SELECT 
        h.idHorario, 
        CONVERT(VARCHAR(5), h.horaInicio, 108) AS horaInicio, 
        CONVERT(VARCHAR(5), h.horaFin, 108) AS horaFin, 
        CONCAT(b.nombre, ' ', b.apellido) AS nombreMedico,
        u.idMedico
    FROM HorariosMedicos h
    JOIN Medicos u ON h.idMedico = u.idMedico
    JOIN Usuarios b ON b.idUsuario = u.idUsuario
    JOIN Especialidades e ON e.idEspecialidad = u.idEspecialidad
    WHERE h.fecha = :fecha AND e.nombreEspecialidad = :especialidad
    ";

    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':fecha', $fecha);
    $consulta->bindParam(':especialidad', $especialidad);
    $consulta->execute();

    $horarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($horarios)) {
        foreach ($horarios as $horario) {
            $horaInicio = date("H:i", strtotime($horario['horaInicio']));
            $horaFin = date("H:i", strtotime($horario['horaFin']));

            echo "<tr>
                    <td>" . $horario['horaInicio'] . " - " . $horario['horaFin'] . "</td>
                    <td>" . 60 . " minutos</td>
                    <td>" . $horario['nombreMedico'] . "</td>
                    <td>
                        <button class='btn-reservar' 
                                data-idhorario='" . $horario['idHorario'] . "' 
                                data-idmedico='" . $horario['idMedico'] . "'
                                data-nombremedico='" . $horario['nombreMedico'] . "' 
                                data-hora-inicio='" . $horaInicio . "'>
                            Seleccionar
                        </button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay horarios disponibles para esta fecha.</td></tr>";
    }
}
