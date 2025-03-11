<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idmedico = $_POST['idmedico'];
    $idusuario = $_POST['idusuario'];

    if (empty($idmedico)) {
        echo json_encode(["status" => "error", "message" => "ID del médico no proporcionado."]);
        exit();
    }
    
    try {
        $consulta = "DELETE FROM medicos WHERE idmedico = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idmedico]);

        if ($statement->rowCount() > 0) {
            $consulta = "DELETE FROM usuarios WHERE idusuario = ?";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idusuario]);

            if($statement->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "Médico eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se encontró el médico o ya fue eliminado."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el médico o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
