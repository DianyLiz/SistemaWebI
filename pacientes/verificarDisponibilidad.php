<?php
include '../conexion.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['disponible' => false, 'error' => 'Error de conexión']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['disponible' => false, 'error' => 'Método no permitido']);
    exit;
}

$required = ['fecha', 'horario', 'horaInicio'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['disponible' => false, 'error' => "Falta el campo $field"]);
        exit;
    }
}

try {
    $fecha = $_POST['fecha'];
    $idHorario = (int)$_POST['horario'];
    $horaInicio = $_POST['horaInicio'];

    // Consulta para verificar disponibilidad
    $sql = "SELECT 
            (SELECT COUNT(*) 
             FROM Citas 
             WHERE idHorario = :horario
             AND CAST(hora AS DATE) = CAST(:fecha AS DATE)
             AND FORMAT(hora, 'HH:mm') = :horaInicio
             AND estado NOT IN ('Cancelada', 'Rechazada')
            ) AS citas_existentes,
            
            (SELECT cupos 
             FROM HorariosMedicos 
             WHERE idHorario = :horario2
            ) AS cupos_totales";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':horario', $idHorario, PDO::PARAM_INT);
    $stmt->bindParam(':horario2', $idHorario, PDO::PARAM_INT);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':horaInicio', $horaInicio, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception("Error en verificación: " . implode(" ", $stmt->errorInfo()));
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $disponible = true;
    if ($result['cupos_totales'] > 0 && $result['citas_existentes'] >= $result['cupos_totales']) {
        $disponible = false;
    }

    echo json_encode([
        'disponible' => $disponible,
        'message' => $disponible ? 'Horario disponible' : 'Horario no disponible',
        'cupos' => $result['cupos_totales'] - $result['citas_existentes']
    ]);
    
} catch (Exception $e) {
    error_log("Error verificarDisponibilidad: ".$e->getMessage());
    echo json_encode([
        'disponible' => false,
        'error' => 'Error al verificar disponibilidad: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>