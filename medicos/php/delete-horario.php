<?php
include '../../conexion.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idcita = $_POST['idCita'];

    if (empty($idcita)) {
        echo json_encode(["status" => "error", "message" => "ID de Horario no proporcionado."]);
        exit();
    }


    
    try {
        $consulta = "DELETE FROM HorariosMedicos WHERE idHorario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idcita]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Horario eliminado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el Horario o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
