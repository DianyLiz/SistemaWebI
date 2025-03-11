<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idpaciente = $_POST['idpaciente'];
    $idusuario = $_POST['idusuario'];

    if (empty($idpaciente)) {
        echo json_encode(["status" => "error", "message" => "ID del paciente no proporcionado."]);
        exit();
    }
    
    try {
        $consulta = "DELETE FROM Pacientes WHERE idPaciente = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente]);

        if ($statement->rowCount() > 0) {
            $consulta = "DELETE FROM Usuarios WHERE idUsuario = ?";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idusuario]);

            if($statement->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "Paciente eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se encontró el paciente o ya fue eliminado."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el paciente o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
