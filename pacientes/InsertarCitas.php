<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$dni = $_POST['dni'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$id_medico = $_POST['id_medico'] ?? '';
$id_horario = $_POST['id_horario'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$duracion = $_POST['duracion'] ?? 60;

try {
    // Obtener idPaciente
    $stmt = $conn->prepare("SELECT idPaciente FROM Pacientes WHERE dni = ?");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$paciente) {
        throw new Exception("No se encontró el paciente con DNI proporcionado");
    }
    
    $id_paciente = $paciente['idPaciente'];
    
    // Insertar cita
    $conn->beginTransaction();
    
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
        SET cupos = cupos - 1 
        WHERE idHorario = ? AND cupos > 0
    ");
    $stmt->execute([$id_horario]);
    
    $conn->commit();
    
    echo json_encode([
        'estado' => 'exito',
        'mensaje' => 'Cita registrada correctamente'
    ]);
    
} catch(PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al registrar cita: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}