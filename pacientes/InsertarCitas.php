<?php
session_start();
include '../conexion.php';

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

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom('medicitas25@gmail.com', 'MediCitas');
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $e->getMessage());
        return false;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"];
    $motivo = trim($_POST["motivo"]);
    $idMedico = $_POST["medico"];
    $idHorario = $_POST["horario"];
    $hora = $_POST["hora"];
    $estado = "pendiente";

    if (empty($dni)) {
        echo "error: DNI no proporcionado.";
        exit;
    }

    try {
        $sqlUsuario = "SELECT idUsuario FROM Usuarios WHERE dni = :dni";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bindParam(":dni", $dni);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $idUsuario = $usuario['idUsuario'];
            $sqlPaciente = "SELECT idPaciente FROM Pacientes WHERE idUsuario = :idUsuario";
            $stmtPaciente = $conn->prepare($sqlPaciente);
            $stmtPaciente->bindParam(":idUsuario", $idUsuario);
            $stmtPaciente->execute();
            $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

            if ($paciente) {
                $idPaciente = $paciente['idPaciente'];

                $sql = "INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario) 
                        VALUES (:idPaciente, :idMedico, :hora, :motivo, :estado, :idHorario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":idPaciente", $idPaciente);
                $stmt->bindParam(":idMedico", $idMedico);
                $stmt->bindParam(":hora", $hora);
                $stmt->bindParam(":motivo", $motivo);
                $stmt->bindParam(":estado", $estado);
                $stmt->bindParam(":idHorario", $idHorario);

                if ($stmt->execute()) {
                    $stmtPacienteDatos = $conn->prepare("
                        SELECT u.nombre, u.correo 
                        FROM Usuarios u
                        JOIN Pacientes p ON u.idUsuario = p.idUsuario
                        WHERE p.idPaciente = :idPaciente
                    ");
                    $stmtPacienteDatos->bindParam(":idPaciente", $idPaciente);
                    $stmtPacienteDatos->execute();
                    $paciente = $stmtPacienteDatos->fetch(PDO::FETCH_ASSOC);
                
                    $stmtCitaDatos = $conn->prepare("
                        SELECT hm.fecha, u.nombre AS medico 
                        FROM HorariosMedicos hm
                        JOIN Medicos m ON hm.idMedico = m.idMedico
                        JOIN Usuarios u ON m.idUsuario = u.idUsuario
                        WHERE hm.idHorario = :idHorario
                    ");
                    $stmtCitaDatos->bindParam(":idHorario", $idHorario);
                    $stmtCitaDatos->execute();
                    $cita = $stmtCitaDatos->fetch(PDO::FETCH_ASSOC);
                
                    $asunto = "Cita Médica Registrada - En espera de aprobación";
                    $mensaje = "
                    <!DOCTYPE html>
                    <html lang='es'>
                    <head>
                        <meta charset='UTF-8'>
                        <style>
                            .container{font-family: Arial; background-color:#fff; padding:20px;}
                            .header{background-color:#2c8dfb;color:white;padding:10px;}
                            .details{background:#f4f4f4;padding:10px;}
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>Cita Médica Registrada</div>
                            <p>¡Hola <strong>{$paciente['nombre']}</strong>!</p>
                            <p>Tu cita está registrada con estos datos:</p>
                            <div class='details'>
                                <p><strong>Fecha:</strong> {$cita['fecha']}</p>
                                <p><strong>Hora:</strong> {$hora}</p>
                                <p><strong>Médico:</strong> Dr. {$cita['medico']}</p>
                            </div>
                            <p>Tu cita está pendiente de aprobación.</p>
                            <p>Gracias,<br>El equipo de MediCitas</p>
                        </div>
                    </body>
                    </html>";
                
                    enviarCorreo($paciente['correo'], $asunto, $mensaje);
                
                    header("Location: confirmación.php");
                    exit;
                }                
                
            } else {
                echo "error: No se encontró un paciente asociado al DNI proporcionado.";
            }
        } else {
            echo "error: No se encontró un usuario con el DNI proporcionado.";
        }
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
}
