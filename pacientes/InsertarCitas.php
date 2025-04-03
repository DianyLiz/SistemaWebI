<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Recibir datos POST
$dni = $_POST['dni'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$duracion = $_POST['duracion'] ?? 60;

try {
    // Validar parámetros obligatorios
    if (empty($dni) || empty($motivo) || empty($id_medico) || empty($id_horario) || empty($hora_inicio) || empty($fecha)) {
        throw new Exception('Parámetros requeridos faltantes.');
    }

    // Validar formato de DNI
    if (!preg_match('/^\d{8,13}$/', $dni)) {
        throw new Exception('DNI inválido. Debe contener entre 8 y 13 dígitos.');
    }

    // Validar duración
    $duracion = is_numeric($duracion) ? (int)$duracion : 60;
    if ($duracion <= 0) {
        throw new Exception('Duración inválida.');
    }

    // Validar fecha
    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        throw new Exception('Formato de fecha inválido.');
    }

    // Obtener idPaciente con transacción
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("SELECT idPaciente FROM Pacientes WHERE dni = ?");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        throw new Exception("No se encontró el paciente con DNI proporcionado.");
    }
    
    $id_paciente = $paciente['idPaciente'];
    
    // Verificar disponibilidad nuevamente (evitar condiciones de carrera)
    $stmt = $conn->prepare("
        SELECT 1 FROM HorariosMedicos 
        WHERE idHorario = ? 
        AND (cupos IS NULL OR cupos > 0)
        FOR UPDATE
    ");
    $stmt->execute([$id_horario]);
    if (!$stmt->fetch()) {
        throw new Exception("El horario seleccionado ya no está disponible.");
    }

    // Insertar cita
    $stmt = $conn->prepare("
        INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario, duracion, fecha)
        VALUES (?, ?, ?, ?, 'pendiente', ?, ?, ?)
    ");
    $stmt->execute([
        $id_paciente,
        $id_medico,
        $hora_inicio,
        $motivo,
        $id_horario,
        $duracion,
        $fecha
    ]);
    
    // Actualizar cupos en horario si es necesario
    $stmt = $conn->prepare("
        UPDATE HorariosMedicos 
        SET cupos = GREATEST(cupos - 1, 0) 
        WHERE idHorario = ? AND (cupos IS NOT NULL)
    ");
    $stmt->execute([$id_horario]);
    
    $conn->commit();
    
    echo json_encode([
        'estado' => 'exito',
        'mensaje' => 'Cita registrada correctamente.'
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    error_log('Error al registrar cita: ' . $e->getMessage());
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al registrar cita: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}
?>