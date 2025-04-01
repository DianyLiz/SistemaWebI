<?php
session_start();
include '../conexion.php';

header('Content-Type: application/json');

require '../php/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function enviarCorreo($destinatario, $asunto, $cuerpoHTML) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'medicitas25@gmail.com';
        $mail->Password = 'thvx dbmb kcvn vhzz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('medicitas25@gmail.com', 'MediCitas');
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: ".$e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Validar campos requeridos
$requiredFields = ['dni', 'motivo', 'medico', 'horario', 'hora_inicio', 'fecha'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Falta el campo requerido: $field"]);
        exit;
    }
}

// Asignar y validar valores
$dni = trim($_POST["dni"]);
$motivo = trim($_POST["motivo"]);
$idMedico = filter_var($_POST["medico"], FILTER_VALIDATE_INT);
$idHorario = filter_var($_POST["horario"], FILTER_VALIDATE_INT);
$horaInicio = trim($_POST["hora_inicio"]);
$fecha = trim($_POST["fecha"]);
$duracion = 60; // Duración fija de 60 minutos
$estado = "pendiente";

// Validar formato de hora
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $horaInicio)) {
    echo json_encode(['status' => 'error', 'message' => "Formato de hora inválido. Use HH:MM"]);
    exit;
}

// Validar formato de fecha
if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
    echo json_encode(['status' => 'error', 'message' => "Formato de fecha inválido. Use YYYY-MM-DD"]);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Verificar disponibilidad
    $sqlVerificar = "SELECT COUNT(*) AS ocupado FROM Citas
                    WHERE idHorario = :horario 
                    AND CONVERT(DATE, hora) = CONVERT(DATE, :fecha)
                    AND CONVERT(TIME, hora) = CONVERT(TIME, :hora_inicio)
                    AND estado NOT IN ('Cancelada', 'Rechazada')";
    
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':horario', $idHorario, PDO::PARAM_INT);
    $stmtVerificar->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmtVerificar->bindParam(':hora_inicio', $horaInicio, PDO::PARAM_STR);
    $stmtVerificar->execute();
    
    $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['ocupado'] > 0) {
        throw new Exception("El horario seleccionado ya no está disponible");
    }

    // 2. Obtener usuario y paciente
    $sqlUsuario = "SELECT u.idUsuario, u.correo, u.nombre, p.idPaciente 
                  FROM Usuarios u
                  LEFT JOIN Pacientes p ON u.idUsuario = p.idUsuario
                  WHERE u.dni = :dni";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->bindParam(':dni', $dni, PDO::PARAM_STR);
    $stmtUsuario->execute();
    
    if ($stmtUsuario->rowCount() == 0) {
        throw new Exception("No se encontró un usuario con el DNI proporcionado");
    }
    
    $dataUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
    if (empty($dataUsuario['idPaciente'])) {
        throw new Exception("No se encontró un paciente asociado al usuario");
    }

    // 3. Insertar cita con duración fija de 60 minutos
    $fechaHora = $fecha . ' ' . $horaInicio;
    $sqlInsert = "INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario, duracion) 
                 VALUES (:idPaciente, :idMedico, :hora, :motivo, :estado, :idHorario, :duracion)";
    
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bindParam(':idPaciente', $dataUsuario['idPaciente'], PDO::PARAM_INT);
    $stmtInsert->bindParam(':idMedico', $idMedico, PDO::PARAM_INT);
    $stmtInsert->bindParam(':hora', $fechaHora, PDO::PARAM_STR);
    $stmtInsert->bindParam(':motivo', $motivo, PDO::PARAM_STR);
    $stmtInsert->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmtInsert->bindParam(':idHorario', $idHorario, PDO::PARAM_INT);
    $stmtInsert->bindParam(':duracion', $duracion, PDO::PARAM_INT);
    $stmtInsert->execute();

    // 4. Obtener información del médico para el correo
    $sqlMedico = "SELECT u.nombre, u.apellido FROM Usuarios u
                 JOIN Medicos m ON u.idUsuario = m.idUsuario
                 WHERE m.idMedico = :idMedico";
    $stmtMedico = $conn->prepare($sqlMedico);
    $stmtMedico->bindParam(':idMedico', $idMedico, PDO::PARAM_INT);
    $stmtMedico->execute();
    $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC);

    // 5. Preparar y enviar correo
    $asunto = "Cita Médica Registrada";
    $mensaje = "<html><body>
        <h2>Cita Registrada</h2>
        <p>Fecha: ".date('d/m/Y', strtotime($fecha))."</p>
        <p>Hora: ".htmlspecialchars($horaInicio)."</p>
        <p>Duración: 60 minutos</p>
        <p>Médico: Dr. ".htmlspecialchars($medico['nombre'])." ".htmlspecialchars($medico['apellido'])."</p>
        <p>Motivo: ".htmlspecialchars($motivo)."</p>
    </body></html>";

    $correoEnviado = enviarCorreo($dataUsuario['correo'], $asunto, $mensaje);
    
    $conn->commit();

    $_SESSION['alert_type'] = 'success';
    $_SESSION['alert_message'] = 'Cita registrada correctamente. ' . ($correoEnviado ? 'Se ha enviado un correo de confirmación.' : '');
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cita registrada correctamente',
        'redirect' => 'confirmacion.php'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error en InsertarCitas: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>