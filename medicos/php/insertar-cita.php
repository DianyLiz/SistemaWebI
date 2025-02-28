<?php
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $idMedico = $_POST['idMedico'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $estado = $_POST['estado'];

    try {
        $sql = "INSERT INTO Citas (idPaciente, idMedico, fecha, hora, estado) 
                VALUES (:idPaciente, :idMedico, :fecha, :hora, :estado)";
        $query = $conn->prepare($sql);
        $query->bindParam(':idPaciente', $idPaciente);
        $query->bindParam(':idMedico', $idMedico);
        $query->bindParam(':fecha', $fecha);
        $query->bindParam(':hora', $hora);
        $query->bindParam(':estado', $estado);
        $query->execute();

        echo json_encode(["status" => "success", "message" => "Cita agregada correctamente"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error al agregar la cita: " . $e->getMessage()]);
    }
}
?>
