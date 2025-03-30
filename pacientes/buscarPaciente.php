<?php
include '../conexion.php';

if (isset($_POST['dni'])) {
    $dni = $_POST['dni'];

    $sql = "SELECT idPaciente, nombre FROM Pacientes WHERE dni = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        echo json_encode(["success" => true, "idPaciente" => $paciente["idPaciente"], "nombre" => $paciente["nombre"]]);
    } else {
        echo json_encode(["success" => false]);
    }
}
