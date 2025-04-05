<?php
include 'email.php';

$destinatario = 'medicitas25@gmail.com'; 
$asunto = 'Prueba de correo Medicitas';
$cuerpoHTML = '<h1>Correo de prueba</h1><p>Si recibes esto, el envío de correos funciona correctamente.</p>';

if(enviarEmail($destinatario, $asunto, $cuerpoHTML)){
    echo 'Correo enviado correctamente.';
} else {
    echo 'Hubo un error al enviar el correo.';
}
?>