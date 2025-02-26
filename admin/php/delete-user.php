<?php
include '../../conexion.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_POST['idusuario'];
    $idusuario_actual = $_SESSION['usuario']['idusuario'];

    if (empty($idusuario)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario no proporcionado."]);
        exit();
    }

    if ($idusuario == $idusuario_actual) {
        echo json_encode(["status" => "error", "message" => "No puedes eliminar tu propio usuario."]);
        exit();
    }
    
    try {
        $consulta = "DELETE FROM usuarios WHERE idusuario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idusuario]);

        if ($statement->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Usuario eliminado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se encontró el usuario o ya fue eliminado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
}
?>
