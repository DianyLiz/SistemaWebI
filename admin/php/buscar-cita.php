<?php
include '../../conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $dnipaciente = $_GET['dnipaciente'];
    $fecha = $_GET['fecha']; // Fecha seleccionada
    $consulta = "SELECT T1.idCita, T6.fecha, 
                    CONCAT(T4.nombre, ' ', T4.apellido) AS Paciente,
                    CONCAT(T5.nombre, ' ', T5.apellido) AS Medico,
                    T1.hora AS HoraAtencion
                    FROM Citas T1
                    INNER JOIN Pacientes T2 ON T2.idPaciente = T1.idPaciente
                    INNER JOIN Medicos T3 ON T3.idMedico = T1.idMedico
                    INNER JOIN Usuarios T4 ON T4.idUsuario = T2.idUsuario
                    INNER JOIN Usuarios T5 ON T5.idUsuario = T3.idUsuario
                    INNER JOIN HorariosMedicos T6 ON T6.idHorario = T1.idHorario
                    WHERE T4.dni = ? AND T6.fecha = ?";

    $statement = $conn->prepare($consulta);
    $statement->execute([$dnipaciente, $fecha]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []); // Si no hay resultados, devolver un array vacío
}
