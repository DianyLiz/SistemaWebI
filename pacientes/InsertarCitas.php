<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Recibir datos POST
$dni = $_POST['dni'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';
$horallegada = $_POST['hora_inicio'] ?? '';  // <-- Este nombre debe coincidir con JS
$fecha = $_POST['fecha'] ?? '';
$duracion = $_POST['duracion'] ?? 60;

$transaccionIniciada = false;

try {
    // Validar parámetros obligatorios
    if (empty($dni) || empty($motivo) || empty($id_medico) || empty($id_horario) || empty($horallegada) || empty($fecha)) {
        throw new Exception('Parámetros requeridos faltantes. INSERT');
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

    // Iniciar transacción
    $conn->beginTransaction();
    $transaccionIniciada = true;

    // Obtener ID del paciente
    $stmt = $conn->prepare("SELECT T1.idPaciente 
                            FROM Pacientes T1
                            INNER JOIN Usuarios T2 ON T2.idUsuario = T1.idUsuario
                            WHERE T2.dni = ?");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        throw new Exception("No se encontró el paciente con DNI proporcionado.");
    }

    $id_paciente = $paciente['idPaciente'];

    // Verificar disponibilidad nuevamente (evitar condiciones de carrera)
    $stmt = $conn->prepare("
            SELECT 1 FROM HorariosMedicos WITH (UPDLOCK, ROWLOCK)
            WHERE idHorario = ? 
            AND (cupos IS NULL OR cupos > 0)
            ");
    $stmt->execute([$id_horario]);
    if (!$stmt->fetch()) {
        throw new Exception("El horario seleccionado ya no está disponible.");
    }

    $stmt->execute([$id_horario]);
    if (!$stmt->fetch()) {
        throw new Exception("El horario seleccionado ya no está disponible.");
    }

    // Insertar cita
    $stmt = $conn->prepare("
        INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario, duracion)
        VALUES (?, ?, ?, ?, 'Pendiente', ?, ?)
    ");
    $stmt->execute([
        $id_paciente,
        $id_medico,
        $horallegada,
        $motivo,
        $id_horario,
        $duracion
    ]);

    // Actualizar cupos
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
    if ($transaccionIniciada) {
        $conn->rollBack();
    }
    error_log('Error al registrar cita (PDO): ' . $e->getMessage());
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al registrar cita: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($transaccionIniciada) {
        $conn->rollBack();
    }
    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}
