<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';
$medico_filter = $_GET['medico'] ?? '';
$paciente_filter = $_GET['paciente'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';
$hora_filter = $_GET['hora'] ?? '';
$estado_filter = $_GET['estado'] ?? '';

$sql = "SELECT Citas.idCita, 
       U1.nombre + ' ' + U1.apellido AS paciente, 
       U2.nombre + ' ' + U2.apellido AS medico, 
       Citas.fecha, 
       Citas.hora, 
       Citas.estado
        FROM Citas 
        INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
        INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
        INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
        INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario
        WHERE 1=1";

if ($medico_filter) {
    $sql .= " AND U2.nombre LIKE '%$medico_filter%'";
}
if ($paciente_filter) {
    $sql .= " AND U1.nombre LIKE '%$paciente_filter%'";
}
if ($fecha_filter) {
    $sql .= " AND Citas.fecha = '$fecha_filter'";
}
if ($hora_filter) {
    $sql .= " AND Citas.hora = '$hora_filter'";
}
if ($estado_filter) {
    $sql .= " AND Citas.estado = '$estado_filter'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $citas = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Citas Médicas</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
              </thead><tbody>";

    foreach ($citas as $fila) {
        $hora_formateada = date("H:i", strtotime($fila['hora']));
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
                    <td>{$fila['medico']}</td>
                    <td>{$fila['fecha']}</td>
                    <td>{$hora_formateada}</td>
                    <td>{$fila['estado']}</td>
                  </tr>";
    }
    $html .= "</tbody></table>";

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("citas_medicas.pdf", array("Attachment" => false));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Médico');
    $sheet->setCellValue('C1', 'Fecha');
    $sheet->setCellValue('D1', 'Hora');
    $sheet->setCellValue('E1', 'Estado');

    $row = 2;
    foreach ($citas as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['medico']);
        $sheet->setCellValue("C$row", $fila['fecha']);
        $sheet->setCellValue("D$row", $fila['hora']);
        $sheet->setCellValue("E$row", $fila['estado']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'citas_medicas.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText("Lista de Citas Médicas", ['bold' => true, 'size' => 16]);
    $table = $section->addTable();

    $table->addRow();
    $table->addCell(2000)->addText("Paciente");
    $table->addCell(2000)->addText("Médico");
    $table->addCell(2000)->addText("Fecha");
    $table->addCell(2000)->addText("Hora");
    $table->addCell(2000)->addText("Estado");

    foreach ($citas as $fila) {
        $table->addRow();
        $table->addCell(2000)->addText($fila['paciente']);
        $table->addCell(2000)->addText($fila['medico']);
        $table->addCell(2000)->addText($fila['fecha']);
        $table->addCell(2000)->addText($fila['hora']);
        $table->addCell(2000)->addText($fila['estado']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"citas_medicas.docx\"");
    $phpWord->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        .filter-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
        }

        .filter-container form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-container input,
        .filter-container select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            flex: 1;
            min-width: 150px;
            transition: border-color 0.3s ease;
        }

        .filter-container input:focus,
        .filter-container select:focus {
            border-color: #0099ff;
            outline: none;
        }

        .filter-container button {
            background-color: #0099ff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .filter-container button:hover {
            background-color: #0077cc;
        }

        .export-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-pdf,
        .btn-excel,
        .btn-word {
            display: inline-block;
            background-color: #154ce4;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-pdf:hover,
        .btn-excel:hover,
        .btn-word:hover {
            background-color: #9bbdf0;
        }

        .status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .status.confirmed {
            background-color: #e3fcef;
            color: #28a745;
        }

        .status.pending {
            background-color: #fff3cd;
            color: #d39e00;
        }

        .status.cancelled {
            background-color: #f8d7da;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .filter-container form {
                flex-direction: column;
            }

            .filter-container input,
            .filter-container select,
            .filter-container button {
                width: 100%;
                margin-bottom: 10px;
            }

            .export-buttons {
                flex-direction: column;
            }

            .btn-pdf,
            .btn-excel,
            .btn-word {
                width: 100%;
                margin-right: 0;
            }

            .status {
                font-size: 10px;
                padding: 6px 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
<div class="contenedor">
    <?php include 'menu.php'; ?>
    <main class="contenido">
        <div class="filter-container">
            <form method="GET" action="">
                <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>">
                <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>">
                <input type="date" name="fecha" value="<?= $fecha_filter ?>">
                <input type="time" name="hora" value="<?= $hora_filter ?>">
                <select name="estado">
                    <option value="">Estado</option>
                    <option value="Confirmada" <?= $estado_filter == 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="Pendiente" <?= $estado_filter == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Cancelada" <?= $estado_filter == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Tabla de Citas Médicas</h2>
            <div class="export-buttons">
                
                <a href="?export_pdf=true" class="btn-pdf">Exportar a PDF</a>
                <a href="?export_excel=true" class="btn-excel">Exportar a Excel</a>
                <a href="?export_word=true" class="btn-word">Exportar a Word</a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $estadoClases = [
                            'Confirmada' => 'confirmed',
                            'Pendiente' => 'pending',
                            'Cancelada' => 'cancelled',
                        ];

                        if (count($citas) > 0) {
                            foreach ($citas as $fila) {
                                $hora_formateada = date("H:i", strtotime($fila['hora']));
                                $claseEstado = $estadoClases[$fila['estado']] ?? '';

                                echo "<tr>
                                <td>{$fila['paciente']}</td>
                                <td>{$fila['medico']}</td>
                                <td>{$fila['fecha']}</td>
                                <td>{$hora_formateada}</td>
                                <td><span class='status $claseEstado'>" . ucfirst($fila['estado']) . "</span></td>
                               <td>
                                <a href='editar-cita.php?id={$fila['idCita']}' class='btn-action
                                    edit'><img src='../img/edit.png' alt='Editar' width='35' height='35'></a>
                                <a href='eliminar_cita.php?id={$fila['idCita']}' class='btn-action delete'>
                                    <img src='../img/delete.png' alt='Eliminar' width='35' height='35'></a>
                            </td>


                              </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No hay citas registradas</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </main>
</div>
    <?php
    $conn = null;
    ?>

</body>

</html>