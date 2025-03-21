<?php
include '../../conexion.php'; 
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idExpediente = $_POST['idExpediente'];

    if (empty($idExpediente)) {
        echo json_encode(["status" => "error", "message" => "ID de expediente no proporcionado."]);
        exit();
    }

    try {

        $consulta = "DELETE FROM ExpedienteMedico WHERE idExpediente = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idExpediente]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Expediente eliminado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el expediente o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>