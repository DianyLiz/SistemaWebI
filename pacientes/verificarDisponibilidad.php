<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$fecha = $_POST['fecha'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';

try {
    // Validar que los parámetros no estén vacíos
    if (empty($fecha) || empty($hora_inicio) || empty($id_medico) || empty($id_horario)) {
        throw new Exception('Parámetros requeridos faltantes.');
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

    // 1. Verificar que el horario sigue activo para el médico (CORREGIDO)
    $stmt = $conn->prepare("
        SELECT cupos 
        FROM HorariosMedicos 
        WHERE idHorario = ? 
        AND idMedico = ?
        AND (
            fecha = ? 
            OR (fecha IS NULL AND diaSemana = ?)
        )
        FOR UPDATE
    ");
    
    $stmt->execute([$id_horario, $id_medico, $fecha, $diaSemanaNombre]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        $conn->rollBack();
        throw new Exception('El horario ya no está disponible en la programación del médico.');
    }

    // Verificar cupos disponibles (LÓGICA CORREGIDA)
    if ($horario['cupos'] !== null) {
        if ($horario['cupos'] <= 0) {
            $conn->rollBack();
            throw new Exception('No hay cupos disponibles para este horario.');
        }
        
        // 2. Verificar citas existentes solo si hay límite de cupos
        $stmt = $conn->prepare("
            SELECT COUNT(*) as existe 
            FROM Citas 
            WHERE fecha = ? 
            AND hora = ? 
            AND idMedico = ? 
            AND idHorario = ?
            AND estado NOT IN ('Cancelada', 'Rechazada')
        ");
        
        $stmt->execute([$fecha, $hora_inicio, $id_medico, $id_horario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['existe'] >= $horario['cupos']) {
            $conn->rollBack();
            throw new Exception('Ya se alcanzó el límite de cupos para este horario.');
        }
    } else {
        // Para horarios sin límite de cupos, solo verificar que no haya una cita exactamente a la misma hora
        $stmt = $conn->prepare("
            SELECT COUNT(*) as existe 
            FROM Citas 
            WHERE fecha = ? 
            AND hora = ? 
            AND idMedico = ? 
            AND idHorario = ?
            AND estado NOT IN ('Cancelada', 'Rechazada')
        ");
        
        $stmt->execute([$fecha, $hora_inicio, $id_medico, $id_horario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['existe'] > 0) {
            $conn->rollBack();
            throw new Exception('Ya existe una cita registrada para este horario exacto.');
        }
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
?>