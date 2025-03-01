<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';
$dni_filter = $_GET['dni'] ?? '';
$nombre_apellido_filter = $_GET['nombre_apellido'] ?? '';
$sexo_filter = $_GET['sexo'] ?? '';
$paginaActual = 'pacientes';

$sql = "SELECT
T1.idPaciente, T2.dni, T2.nombre, T2.apellido, T1.sexo, T1.fechaNacimiento, T1.telefono, T1.direccion
FROM Pacientes T1
INNER JOIN Usuarios T2 ON T2.idUsuario = T1.idUsuario WHERE 1=1";

if ($dni_filter) {
    $sql .= " AND T2.dni LIKE '%$dni_filter%'";
}
if ($nombre_apellido_filter) {
    $sql .= " AND (T2.nombre LIKE '%$nombre_apellido_filter%' OR T2.apellido LIKE '%$nombre_apellido_filter%')";
}
if ($sexo_filter) {
    $sql .= " AND T1.sexo LIKE '%$sexo_filter%'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $pacientes = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Pacientes Registrados</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Sexo</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                </tr>
              </thead><tbody>";

    foreach ($pacientes as $fila) {
        $html .= "<tr>
                    <td>{$fila['dni']}</td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['apellido']}</td>
                    <td>{$fila['sexo']}</td>
                    <td>{$fila['fechaNacimiento']}</td>
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
    $dompdf->stream("pacientes_registrados.pdf", array("Attachment" => false));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'DNI');
    $sheet->setCellValue('C1', 'Nombre');
    $sheet->setCellValue('D1', 'Apellido');
    $sheet->setCellValue('E1', 'Sexo');
    $sheet->setCellValue('F1', 'Fecha de Nacimiento');
    $sheet->setCellValue('G1', 'Teléfono');
    $sheet->setCellValue('H1', 'Dirección');

    $row = 2;
    foreach ($pacientes as $fila) {
        $sheet->setCellValue("A$row", $fila['idPaciente']);
        $sheet->setCellValue("B$row", $fila['dni']);
        $sheet->setCellValue("C$row", $fila['nombre']);
        $sheet->setCellValue("D$row", $fila['apellido']);
        $sheet->setCellValue("E$row", $fila['sexo']);
        $sheet->setCellValue("F$row", $fila['fechaNacimiento']);
        $sheet->setCellValue("G$row", $fila['telefono']);
        $sheet->setCellValue("H$row", $fila['direccion']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'pacientes_registrados.xlsx';
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
    $table->addCell(1000)->addText("ID");
    $table->addCell(1500)->addText("DNI");
    $table->addCell(1500)->addText("Nombre");
    $table->addCell(1500)->addText("Apellido");
    $table->addCell(1500)->addText("Sexo");
    $table->addCell(2000)->addText("Fecha de Nacimiento");
    $table->addCell(1500)->addText("Teléfono");
    $table->addCell(2000)->addText("Dirección");

    foreach ($pacientes as $fila) {
        $table->addRow();
        $table->addCell(1000)->addText($fila['idPaciente']);
        $table->addCell(1500)->addText($fila['dni']);
        $table->addCell(1500)->addText($fila['nombre']);
        $table->addCell(1500)->addText($fila['apellido']);
        $table->addCell(1500)->addText($fila['sexo']);
        $table->addCell(2000)->addText($fila['fechaNacimiento']);
        $table->addCell(1500)->addText($fila['telefono']);
        $table->addCell(2000)->addText($fila['direccion']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"pacientes_registrados.docx\"");
    $phpWord->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Registrados</title>
    <link rel="stylesheet" href="../css/tabla.css">
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

        .add-btn,
        .btn-pdf,
        .btn-excel,
        .btn-word {
            display: inline-block;
            background-color: #0b5471;
            color: white;
            padding: 10px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .add-btn:hover,
        .btn-pdf:hover,
        .btn-excel:hover,
        .btn-word:hover {
            background-color: #9bbdf0;
        }

        @media (max-width: 768px) {
            .export-buttons {
                flex-direction: column;
            }

            .add-btn,
            .btn-pdf,
            .btn-excel,
            .btn-word {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <?php include 'modals/agregar-usuario.php'; ?>
        <main class="contenido">
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" name="dni" placeholder="Buscar por DNI" value="<?= $dni_filter ?>" autocomplete="off">
                    <input type="text" name="nombre_apellido" placeholder="Buscar por Nombre/Apellido" value="<?= $nombre_apellido_filter ?>" autocomplete="off">
                    <select name="sexo">
                        <option value="">Sexo</option>
                        <option value="Masculino" <?= $sexo_filter == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino" <?= $sexo_filter == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                    <button type="submit">Filtrar</button>
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE PACIENTES</h2>
                <div class="export-buttons">
                    <a href="#" class="add-btn">Agregar Paciente</a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_pdf' => 1])) ?>" class="btn-pdf">Exportar a PDF</a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_excel' => 1])) ?>" class="btn-excel">Exportar a Excel</a>
                    <a href="?<?= http_build_query(array_merge($_GET, ['export_word' => 1])) ?>" class="btn-word">Exportar a Word</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>SEXO</th>
                                <th>FECHA DE NACIMIENTO</th>
                                <th>TELEFONO</th>
                                <th>DIRECCION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($pacientes) > 0) {
                                foreach ($pacientes as $fila) {
                                    echo "<tr>
                                <td>{$fila['idPaciente']}</td>
                                <td>{$fila['dni']}</td>
                                <td>{$fila['nombre']}</td>
                                <td>{$fila['apellido']}</td>
                                <td>{$fila['sexo']}</td>
                                <td>{$fila['fechaNacimiento']}</td>
                                <td>{$fila['telefono']}</td>
                                <td>{$fila['direccion']}</td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No hay pacientes registrados</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
<script>
    const modals = document.querySelectorAll(".modalAgregarUsuario, .modalEditarUsuario");
    const closeButtons = document.querySelectorAll(".close");
    const editButtons = document.querySelectorAll(".edit-btn");
    const addButtons = document.querySelectorAll(".add-btn");
    const deleteButtons = document.querySelectorAll(".delete-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            event.preventDefault();
            modalAgregarUsuario.style.display = "block";
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener("click", function() {
            modals.forEach(modal => modal.style.display = "none");
        });
    });

    window.onclick = function(event) {
        modals.forEach(modal => {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    };
</script>
<?php include 'alert.php'; ?>
</html>