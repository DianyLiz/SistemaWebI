<?php
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Paciente');
$sheet->setCellValue('B1', 'Médico');
$sheet->setCellValue('C1', 'Fecha');
$sheet->setCellValue('D1', 'Hora');
$sheet->setCellValue('E1', 'Estado');

$row = 2; 
foreach ($citas as $cita) {
    $sheet->setCellValue('A' . $row, $cita['paciente']);
    $sheet->setCellValue('B' . $row, $cita['medico']);
    $sheet->setCellValue('C' . $row, $cita['fecha']);
    $sheet->setCellValue('D' . $row, $cita['hora']);
    $sheet->setCellValue('E' . $row, $cita['estado']);
    $row++;
}


$writer = new Xlsx($spreadsheet);
$filename = 'Citas_Medicas.xlsx';


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
?>
