<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$fecha = $_POST['fecha'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';

try {
    // 1. Verificar que el horario sigue disponible
    $stmt = $conn->prepare("
        SELECT COUNT(*) as disponible 
        FROM HorariosMedicos 
        WHERE idHorario = :id_horario 
        AND idMedico = :id_medico
        AND fecha = :fecha
        AND horaInicio = :hora_inicio
    ");
    $stmt->execute([
        ':id_horario' => $id_horario,
        ':id_medico' => $id_medico,
        ':fecha' => $fecha,
        ':hora_inicio' => $hora_inicio
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result['disponible'] == 0) {
        echo json_encode([
            'disponible' => false, 
            'mensaje' => 'El horario ya no está disponible en la programación del médico'
        ]);
        exit;
    }

    // 2. Verificar que no exista cita en ese horario
    $stmt = $conn->prepare("
        SELECT COUNT(*) as existe 
        FROM Citas 
        WHERE fecha = :fecha 
        AND hora = :hora_inicio 
        AND idMedico = :id_medico 
        AND estado != 'cancelada'
    ");
    $stmt->execute([
        ':fecha' => $fecha,
        ':hora_inicio' => $hora_inicio,
        ':id_medico' => $id_medico
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result['existe'] > 0) {
        echo json_encode([
            'disponible' => false, 
            'mensaje' => 'Ya existe una cita registrada para este horario'
        ]);
        exit;
    }

    // 3. Verificar solapamiento (considerando duración de 60 minutos)
    $hora_fin = date('H:i:s', strtotime("$hora_inicio + 60 minutes"));
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as solapadas 
        FROM Citas 
        WHERE fecha = :fecha 
        AND idMedico = :id_medico 
        AND estado != 'cancelada'
        AND (
            (hora <= :hora_inicio AND DATEADD(MINUTE, duracion, hora) > :hora_inicio)
            OR (hora < :hora_fin AND DATEADD(MINUTE, duracion, hora) >= :hora_fin)
            OR (hora >= :hora_inicio AND DATEADD(MINUTE, duracion, hora) <= :hora_fin)
        )
    ");
    $stmt->execute([
        ':fecha' => $fecha,
        ':id_medico' => $id_medico,
        ':hora_inicio' => $hora_inicio,
        ':hora_fin' => $hora_fin
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($result['solapadas'] > 0) {
        echo json_encode([
            'disponible' => false, 
            'mensaje' => 'El horario se solapa con otra cita existente'
        ]);
        exit;
    }

    echo json_encode(['disponible' => true]);
    
} catch(PDOException $e) {
    echo json_encode([
        'disponible' => false, 
        'mensaje' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
}