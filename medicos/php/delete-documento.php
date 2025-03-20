<?php
include '../../conexion.php'; // Asegúrate de que la ruta sea correcta
header('Content-Type: application/json'); // Para devolver una respuesta JSON

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idDocumento = $_POST['idDocumento'];

    if (empty($idDocumento)) {
        echo json_encode(["status" => "error", "message" => "ID de documento no proporcionado."]);
        exit();
    }

    try {
        // Preparar la consulta SQL para eliminar el documento
        $consulta = "DELETE FROM DocumentosMedicos WHERE idDocumento = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idDocumento]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Documento eliminado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el documento o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>