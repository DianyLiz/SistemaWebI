<?php
include '../../conexion.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idespecialidad = $_POST['idespecialidad'];

    if (empty($idespecialidad)) {
        echo json_encode(["status" => "error", "message" => "ID de especialidad no proporcionado."]);
        exit();
    }
    try {
        $consulta = "DELETE FROM Especialidades WHERE idEspecialidad = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idespecialidad]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Especialidad eliminada correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró la especialidad o ya fue eliminada."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
