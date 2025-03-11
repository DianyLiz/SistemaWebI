<?php
session_start();
include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idpaciente = $_POST['idpaciente']; 
    $paciente = $_POST['paciente'];
    $idmedico = $_POST['idmedico']; 
    $medico = $_POST['medico'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo']; 
    $estado = $_POST['estado'];

    if (empty($idpaciente) || empty($idmedico) || empty($fecha) || empty($hora) || empty($motivo) || empty($estado)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    if($paciente == "No encontrado" || $medico == "No encontrado") {
        $_SESSION['error'] = "Paciente o Médico no encontrado.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    try {
        $consulta = "SELECT * FROM Citas WHERE idPaciente = ? AND idMedico = ? AND fecha = ? AND hora = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente, $idmedico, $fecha, $hora]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe una cita con este paciente y médico en la misma fecha y hora.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $consulta = "INSERT INTO Citas (idPaciente, idMedico, fecha, hora, motivo, estado) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente, $idmedico, $fecha, $hora, $motivo, $estado]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Cita agregada correctamente.";

        } else {

        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }

    header("Location: ../ListadeCitas.php");
    exit;
}
?>
