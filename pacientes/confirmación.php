<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$dni = $_POST['dni'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';
$medico = $_POST['medico'] ?? '';
$motivo = $_POST['motivo'] ?? '';

try {
    // Obtener email del paciente
    $stmt = $conn->prepare("SELECT email FROM Pacientes WHERE dni = ?");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$paciente || empty($paciente['email'])) {
        throw new Exception("No se encontró el email del paciente");
    }
    
    $email_paciente = $paciente['email'];
    
    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    
    // Configuración SMTP (ajustar según tu servidor)
    $mail->isSMTP();
    $mail->Host = 'smtp.tudominio.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tucorreo@tudominio.com';
    $mail->Password = 'tucontraseña';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('no-reply@tudominio.com', 'MediCitas');
    $mail->addAddress($email_paciente);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmación de cita médica';
    
    $mail->Body = "
        <h1>Confirmación de Cita Médica</h1>
        <p>Estimado paciente,</p>
        <p>Su cita ha sido registrada con los siguientes detalles:</p>
        <ul>
            <li><strong>Fecha:</strong> $fecha</li>
            <li><strong>Hora:</strong> $hora</li>
            <li><strong>Médico:</strong> $medico</li>
            <li><strong>Motivo:</strong> $motivo</li>
        </ul>
        <p>Por favor llegue 15 minutos antes de su cita.</p>
        <p>Atentamente,<br>El equipo de MediCitas</p>
    ";
    
    $mail->AltBody = "Confirmación de cita:\nFecha: $fecha\nHora: $hora\nMédico: $medico\nMotivo: $motivo";
    
    if(!$mail->send()) {
        throw new Exception("Error al enviar el correo: " . $mail->ErrorInfo);
    }
    
    echo json_encode(['estado' => 'exito', 'mensaje' => 'Correo enviado correctamente']);
    
} catch(Exception $e) {
    error_log("Error al enviar correo: " . $e->getMessage());
    echo json_encode(['estado' => 'error', 'mensaje' => $e->getMessage()]);
}