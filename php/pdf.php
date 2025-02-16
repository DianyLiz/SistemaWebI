<?php
require 'vendor/autoload.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

include '../conexion.php'; 
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos.");
}

try {
    $sql = "SELECT U1.nombre + ' ' + U1.apellido AS paciente, 
                   U2.nombre + ' ' + U2.apellido AS medico, 
                   Citas.fecha, 
                   Citas.hora, 
                   Citas.estado 
            FROM Citas 
            INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
            INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
            INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
            INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario";

    $query = $conn->prepare($sql);
    $query->execute();
    $citas = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

$html = '<h2>Tabla de Citas Médicas</h2><table border="1">';
$html .= '<thead><tr><th>Paciente</th><th>Médico</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead><tbody>';

foreach ($citas as $cita) {
    $html .= '<tr>';
    $html .= '<td>' . $cita['paciente'] . '</td>';
    $html .= '<td>' . $cita['medico'] . '</td>';
    $html .= '<td>' . $cita['fecha'] . '</td>';
    $html .= '<td>' . $cita['hora'] . '</td>';
    $html .= '<td>' . $cita['estado'] . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();


$dompdf->stream("Citas_Medicas.pdf", array("Attachment" => false));
?>
