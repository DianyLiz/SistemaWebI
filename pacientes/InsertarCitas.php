<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar solicitud OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Leer datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Formato de datos inválido", 400);
    }

    // Validar datos
    $dni = trim($data['dni'] ?? '');
    $motivo = trim($data['motivo'] ?? '');
    $id_medico = (int)($data['id_medico'] ?? 0);
    $id_horario = (int)($data['id_horario'] ?? 0);
    $hora_inicio = trim($data['hora_inicio'] ?? '');
    $fecha = trim($data['fecha'] ?? '');
    $duracion = (int)($data['duracion'] ?? 60);

    // Validaciones
    if (empty($dni) || !preg_match('/^\d{8,13}$/', $dni)) {
        throw new Exception("DNI inválido (8-13 dígitos)", 400);
    }
    if (empty($motivo) || strlen($motivo) < 10) {
        throw new Exception("El motivo debe tener al menos 10 caracteres", 400);
    }
    if ($id_medico <= 0 || $id_horario <= 0) {
        throw new Exception("ID de médico u horario inválido", 400);
    }
    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        throw new Exception("Formato de fecha inválido (YYYY-MM-DD)", 400);
    }
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_inicio)) {
        throw new Exception("Formato de hora inválido (HH:MM o HH:MM:SS)", 400);
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // 1. Verificar paciente
    $stmt = $conn->prepare("SELECT idPaciente FROM Pacientes WHERE dni = ?");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        throw new Exception("No existe paciente con DNI: $dni", 404);
    }
    $id_paciente = $paciente['idPaciente'];

    // 2. Verificar horario
    $stmt = $conn->prepare("
        SELECT idHorario, cupos 
        FROM HorariosMedicos 
        WHERE idHorario = ? 
        AND idMedico = ?
        FOR UPDATE
    ");
    $stmt->execute([$id_horario, $id_medico]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        throw new Exception("Horario no disponible", 404);
    }

    // 3. Verificar disponibilidad
    $hora_completa = $fecha . ' ' . substr($hora_inicio . ':00', 0, 8);
    $stmt = $conn->prepare("
        SELECT 1 
        FROM Citas 
        WHERE idHorario = ? 
        AND CONVERT(DATE, hora) = ?
        AND CONVERT(VARCHAR(8), hora, 108) LIKE ?
        AND estado NOT IN ('Cancelada', 'Rechazada')
    ");
    $stmt->execute([$id_horario, $fecha, substr($hora_inicio, 0, 5) . '%']);

    if ($stmt->fetch()) {
        throw new Exception("Ya existe una cita en este horario", 409);
    }

    // 4. Insertar cita
    $stmt = $conn->prepare("
        INSERT INTO Citas 
        (idPaciente, idMedico, hora, motivo, estado, idHorario, duracion, fecha_registro) 
        VALUES (?, ?, ?, ?, 'pendiente', ?, ?, GETDATE())
    ");
    $stmt->execute([
        $id_paciente,
        $id_medico,
        $hora_completa,
        $motivo,
        $id_horario,
        $duracion
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Error al registrar la cita", 500);
    }

    // 5. Actualizar cupos si aplica
    if ($horario['cupos'] !== null) {
        $stmt = $conn->prepare("
            UPDATE HorariosMedicos 
            SET cupos = GREATEST(cupos - 1, 0) 
            WHERE idHorario = ?
        ");
        $stmt->execute([$id_horario]);
    }

    $conn->commit();

    echo json_encode([
        'estado' => 'exito',
        'mensaje' => 'Cita registrada correctamente',
        'id_cita' => $conn->lastInsertId()
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}
?>