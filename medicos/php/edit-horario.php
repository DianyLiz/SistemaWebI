<?php 
include '../../conexion.php';
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idhorario = $_POST['idHorario'];
    $idmedico = $_POST['idMedico'];
    $diasemana = $_POST['diaSemana'];
    $horainicio = $_POST['horaInicio'];
    $horafin = $_POST['horaFin'];
    $cupos = $_POST['cupos'];
    $fecha = $_POST['fecha'];

    if(empty($idmedico) || empty($diasemana) || empty($horainicio) || empty($horafin) || empty($cupos)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../horarios.php");
        exit();
    }

    try {
        $consulta = "SELECT * FROM HorariosMedicos WHERE idMedico = ? AND fecha = ? AND horaInicio = ? AND horaFin = ? AND idHorario != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idmedico, $fecha, $horainicio, $horafin, $idhorario]);

        if($statement->fetch()) {
            $_SESSION['error'] = "Ya existe un horario con este médico en la misma fecha y hora.";
            header("Location: ../horarios.php");
            exit();
        }

        $consulta = "UPDATE HorariosMedicos SET idMedico = ?, diaSemana = ?, horaInicio = ?, horaFin = ?, cupos = ?, fecha = ? WHERE idHorario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idmedico, $diasemana, $horainicio, $horafin, $cupos, $fecha, $idhorario]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Horario actualizado correctamente.";
            unset($_SESSION['form_data']);
            header("Location: ../horarios.php");
        } else {
            $_SESSION['error'] = "Hubo un problema al actualizar el horario.";
            header("Location: ../horarios.php");
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }
}
?>
