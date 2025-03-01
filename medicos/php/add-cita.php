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

    try {
        $sql = "INSERT INTO Citas (idPaciente, idMedico, fecha, hora, motivo, estado) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$paciente, $medico, $fecha, $hora, $motivo, $estado]);

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
