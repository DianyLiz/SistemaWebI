<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$fecha = $_POST['fecha'] ?? '';
$hora_llegada = $_POST['hora_llegada'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';

try {
    // Validar que los parámetros no estén vacíos
    if (empty($fecha) || empty($hora_llegada) || empty($id_medico) || empty($id_horario)) {
        throw new Exception('Parámetros requeridos faltantes. VERIFICAR');
    }

    // Validar formato de fecha
    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        throw new Exception('Formato de fecha inválido.');
    }

    // Obtener día de la semana
    $diaSemanaNumero = date('N', strtotime($fecha));
    $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    $diaSemanaNombre = $diasSemana[$diaSemanaNumero];

    // Iniciar transacción para evitar condiciones de carrera
    $conn->beginTransaction();

    /// 1. Verificar que el horario sigue activo para el médico (CORREGIDO)
    $stmt = $conn->prepare("
        SELECT cupos 
        FROM HorariosMedicos WITH (UPDLOCK, ROWLOCK)
        WHERE idHorario = ? 
        AND idMedico = ?
        AND (
            fecha = ? 
            OR (fecha IS NULL AND diaSemana = ?)
        )
        ");


    $stmt->execute([$id_horario, $id_medico, $fecha, $diaSemanaNombre]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        $conn->rollBack();
        throw new Exception('El horario ya no está disponible en la programación del médico.');
    }

    // Para horarios sin límite de cupos, verificar que no haya cita a la misma hora
    $stmt = $conn->prepare("
            SELECT COUNT(*) as existe 
            FROM Citas T1
            INNER JOIN HorariosMedicos T2 ON T2.idHorario = T1.idHorario
            WHERE 
                T2.idHorario = ? 
                AND T2.idMedico = ? 
                AND (
                    T2.fecha = ? OR (T2.fecha IS NULL AND T2.diaSemana = ?)
                )
                AND T1.hora = ? 
                AND T1.estado NOT IN ('Cancelada', 'Rechazada')");

    $stmt->execute([
        $id_horario,
        $id_medico,
        $fecha,
        $diaSemanaNombre,
        $hora_llegada
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['existe'] > 0) {
        $conn->rollBack();
        throw new Exception('Ya existe una cita registrada para este horario exacto.');
    }

    $conn->commit();

    echo json_encode([
        'disponible' => true,
        'mensaje' => 'Horario disponible'
    ]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'disponible' => false,
        'mensaje' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'disponible' => false,
        'mensaje' => $e->getMessage()
    ]);
}
