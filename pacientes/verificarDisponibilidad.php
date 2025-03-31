<?php
include '../conexion.php';

header('Content-Type: application/json');

// Verificar conexión
if (!$conn) {
    echo json_encode(['disponible' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['disponible' => false, 'error' => 'Método no permitido']);
    exit;
}

// Validar campos requeridos
$required = ['fecha', 'horario', 'horaInicio'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['disponible' => false, 'error' => "Falta el campo $field"]);
        exit;
    }
}

try {
    $conn->beginTransaction();
    
    $fecha = $_POST['fecha'];
    $idHorario = (int)$_POST['horario'];
    $horaInicio = date("H:i:s", strtotime($_POST['horaInicio'])); // Asegurar formato correcto

    // Consulta corregida
    $sqlCitas = "SELECT COUNT(*) AS total FROM Citas 
                 WHERE idHorario = :horario AND hora = :hora";

    error_log("Consulta SQL: $sqlCitas | Parámetros: idHorario=$idHorario, hora=$horaInicio");

    $stmtCitas = $conn->prepare($sqlCitas);
    $stmtCitas->bindParam(':horario', $idHorario, PDO::PARAM_INT);
    $stmtCitas->bindParam(':hora', $horaInicio, PDO::PARAM_STR);

    if (!$stmtCitas->execute()) {
        throw new Exception("Error al ejecutar la consulta de verificación");
    }

    $resultCitas = $stmtCitas->fetch(PDO::FETCH_ASSOC);
    $disponible = ($resultCitas['total'] == 0);

    $conn->commit();

    echo json_encode([
        'disponible' => $disponible,
        'message' => $disponible ? 'Horario disponible' : 'Horario ocupado'
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error en verificarDisponibilidad: " . $e->getMessage());
    echo json_encode([
        'disponible' => false,
        'error' => 'Error al verificar disponibilidad'
    ]);
} finally {
    $conn = null;
}
?>