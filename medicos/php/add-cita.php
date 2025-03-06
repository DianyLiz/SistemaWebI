<?php
session_start();
include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente = filter_input(INPUT_POST, 'paciente', FILTER_SANITIZE_NUMBER_INT);
    $medico = filter_input(INPUT_POST, 'medico', FILTER_SANITIZE_NUMBER_INT);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
    $idHorario = filter_input(INPUT_POST, 'idHorario', FILTER_SANITIZE_NUMBER_INT);

    if (empty($paciente) || empty($medico) || empty($hora) || empty($motivo) || empty($estado) || empty($idHorario)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../ListadeCitas.php");
        exit;
    }

    try {
        $sql_verificar = "SELECT * FROM Citas 
                          WHERE idPaciente = ? AND idMedico = ? AND idHorario = ? AND hora = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->execute([$paciente, $medico, $idHorario, $hora]);

        if ($stmt_verificar->fetch()) {
            $_SESSION['mensaje'] = "Ya existe una cita con este paciente y médico en el mismo horario y hora.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../ListadeCitas.php");
            exit;
        }

        $sql = "INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$paciente, $medico, $hora, $motivo, $estado, $idHorario]);

        $_SESSION['mensaje'] = "Cita agregada correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al agregar la cita: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: ../ListadeCitas.php");
    exit;
}
?>
