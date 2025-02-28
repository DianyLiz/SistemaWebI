<?php
include '../../conexion.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idcita = $_POST['idCita'];

    if (empty($idcita)) {
        echo json_encode(["status" => "error", "message" => "ID de cita no proporcionado."]);
        exit();
    }


    
    try {
        $consulta = "DELETE FROM Citas WHERE idCita = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idcita]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Cita eliminada correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró la cita o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
