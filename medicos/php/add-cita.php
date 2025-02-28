<?php
session_start();
include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente = $_POST['paciente']; 
    $medico = $_POST['medico']; 
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo']; 
    $estado = $_POST['estado'];

    $sql = "INSERT INTO Citas (idPaciente, idMedico, fecha, hora, motivo, estado) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$paciente, $medico, $fecha, $hora, $motivo, $estado]);

    header("Location: ../ListadeCitas.php");
    exit;
}
?>

