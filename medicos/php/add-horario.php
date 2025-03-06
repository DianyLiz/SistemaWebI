<?php
session_start();
include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medico = filter_input(INPUT_POST, 'medico', FILTER_SANITIZE_NUMBER_INT);
    $diaSemana = filter_input(INPUT_POST, 'diaSemana', FILTER_SANITIZE_STRING);
    $horaInicio = filter_input(INPUT_POST, 'horaInicio', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
    $horaFin = filter_input(INPUT_POST, 'horaFin', FILTER_SANITIZE_STRING);
    $cupos = filter_input(INPUT_POST, 'cupos', FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);

    if (empty($medico) || empty($diaSemana) || empty($horaInicio) || empty($estado) || empty($horaFin) || empty($cupos) || empty($fecha)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../ListadeCitas.php");
        exit;
    }

    try {
        $sql = "INSERT INTO Horarios (idMedico, diaSemana, horaInicio, estado, horaFin, cupos, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$medico, $diaSemana, $horaInicio, $estado, $horaFin, $cupos, $fecha]);

        $_SESSION['mensaje'] = "Horario agregado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al agregar el horario: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    $conn = null;

    header("Location: ../ListadeCitas.php");
    exit;
}
?>
