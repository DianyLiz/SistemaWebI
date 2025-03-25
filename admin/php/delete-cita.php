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
        $consulta = "SELECT idHorario FROM Citas WHERE idCita = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idcita]);
        $resultado = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$resultado) {
            echo json_encode(["status" => "error", "message" => "No se encontró la cita."]);
            exit();
        }

        $idHorario = $resultado['idHorario']; // Guardar el idHorario antes de eliminar

        // Eliminar la cita
        $consulta = "DELETE FROM Citas WHERE idCita = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idcita]);

        if ($statement->rowCount() > 0) {
            $consulta = "UPDATE HorariosMedicos SET cupos = cupos + 1 WHERE idHorario = ?";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idHorario]);

            echo json_encode(["status" => "success", "message" => "Cita eliminada correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró la cita o ya fue eliminada."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}

?>
