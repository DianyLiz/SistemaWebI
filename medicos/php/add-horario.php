<?php 
include '../../conexion.php';
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;
    $idmedico = $_POST['idmedico'];
    $diasemana = $_POST['diaSemana'];
    $horainicio = $_POST['horainicio'];
    $horafin = $_POST['horafin'];
    $cupos = $_POST['cupos'];
    $fecha = $_POST['fecha'];

    if(empty($idmedico) || empty($diasemana) || empty($horainicio) || empty($horafin) || empty($cupos)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../horarios.php");
        exit();
    }

    try {
        $consulta = "SELECT * FROM HorariosMedicos WHERE idMedico = ? AND fecha = ? AND horaInicio = ? AND horaFin = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idmedico, $fecha, $horainicio, $horafin]);

        if($statement->fetch()) {
            $_SESSION['error'] = "Ya existe un horario con este médico en la misma fecha y hora.";
            header("Location: ../horarios.php");
            exit();
        }

        $consulta = "INSERT INTO HorariosMedicos (idMedico, diaSemana, horaInicio, horaFin, cupos, fecha) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idmedico, $diasemana, $horainicio, $horafin, $cupos, $fecha]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Horario agregado correctamente.";
            unset($_SESSION['form_data']);
            header("Location: ../horarios.php");
        } else {
            $_SESSION['error'] = "Hubo un problema al agregar el horario.";
            header("Location: ../horarios.php");
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }
}
?>