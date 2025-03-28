<?php
include '../../conexion.php';

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];

    // Consulta para obtener los horarios de la fecha seleccionada
    $sql = "SELECT Citas.idCita,
            U1.dni AS dnipaciente, 
            Citas.idPaciente, U1.nombre + ' ' + U1.apellido AS paciente, 
            U2.dni AS dnimedico,
            Citas.idMedico, U2.nombre + ' ' + U2.apellido AS medico, 
            HorariosMedicos.fecha AS FechaAtencion, 
            CONVERT(VARCHAR(5), Citas.hora, 108) AS hora,
            Citas.idHorario
                FROM Citas 
                INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
                INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
                INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
                INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario
                INNER JOIN HorariosMedicos ON Citas.idHorario = HorariosMedicos.idHorario
                WHERE HorariosMedicos.fecha = :fecha";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->execute();
    
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($citas) {
        echo json_encode(['success' => true, 'citas' => $citas]);
    } else {
        echo json_encode(['success' => false, 'citas' => []]);
    }
}
?>
