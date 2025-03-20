<?php
include '../conexion.php';

$idPaciente = $_GET['idPaciente'] ?? '';

if ($idPaciente) {
    try {
        $sql = "SELECT C.idCita, h.fecha 
                FROM Citas C
                INNER JOIN HorariosMedicos h ON C.idHorario = h.idHorario
                WHERE C.idPaciente = :idPaciente";
        $query = $conn->prepare($sql);
        $query->bindParam(':idPaciente', $idPaciente, PDO::PARAM_INT);
        $query->execute();
        $citas = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($citas);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}

$conn = null;
?>