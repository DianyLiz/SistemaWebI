<?php
use PHPMailer\PHPMailer\PHPMailer;
require __DIR__ . '/../vendor/autoload.php';

function enviarEmail($destinatario, $asunto, $cuerpoHTML) {
    $mail = new PHPMailer(true);
    
    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'medicitas25@gmail.com'; 
        $mail->Password = 'thvx dbmb kcvn vhzz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        

        $mail->setFrom('medicitas25@gmail.com', 'MediCitas');
        $mail->addAddress($destinatario);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML); 
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}
?>
