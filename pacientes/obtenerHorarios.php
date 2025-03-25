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
        b.nombre AS nombreMedico,
        b.apellido AS apellidoMedico
        FROM HorariosMedicos h
        JOIN Medicos u ON h.idMedico = u.idMedico
        JOIN Usuarios b ON b.idUsuario = u.idUsuario
        JOIN Especialidades e ON e.idEspecialidad = u.idEspecialidad
        WHERE h.diaSemana = :diaSemana AND e.nombreEspecialidad = :especialidad
";

    $consulta = $conn->prepare($sql);
    $consulta->bindParam(':diaSemana', $diaSemana);
    $consulta->bindParam(':especialidad', $especialidad);
    $consulta->execute();

    $horarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($horarios)) {
        foreach ($horarios as $horario) {
            
            $horaInicio = date("H:i", strtotime($horario['horaInicio']));
            $horaFin = date("H:i", strtotime($horario['horaFin']));

            echo "<tr>
                    <td>" . $horario['horaInicio'] . "</td>
                    <td>" . $horario['horaFin'] . "</td>
                    <td>" . $horario['nombreMedico'] ." ". $horario['apellidoMedico'] . "</td>
                    <td>
                        <button class='btn-horario' data-horario='" . $horario['idHorario'] ."' >
                            Seleccionar
                        </button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay horarios disponibles para esta fecha.</td></tr>";
    }
}
?>
