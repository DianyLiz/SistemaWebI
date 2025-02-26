<?php
session_start();
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente = $_POST['paciente'];
    $medico = $_POST['medico'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO Citas (idPaciente, idMedico, fecha, hora, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$paciente, $medico, $fecha, $hora, $estado]);

    header("Location: ../citas.php");
    exit;
}
?>