<?php
require '../../php/vendor/autoload.php'; // Cargar la librería Dompdf
include '../../conexion.php';

$idDocumento = $_GET['id'];

$consulta = "SELECT 
        T1.idDocumento, T2.idCita, T7.fecha AS FechaCita,
        T5.nombre + ' ' + T5.apellido AS paciente,
        DATEDIFF(YEAR, T3.fechaNacimiento, GETDATE()) - 
        CASE 
            WHEN MONTH(GETDATE()) < MONTH(T3.fechaNacimiento) 
            OR (MONTH(GETDATE()) = MONTH(T3.fechaNacimiento) AND DAY(GETDATE()) < DAY(T3.fechaNacimiento))
            THEN 1 
            ELSE 0 
        END AS Edad,
        T4.telefono AS telMedico, T6.nombre + ' ' + T6.apellido AS medico,
        T1.tipoDocumento, T1.descripcion, T1.fechaSubida
        FROM DocumentosMedicos T1
        INNER JOIN Citas T2 ON T2.idCita = T1.idCita
        INNER JOIN Pacientes T3 ON T3.idPaciente = T2.idPaciente
        INNER JOIN Medicos T4 ON T4.idMedico = T2.idMedico
        INNER JOIN Usuarios T5 ON T5.idUsuario = T3.idUsuario
        INNER JOIN Usuarios T6 ON T6.idUsuario = T4.idUsuario
        INNER JOIN HorariosMedicos T7 ON T7.idHorario = T2.idHorario
        WHERE idDocumento = ?";
$statement = $conn->prepare($consulta);
$statement->execute([$idDocumento]);
$documento = $statement->fetch();

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuración de Dompdf
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isRemoteEnabled', true); // Habilita el uso de imágenes remotas
$dompdf = new Dompdf($options);

// HTML de la receta
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 40px; }
        .header { text-align: center; font-size: 22px; font-weight: bold; }
        .sub-header { text-align: center; font-size: 18px; margin-bottom: 20px; }
        .content { border: 1px solid #000; padding: 15px; border-radius: 5px; }
        .content p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border-radius: 5px; }
        th, td { border: 1px solid #000; padding: 15px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
<img src="http://localhost/CitasMedicas/img/logo-medicitas.png" alt="Logo" style="display: block; margin: 0 auto; width: 200px;">

<div class="header">Clínica Médica MediCitas</div>
<div class="sub-header">'. $documento['tipoDocumento'] .' Médica</div>

<p><strong>Nº Cita: '. $documento['idCita'] .'</strong></p>

<div class="content">
    <p><strong>Médico:</strong> Dr. ' . $documento['medico'] . '</p>
    <p><strong>Fecha:</strong> ' . $documento['FechaCita'] . '</p>
    <p><strong>Paciente:</strong> ' . $documento['paciente'] . '</p>
    <p><strong>Edad:</strong> ' . $documento['Edad'] . ' años</p>
</div>

<table>
    <tr>
        <th>Descripción</th>
    </tr>
    <tr>
        <td>'. $documento['descripcion'] .'</td>
    </tr>
</table>

<p><strong>Fecha de emisión:</strong> '. $documento['fechaSubida'] .'</p>
<p><strong>Teléfono:</strong> + (504) '. $documento['telMedico'] .'</p>
<p><strong>Dirección:</strong> Santa Bárbara, S.B. , Honduras C.A.</p>

</body>
</html>';

// Cargar el HTML en Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Tamaño y orientación
$dompdf->render();

// Descargar o mostrar el PDF
$dompdf->stream("receta_medica.pdf", ["Attachment" => false]); // Cambia a true para descargar
?>