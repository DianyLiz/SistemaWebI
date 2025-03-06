<?php 
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idHorario = filter_input(INPUT_POST, 'idHorario', FILTER_SANITIZE_NUMBER_INT);
    $idMedico = filter_input(INPUT_POST, 'idMedico', FILTER_SANITIZE_NUMBER_INT);
    $diaSemana = filter_input(INPUT_POST, 'diaSemana', FILTER_SANITIZE_STRING);
    $horaInicio = filter_input(INPUT_POST, 'horaInicio', FILTER_SANITIZE_STRING);
    $horaFin = filter_input(INPUT_POST, 'horaFin', FILTER_SANITIZE_STRING);
    $cupos = filter_input(INPUT_POST, 'cupos', FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);

    if (empty($idHorario) || empty($idMedico) || empty($diaSemana) || empty($horaInicio) || empty($horaFin) || empty($cupos) || empty($fecha)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../horarios.php');
        exit();
    }

    try {
        $consulta = "UPDATE HorariosMedicos SET idMedico = :idMedico, diaSemana = :diaSemana, horaInicio = :horaInicio, horaFin = :horaFin, cupos = :cupos, fecha = :fecha WHERE idHorario = :idHorario";
        $statement = $conn->prepare($consulta);
        $statement->execute([
            'idMedico' => $idMedico,
            'diaSemana' => $diaSemana,
            'horaInicio' => $horaInicio,
            'horaFin' => $horaFin,
            'cupos' => $cupos,
            'fecha' => $fecha,
            'idHorario' => $idHorario
        ]);

        $_SESSION['success'] = "Horario actualizado correctamente.";
        header('Location: ../horarios.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el horario: " . $e->getMessage();
        header('Location: ../horarios.php');
        exit();
    }

    $conn = null;
}
?>
