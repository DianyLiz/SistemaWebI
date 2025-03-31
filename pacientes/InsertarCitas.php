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
    if (!isset($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Falta el campo requerido: $field"]);
        exit;
    }
}

// Asignar valores
$dni = trim($_POST["dni"]);
$motivo = trim($_POST["motivo"]);
$idMedico = (int)$_POST["medico"];
$idHorario = (int)$_POST["horario"];
$horaInicio = $_POST["hora_inicio"];
$fecha = $_POST["fecha"];
$duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 60;
$estado = "pendiente";

try {
    $conn->beginTransaction();

    // 1. Verificar disponibilidad
    $sqlVerificar = "SELECT COUNT(*) AS ocupado FROM Citas
                    WHERE idHorario = :horario 
                    AND CAST(hora AS DATE) = CAST(:fecha AS DATE)
                    AND CAST(hora AS TIME) = CAST(:hora_inicio AS TIME)
                    AND estado NOT IN ('Cancelada', 'Rechazada')";
    
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':horario', $idHorario, PDO::PARAM_INT);
    $stmtVerificar->bindParam(':fecha', $fecha);
    $stmtVerificar->bindParam(':hora_inicio', $horaInicio);
    $stmtVerificar->execute();
    
    $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['ocupado'] > 0) {
        throw new Exception("El horario seleccionado ya no está disponible");
    }

    // 2. Obtener usuario y paciente
    $sqlUsuario = "SELECT u.idUsuario, u.correo, u.nombre, p.idPaciente 
                  FROM Usuarios u
                  LEFT JOIN Pacientes p ON u.idUsuario = p.idUsuario
                  WHERE u.dni = ?";
    $stmtUsuario = $conn->prepare($sqlUsuario);
    $stmtUsuario->execute([$dni]);
    
    if ($stmtUsuario->rowCount() == 0) {
        throw new Exception("No se encontró un usuario con el DNI proporcionado");
    }
    
    $dataUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
    if (empty($dataUsuario['idPaciente'])) {
        throw new Exception("No se encontró un paciente asociado al usuario");
    }

    // 3. Insertar cita
    $fechaHora = $fecha . ' ' . $horaInicio;
    $sqlInsert = "INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario, duracion) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([
        $dataUsuario['idPaciente'],
        $idMedico,
        $fechaHora,
        $motivo,
        $estado,
        $idHorario,
        $duracion
    ]);

    // 4. Enviar correo de confirmación
    $sqlMedico = "SELECT u.nombre, u.apellido FROM Usuarios u
                 JOIN Medicos m ON u.idUsuario = m.idUsuario
                 WHERE m.idMedico = ?";
    $stmtMedico = $conn->prepare($sqlMedico);
    $stmtMedico->execute([$idMedico]);
    $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC);

    $asunto = "Cita Médica Registrada";
    $mensaje = "<html><body>
        <h2>Cita Registrada</h2>
        <p>Fecha: ".date('d/m/Y', strtotime($fecha))."</p>
        <p>Hora: ".date('H:i', strtotime($horaInicio))."</p>
        <p>Duración: $duracion minutos</p>
        <p>Médico: Dr. {$medico['nombre']} {$medico['apellido']}</p>
        <p>Motivo: $motivo</p>
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