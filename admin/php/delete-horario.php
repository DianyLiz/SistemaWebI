<?php
include '../../conexion.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idhorario = $_POST['idHorario'];

    if (empty($idhorario)) {
        echo json_encode(["status" => "error", "message" => "ID de horario no proporcionado."]);
        exit();
    }
    
    try {
        $consulta = "DELETE FROM HorariosMedicos WHERE idHorario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idhorario]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Horario eliminado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el horario o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
