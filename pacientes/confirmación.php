<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    // Recibir datos
    $dni = trim($_POST['dni'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $medico = trim($_POST['medico'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');

    // Validar datos
    if (empty($dni) || empty($fecha) || empty($hora) || empty($medico)) {
        throw new Exception("Datos incompletos para enviar el correo");
    }

    // Obtener información del paciente
    $stmt = $conn->prepare("
        SELECT p.idPaciente, u.nombre, u.apellido, u.correo 
        FROM Pacientes p
        JOIN Usuarios u ON p.idUsuario = u.idUsuario
        WHERE p.dni = ?
    ");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        throw new Exception("No se encontró información del paciente");
    }

    // Configurar y enviar el correo (ejemplo con PHPMailer)
    require_once '../vendor/autoload.php'; // Si usas Composer
    
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.tudominio.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tucorreo@tudominio.com';
    $mail->Password = 'tucontraseña';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    // Configuración del correo
    $mail->setFrom('no-reply@medicitas.com', 'Sistema MediCitas');
    $mail->addAddress($paciente['correo'], $paciente['nombre'] . ' ' . $paciente['apellido']);
    $mail->isHTML(true);
    
    $mail->Subject = 'Confirmación de Cita Médica';
    
    $mail->Body = "
        <h1>Confirmación de Cita Médica</h1>
        <p>Estimado/a {$paciente['nombre']}:</p>
        <p>Su cita ha sido registrada exitosamente con los siguientes detalles:</p>
        
        <table border='1' cellpadding='5' cellspacing='0'>
            <tr><th>Fecha:</th><td>$fecha</td></tr>
            <tr><th>Hora:</th><td>$hora</td></tr>
            <tr><th>Médico:</th><td>$medico</td></tr>
            <tr><th>Motivo:</th><td>$motivo</td></tr>
        </table>
        
        <p>Por favor llegue 15 minutos antes de su cita.</p>
        <p>Si necesita cancelar o reprogramar, contáctenos con anticipación.</p>
        <p>Atentamente,<br>El equipo de MediCitas</p>
    ";
    
    $mail->AltBody = "Confirmación de Cita:\nFecha: $fecha\nHora: $hora\nMédico: $medico\nMotivo: $motivo";
    
    if (!$mail->send()) {
        throw new Exception("Error al enviar el correo: " . $mail->ErrorInfo);
    }
    
    echo json_encode([
        'estado' => 'exito',
        'mensaje' => 'Correo de confirmación enviado'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}
?>