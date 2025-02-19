<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php'; 

$paciente_filter = $_GET['paciente'] ?? '';
$fecha_filter = $_GET['fechaNacimiento'] ?? '';
$sexo_filter = $_GET['sexo'] ?? '';
$telefono_filter = $_GET['telefono'] ?? '';
$direccion_filter = $_GET['direccion'] ?? '';


$sql = "SELECT U1.nombre + ' ' + U1.apellido AS paciente, 
               Pacientes.fechaNacimiento, 
               Pacientes.sexo, 
               Pacientes.telefono, 
			   Pacientes.direccion
        FROM Pacientes 
        INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
        WHERE 1=1";

if ($paciente_filter) {
    $sql .= " AND U1.nombre LIKE '%$paciente_filter%'";
}
if ($fecha_filter) {
    $sql .= " AND Pacientes.fechaNacimiento = '$fecha_filter'";
}
if ($sexo_filter) {
    $sql .= " AND Pacientes.sexo = '$sexo_filter'";
}
if ($telefono_filter) {
    $sql .= " AND Pacientes.telefono = '$telefono_filter' ";
}
if ($direccion_filter) {
    $sql .= " AND Pacientes.direccion = '$direccion_filter' ";
}


try {
    $query = $conn->prepare($sql);
    $query->execute();
    $paciente = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Pacientes Registrados</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha Nacimiento</th>
                    <th>Sexo</th>
                    <th>Telefono</th>
                    <th>Direccion</th>
                </tr>
              </thead><tbody>";

    foreach ($paciente as $fila) {
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
                    <td>{$fila['fechaNacimiento']}</td>
                    <td>{$fila['sexo']}</td>
                    <td>{$fila['telefono']}</td>
                    <td>{$fila['direccion']}</td>
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
    $dompdf->stream("pacientes_registros.pdf", array("Attachment" => false));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Fecha de Nacimiento');
    $sheet->setCellValue('C1', 'Sexo');
    $sheet->setCellValue('D1', 'Teléfono');
    $sheet->setCellValue('E1', 'Dirección');

    $row = 2;
    foreach ($paciente as $fila) {  // Antes estaba $citas en lugar de $paciente
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['fechaNacimiento']);
        $sheet->setCellValue("C$row", $fila['sexo']);
        $sheet->setCellValue("D$row", $fila['telefono']);
        $sheet->setCellValue("E$row", $fila['direccion']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'pacientes_registros.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText("Lista de Pacientes Registrados", ['bold' => true, 'size' => 16]);
    $table = $section->addTable();

    $table->addRow();
    $table->addCell(2000)->addText("Paciente");
    $table->addCell(2000)->addText("Fecha de Nacimiento");
    $table->addCell(2000)->addText("Sexo");
    $table->addCell(2000)->addText("Teléfono");
    $table->addCell(2000)->addText("Dirección");

    foreach ($paciente as $fila) {
        $table->addRow();
        $table->addCell(2000)->addText($fila['paciente']);
        $table->addCell(2000)->addText($fila['fechaNacimiento']);
        $table->addCell(2000)->addText($fila['sexo']);
        $table->addCell(2000)->addText($fila['telefono']);
        $table->addCell(2000)->addText($fila['direccion']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"pacientes_registros.docx\"");
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
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
        }
    </style>
</head>
<body>
<nav>
    <div class="logo">
        MediCitas
    </div>
    <input type="checkbox" id="click">
    <label for="click" class="menu-btn">
        <i class="fas fa-bars"></i>
    </label>
    <ul class="menu">
        <li><a class="active" href="../medicos/header.php">Salir</a></li>
    </ul>
</nav>

<main>
    <div class="filter-container">
        <form method="GET" action="">
            <input type="text" name="nombre" placeholder="Buscar por Nombre" value="<?= $paciente_filter ?>">
            <input type="text" name="fechaNacimiento" placeholder="Buscar por Fecha de Nacimiento" value="<?= $fecha_filter ?>">
            <input type="text" name="sexo" placeholder="Buscar por Sexo" value="<?= $sexo_filter ?>">
            <input type="text" name="telefono" placeholder="Buscar por Telefono" value="<?= $telefono_filter ?>">
            <input type="text" name="direccion" placeholder="Buscar por Direccion" value="<?= $direccion_filter ?>">
            <button type="submit">Filtrar</button>
        </form>
    </div>
    

    <div class="table-container">
        <h2>Tabla de Pacientes Registrados</h2>
        <div class="export-buttons">
            <a href="?export_pdf=true" class="btn-pdf">Exportar a PDF</a>
            <a href="?export_excel=true" class="btn-excel">Exportar a Excel</a>
            <a href="?export_word=true" class="btn-word">Exportar a Word</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha Nacimiento</th>
                    <th>Sexo</th>
                    <th>Telefono</th>
                    <th>Direccion</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (count($paciente) > 0) {
                foreach ($paciente as $fila) {
                    echo "<tr>
                            <td>{$fila['paciente']}</td>
                            <td>{$fila['fechaNacimiento']}</td>
                            <td>{$fila['sexo']}</td>
                            <td>{$fila['telefono']}</td>
                            <td>{$fila['direccion']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay pacientes registrados</td></tr>";
            }
            ?>
            </tbody>
        </table>
        
    </div>
</main>

<?php
$conn = null;
?>

</body>
</html>