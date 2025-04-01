<?php
session_start();
include '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Validar campos requeridos
$requiredFields = ['dni', 'motivo', 'medico', 'horario', 'hora_inicio', 'fecha'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Falta el campo requerido: $field"]);
        exit;
    }
}

try {
    $conn->beginTransaction();

    $dni = $_POST["dni"];
    $motivo = $_POST["motivo"];
    $idMedico = (int)$_POST["medico"];
    $idHorario = (int)$_POST["horario"];
    $horaInicio = $_POST["hora_inicio"];
    $fecha = $_POST["fecha"];

    // Verificar disponibilidad antes de insertar
    $sqlVerificar = "SELECT COUNT(*) AS ocupado FROM Citas
                    WHERE idHorario = :horario 
                    AND CAST(hora AS DATE) = CAST(:fecha AS DATE)
                    AND FORMAT(hora, 'HH:mm') = :horaInicio
                    AND estado NOT IN ('Cancelada', 'Rechazada')";
    
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':horario', $idHorario, PDO::PARAM_INT);
    $stmtVerificar->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmtVerificar->bindParam(':horaInicio', $horaInicio, PDO::PARAM_STR);
    $stmtVerificar->execute();
    
    $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['ocupado'] > 0) {
        throw new Exception("El horario seleccionado ya no está disponible");
    }

    // Obtener información del paciente
    $sqlPaciente = "SELECT p.idPaciente 
                   FROM Pacientes p
                   JOIN Usuarios u ON p.idUsuario = u.idUsuario
                   WHERE u.dni = :dni";
    $stmtPaciente = $conn->prepare($sqlPaciente);
    $stmtPaciente->bindParam(':dni', $dni, PDO::PARAM_STR);
    $stmtPaciente->execute();
    
    if ($stmtPaciente->rowCount() == 0) {
        throw new Exception("No se encontró un paciente con el DNI proporcionado");
    }
    
    $idPaciente = $stmtPaciente->fetchColumn();

    // Insertar la cita
    $fechaHora = $fecha . ' ' . $horaInicio;
    $sqlInsert = "INSERT INTO Citas (idPaciente, idMedico, idHorario, hora, motivo, estado) 
                 VALUES (:idPaciente, :idMedico, :idHorario, :hora, :motivo, 'pendiente')";
    
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bindParam(':idPaciente', $idPaciente, PDO::PARAM_INT);
    $stmtInsert->bindParam(':idMedico', $idMedico, PDO::PARAM_INT);
    $stmtInsert->bindParam(':idHorario', $idHorario, PDO::PARAM_INT);
    $stmtInsert->bindParam(':hora', $fechaHora, PDO::PARAM_STR);
    $stmtInsert->bindParam(':motivo', $motivo, PDO::PARAM_STR);
    
    if (!$stmtInsert->execute()) {
        throw new Exception("Error al insertar la cita: " . implode(" ", $stmtInsert->errorInfo()));
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Cita registrada correctamente'
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error en InsertarCitas: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>