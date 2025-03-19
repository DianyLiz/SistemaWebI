<?php
include '../../conexion.php';

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];

    // Consulta para obtener los horarios de la fecha seleccionada
    $sql = "SELECT T1.idHorario, T1.fecha, T1.diaSemana, T3.dni AS DNIMedico, T2.idMedico, 
                    CONCAT(T3.nombre,' ',T3.apellido) AS Medico, 
                    CONVERT(VARCHAR(5), T1.horaInicio, 108) AS HoraInicio, 
                    CONVERT(VARCHAR(5), T1.horaFin, 108) AS HoraFin, T1.cupos
            FROM HorariosMedicos T1
            INNER JOIN Medicos T2 ON T2.idMedico = T1.idMedico
            INNER JOIN Usuarios T3 ON T3.idUsuario = T2.idUsuario
            WHERE T1.fecha = :fecha";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($horarios) {
        echo json_encode(['success' => true, 'horarios' => $horarios]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>