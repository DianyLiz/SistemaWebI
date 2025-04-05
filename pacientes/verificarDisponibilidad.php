<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Leer datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validar datos
    $fecha = trim($data['fecha'] ?? '');
    $hora_inicio = trim($data['hora_inicio'] ?? '');
    $id_medico = (int)($data['id_medico'] ?? 0);
    $id_horario = (int)($data['id_horario'] ?? 0);

    // Registrar datos recibidos
    error_log("Datos recibidos: fecha={$fecha}, hora_inicio={$hora_inicio}, id_medico={$id_medico}, id_horario={$id_horario}");

    if (empty($fecha)) {
        throw new Exception("El campo 'fecha' está vacío", 400);
    }

    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        throw new Exception("Formato de fecha inválido (YYYY-MM-DD)", 400);
    }

    if (empty($hora_inicio)) {
        throw new Exception("El campo 'hora_inicio' está vacío", 400);
    }

    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora_inicio)) {
        throw new Exception("Formato de hora inválido (HH:MM o HH:MM:SS)", 400);
    }

    if ($id_medico <= 0 || $id_horario <= 0) {
        throw new Exception("ID de médico u horario inválido", 400);
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Consultar horario
    $stmt = $conn->prepare("SELECT hm.fecha
        FROM HorariosMedicos hm
        JOIN Medicos m ON hm.idMedico = m.idMedico
        JOIN Usuarios u ON m.idUsuario = u.idUsuario
        WHERE hm.idHorario = ?
        AND hm.idMedico = ?
        AND (hm.fecha = ? OR (hm.fecha IS NULL AND hm.diaSemana = ?))
        FOR UPDATE
    ");
    
    // Obtener el nombre del día de la semana
    $diaSemana = date('N', strtotime($fecha));
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $diaSemanaNombre = $diasSemana[$diaSemana];

    // Registrar parámetros en el log
    error_log("Parámetros SQL: idHorario={$id_horario}, idMedico={$id_medico}, fecha={$fecha}, diaSemana={$diaSemanaNombre}");

    $stmt->execute([$id_horario, $id_medico, $fecha, $diaSemanaNombre]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        throw new Exception("Horario no disponible", 404);
    }

    // Verificar disponibilidad
    $disponible = true;
    $mensaje = 'Horario disponible';

    $horaFormateada = substr($hora_inicio . ':00', 0, 8);
    $stmt = $conn->prepare(" SELECT COUNT(*) AS citas_activas
        FROM Citas
        WHERE idHorario = ?
        AND CAST(hora AS DATE) = ?
        AND CAST(hora AS TIME) = ?
        AND estado NOT IN ('Cancelada', 'Rechazada')
    ");
    $stmt->execute([$id_horario, $fecha, $horaFormateada]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($horario['cupos'] !== null && $resultado['citas_activas'] >= $horario['cupos']) {
        $disponible = false;
        $mensaje = 'No hay cupos disponibles';
    }

    $conn->commit();

    echo json_encode([
        'disponible' => $disponible,
        'mensaje' => $mensaje,
        'horario' => [
            'medico' => $horario['medico_nombre'] ?? '',
            'cupos' => $horario['cupos'] ?? null
        ]
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error en la base de datos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'disponible' => false,
        'mensaje' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Error general: " . $e->getMessage());
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'disponible' => false,
        'mensaje' => $e->getMessage()
    ]);
}
?>